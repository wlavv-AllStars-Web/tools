-- Stock synchronization automation for PS9.
--
-- Covers three rules:
-- 1) Combination stock changes update the parent product stock by shop/scope.
-- 2) Products with the same product reference share the same parent stock by shop/scope.
-- 3) Combinations with the same combination reference share the same combination stock by shop/scope.
-- 4) stock_arrive follows the same duplicate-reference rule for:
--      ps_custom_product.stock_arrive by ps_product.reference
--      ps_custom_product_attribute.stock_arrive by ps_product_attribute.reference
--
-- MySQL does not allow a trigger to update the same table that fired it.
-- Triggers therefore only write to queue tables. A database event processes the queues.
--
-- Requirement:
-- event_scheduler must be enabled:
--   SET GLOBAL event_scheduler = ON;
-- For persistence after restart, configure MySQL/MariaDB:
--   event_scheduler=ON

CREATE TABLE IF NOT EXISTS ps_stock_parent_sync_queue (
    id_product INT UNSIGNED NOT NULL,
    date_add DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_upd DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_product)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ps_stock_reference_sync_queue (
    sync_type ENUM('product','attribute') NOT NULL,
    reference VARCHAR(128) NOT NULL,
    id_shop INT UNSIGNED NOT NULL DEFAULT 0,
    id_shop_group INT UNSIGNED NOT NULL DEFAULT 0,
    quantity INT NOT NULL DEFAULT 0,
    date_add DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_upd DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (sync_type, reference, id_shop, id_shop_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ps_stock_arrive_reference_sync_queue (
    sync_type ENUM('product','attribute') NOT NULL,
    reference VARCHAR(128) NOT NULL,
    stock_arrive INT DEFAULT 0,
    date_add DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_upd DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (sync_type, reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP PROCEDURE IF EXISTS ps_sync_parent_stock_from_combinations;
DROP PROCEDURE IF EXISTS ps_process_parent_stock_sync_queue;
DROP PROCEDURE IF EXISTS ps_process_stock_reference_sync_queue;
DROP PROCEDURE IF EXISTS ps_process_stock_arrive_reference_sync_queue;
DROP PROCEDURE IF EXISTS ps_process_stock_sync_queues;

DELIMITER $$

CREATE PROCEDURE ps_sync_parent_stock_from_combinations(IN in_id_product INT)
BEGIN
    UPDATE ps_stock_available parent
    LEFT JOIN (
        SELECT
            id_product,
            id_shop,
            id_shop_group,
            SUM(quantity) AS children_quantity
        FROM ps_stock_available
        WHERE id_product_attribute <> 0
          AND (in_id_product IS NULL OR in_id_product = 0 OR id_product = in_id_product)
        GROUP BY id_product, id_shop, id_shop_group
    ) sums
        ON sums.id_product = parent.id_product
       AND sums.id_shop = parent.id_shop
       AND sums.id_shop_group = parent.id_shop_group
    SET parent.quantity = COALESCE(sums.children_quantity, 0)
    WHERE parent.id_product_attribute = 0
      AND (in_id_product IS NULL OR in_id_product = 0 OR parent.id_product = in_id_product)
      AND EXISTS (
          SELECT 1
          FROM ps_stock_available child
          WHERE child.id_product = parent.id_product
            AND child.id_product_attribute <> 0
      )
      AND parent.quantity <> COALESCE(sums.children_quantity, 0);
END$$

CREATE PROCEDURE ps_process_parent_stock_sync_queue()
BEGIN
    UPDATE ps_stock_available parent
    JOIN ps_stock_parent_sync_queue queue
        ON queue.id_product = parent.id_product
    LEFT JOIN (
        SELECT
            id_product,
            id_shop,
            id_shop_group,
            SUM(quantity) AS children_quantity
        FROM ps_stock_available
        WHERE id_product_attribute <> 0
        GROUP BY id_product, id_shop, id_shop_group
    ) sums
        ON sums.id_product = parent.id_product
       AND sums.id_shop = parent.id_shop
       AND sums.id_shop_group = parent.id_shop_group
    SET parent.quantity = COALESCE(sums.children_quantity, 0)
    WHERE parent.id_product_attribute = 0
      AND parent.quantity <> COALESCE(sums.children_quantity, 0);

    DELETE FROM ps_stock_parent_sync_queue;
END$$

CREATE PROCEDURE ps_process_stock_reference_sync_queue()
BEGIN
    UPDATE ps_stock_available target
    JOIN ps_product product_target
        ON product_target.id_product = target.id_product
    JOIN ps_stock_reference_sync_queue queue
        ON queue.sync_type = 'product'
       AND queue.reference = product_target.reference
       AND queue.id_shop = target.id_shop
       AND queue.id_shop_group = target.id_shop_group
    SET target.quantity = queue.quantity
    WHERE target.id_product_attribute = 0
      AND target.quantity <> queue.quantity;

    INSERT INTO ps_stock_parent_sync_queue (id_product, date_add, date_upd)
    SELECT DISTINCT attribute_target.id_product, NOW(), NOW()
    FROM ps_stock_available target
    JOIN ps_product_attribute attribute_target
        ON attribute_target.id_product_attribute = target.id_product_attribute
    JOIN ps_stock_reference_sync_queue queue
        ON queue.sync_type = 'attribute'
       AND queue.reference = attribute_target.reference
       AND queue.id_shop = target.id_shop
       AND queue.id_shop_group = target.id_shop_group
    WHERE target.id_product_attribute <> 0
      AND target.quantity <> queue.quantity
    ON DUPLICATE KEY UPDATE date_upd = NOW();

    UPDATE ps_stock_available target
    JOIN ps_product_attribute attribute_target
        ON attribute_target.id_product_attribute = target.id_product_attribute
    JOIN ps_stock_reference_sync_queue queue
        ON queue.sync_type = 'attribute'
       AND queue.reference = attribute_target.reference
       AND queue.id_shop = target.id_shop
       AND queue.id_shop_group = target.id_shop_group
    SET target.quantity = queue.quantity
    WHERE target.id_product_attribute <> 0
      AND target.quantity <> queue.quantity;
END$$

CREATE PROCEDURE ps_process_stock_arrive_reference_sync_queue()
BEGIN
    UPDATE ps_custom_product target
    JOIN ps_product product_target
        ON product_target.id_product = target.id_product
    JOIN ps_stock_arrive_reference_sync_queue queue
        ON queue.sync_type = 'product'
       AND queue.reference = product_target.reference
    SET target.stock_arrive = queue.stock_arrive
    WHERE COALESCE(target.stock_arrive, 0) <> COALESCE(queue.stock_arrive, 0);

    UPDATE ps_custom_product_attribute target
    JOIN ps_product_attribute attribute_target
        ON attribute_target.id_product_attribute = target.id_product_attribute
    JOIN ps_stock_arrive_reference_sync_queue queue
        ON queue.sync_type = 'attribute'
       AND queue.reference = attribute_target.reference
    SET target.stock_arrive = queue.stock_arrive
    WHERE COALESCE(target.stock_arrive, 0) <> COALESCE(queue.stock_arrive, 0);
END$$

CREATE PROCEDURE ps_process_stock_sync_queues()
BEGIN
    SET @ps_stock_sync_processing = 1;

    CALL ps_process_stock_reference_sync_queue();
    CALL ps_process_parent_stock_sync_queue();
    CALL ps_process_stock_reference_sync_queue();
    CALL ps_process_stock_arrive_reference_sync_queue();

    DELETE FROM ps_stock_reference_sync_queue;
    DELETE FROM ps_stock_arrive_reference_sync_queue;

    SET @ps_stock_sync_processing = NULL;
END$$

DELIMITER ;

DROP TRIGGER IF EXISTS ps_stock_available_parent_sync_ai;
DROP TRIGGER IF EXISTS ps_stock_available_parent_sync_au;
DROP TRIGGER IF EXISTS ps_stock_available_parent_sync_ad;
DROP TRIGGER IF EXISTS ps_custom_product_stock_arrive_sync_ai;
DROP TRIGGER IF EXISTS ps_custom_product_stock_arrive_sync_au;
DROP TRIGGER IF EXISTS ps_custom_product_attribute_stock_arrive_sync_ai;
DROP TRIGGER IF EXISTS ps_custom_product_attribute_stock_arrive_sync_au;

DELIMITER $$

CREATE TRIGGER ps_stock_available_parent_sync_ai
AFTER INSERT ON ps_stock_available
FOR EACH ROW
BEGIN
    IF NEW.id_product_attribute = 0 AND COALESCE(@ps_stock_sync_processing, 0) = 0 THEN
        INSERT INTO ps_stock_reference_sync_queue (sync_type, reference, id_shop, id_shop_group, quantity, date_add, date_upd)
        SELECT 'product', reference, NEW.id_shop, NEW.id_shop_group, NEW.quantity, NOW(), NOW()
        FROM ps_product
        WHERE id_product = NEW.id_product
          AND reference <> ''
        ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), date_upd = NOW();
    ELSEIF COALESCE(@ps_stock_sync_processing, 0) = 0 THEN
        INSERT INTO ps_stock_reference_sync_queue (sync_type, reference, id_shop, id_shop_group, quantity, date_add, date_upd)
        SELECT 'attribute', reference, NEW.id_shop, NEW.id_shop_group, NEW.quantity, NOW(), NOW()
        FROM ps_product_attribute
        WHERE id_product_attribute = NEW.id_product_attribute
          AND reference <> ''
        ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), date_upd = NOW();

        INSERT INTO ps_stock_parent_sync_queue (id_product, date_add, date_upd)
        VALUES (NEW.id_product, NOW(), NOW())
        ON DUPLICATE KEY UPDATE date_upd = NOW();
    END IF;
END$$

CREATE TRIGGER ps_stock_available_parent_sync_au
AFTER UPDATE ON ps_stock_available
FOR EACH ROW
BEGIN
    IF OLD.id_product_attribute <> 0 THEN
        INSERT INTO ps_stock_parent_sync_queue (id_product, date_add, date_upd)
        VALUES (OLD.id_product, NOW(), NOW())
        ON DUPLICATE KEY UPDATE date_upd = NOW();
    END IF;

    IF NEW.id_product_attribute = 0 AND COALESCE(@ps_stock_sync_processing, 0) = 0 THEN
        INSERT INTO ps_stock_reference_sync_queue (sync_type, reference, id_shop, id_shop_group, quantity, date_add, date_upd)
        SELECT 'product', reference, NEW.id_shop, NEW.id_shop_group, NEW.quantity, NOW(), NOW()
        FROM ps_product
        WHERE id_product = NEW.id_product
          AND reference <> ''
        ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), date_upd = NOW();
    ELSEIF COALESCE(@ps_stock_sync_processing, 0) = 0 THEN
        INSERT INTO ps_stock_reference_sync_queue (sync_type, reference, id_shop, id_shop_group, quantity, date_add, date_upd)
        SELECT 'attribute', reference, NEW.id_shop, NEW.id_shop_group, NEW.quantity, NOW(), NOW()
        FROM ps_product_attribute
        WHERE id_product_attribute = NEW.id_product_attribute
          AND reference <> ''
        ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), date_upd = NOW();

        INSERT INTO ps_stock_parent_sync_queue (id_product, date_add, date_upd)
        VALUES (NEW.id_product, NOW(), NOW())
        ON DUPLICATE KEY UPDATE date_upd = NOW();
    END IF;
END$$

CREATE TRIGGER ps_stock_available_parent_sync_ad
AFTER DELETE ON ps_stock_available
FOR EACH ROW
BEGIN
    IF OLD.id_product_attribute <> 0 THEN
        INSERT INTO ps_stock_parent_sync_queue (id_product, date_add, date_upd)
        VALUES (OLD.id_product, NOW(), NOW())
        ON DUPLICATE KEY UPDATE date_upd = NOW();
    END IF;
END$$

CREATE TRIGGER ps_custom_product_stock_arrive_sync_ai
AFTER INSERT ON ps_custom_product
FOR EACH ROW
BEGIN
    IF COALESCE(@ps_stock_sync_processing, 0) = 0 THEN
        INSERT INTO ps_stock_arrive_reference_sync_queue (sync_type, reference, stock_arrive, date_add, date_upd)
        SELECT 'product', reference, COALESCE(NEW.stock_arrive, 0), NOW(), NOW()
        FROM ps_product
        WHERE id_product = NEW.id_product
          AND reference <> ''
        ON DUPLICATE KEY UPDATE stock_arrive = VALUES(stock_arrive), date_upd = NOW();
    END IF;
END$$
CREATE TRIGGER ps_custom_product_stock_arrive_sync_au
AFTER UPDATE ON ps_custom_product
FOR EACH ROW
BEGIN
    IF COALESCE(OLD.stock_arrive, 0) <> COALESCE(NEW.stock_arrive, 0) AND COALESCE(@ps_stock_sync_processing, 0) = 0 THEN
        INSERT INTO ps_stock_arrive_reference_sync_queue (sync_type, reference, stock_arrive, date_add, date_upd)
        SELECT 'product', reference, COALESCE(NEW.stock_arrive, 0), NOW(), NOW()
        FROM ps_product
        WHERE id_product = NEW.id_product
          AND reference <> ''
        ON DUPLICATE KEY UPDATE stock_arrive = VALUES(stock_arrive), date_upd = NOW();
    END IF;
END$$

CREATE TRIGGER ps_custom_product_attribute_stock_arrive_sync_ai
AFTER INSERT ON ps_custom_product_attribute
FOR EACH ROW
BEGIN
    IF COALESCE(@ps_stock_sync_processing, 0) = 0 THEN
        INSERT INTO ps_stock_arrive_reference_sync_queue (sync_type, reference, stock_arrive, date_add, date_upd)
        SELECT 'attribute', reference, COALESCE(NEW.stock_arrive, 0), NOW(), NOW()
        FROM ps_product_attribute
        WHERE id_product_attribute = NEW.id_product_attribute
          AND reference <> ''
        ON DUPLICATE KEY UPDATE stock_arrive = VALUES(stock_arrive), date_upd = NOW();
    END IF;
END$$
CREATE TRIGGER ps_custom_product_attribute_stock_arrive_sync_au
AFTER UPDATE ON ps_custom_product_attribute
FOR EACH ROW
BEGIN
    IF COALESCE(OLD.stock_arrive, 0) <> COALESCE(NEW.stock_arrive, 0) AND COALESCE(@ps_stock_sync_processing, 0) = 0 THEN
        INSERT INTO ps_stock_arrive_reference_sync_queue (sync_type, reference, stock_arrive, date_add, date_upd)
        SELECT 'attribute', reference, COALESCE(NEW.stock_arrive, 0), NOW(), NOW()
        FROM ps_product_attribute
        WHERE id_product_attribute = NEW.id_product_attribute
          AND reference <> ''
        ON DUPLICATE KEY UPDATE stock_arrive = VALUES(stock_arrive), date_upd = NOW();
    END IF;
END$$

DELIMITER ;

DROP EVENT IF EXISTS ps_evt_process_parent_stock_sync_queue;

DELIMITER $$

CREATE EVENT ps_evt_process_parent_stock_sync_queue
ON SCHEDULE EVERY 1 MINUTE
DO CALL ps_process_stock_sync_queues()$$

DELIMITER ;

-- Initial full parent sync after installing:
CALL ps_sync_parent_stock_from_combinations(0);

-- Manual processing of queued stock/reference/arrive changes:
-- CALL ps_process_stock_sync_queues();