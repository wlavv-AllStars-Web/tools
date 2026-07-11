-- Keeps ps_stock_available parent rows (id_product_attribute = 0)
-- synchronized with the sum of their combination rows.
--
-- MySQL does not allow a trigger on ps_stock_available to update
-- ps_stock_available directly. For that reason the trigger only queues
-- the product id, and a database event processes the queue.
--
-- Requirement:
-- event_scheduler must be enabled in MySQL/MariaDB.
-- Usually this requires a privileged user:
--   SET GLOBAL event_scheduler = ON;
--
-- For persistence after restart, configure MySQL/MariaDB:
--   event_scheduler=ON

CREATE TABLE IF NOT EXISTS ps_stock_parent_sync_queue (
    id_product INT UNSIGNED NOT NULL,
    date_add DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_upd DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_product)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP PROCEDURE IF EXISTS ps_sync_parent_stock_from_combinations;
DROP PROCEDURE IF EXISTS ps_process_parent_stock_sync_queue;

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

DELIMITER ;

DROP TRIGGER IF EXISTS ps_stock_available_parent_sync_ai;
DROP TRIGGER IF EXISTS ps_stock_available_parent_sync_au;
DROP TRIGGER IF EXISTS ps_stock_available_parent_sync_ad;

DELIMITER $$

CREATE TRIGGER ps_stock_available_parent_sync_ai
AFTER INSERT ON ps_stock_available
FOR EACH ROW
BEGIN
    IF NEW.id_product_attribute <> 0 THEN
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

    IF NEW.id_product_attribute <> 0 THEN
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

DELIMITER ;

DROP EVENT IF EXISTS ps_evt_process_parent_stock_sync_queue;

DELIMITER $$

CREATE EVENT ps_evt_process_parent_stock_sync_queue
ON SCHEDULE EVERY 1 MINUTE
DO CALL ps_process_parent_stock_sync_queue()$$

DELIMITER ;

-- Initial full sync after installing:
CALL ps_sync_parent_stock_from_combinations(0);

-- Manual sync for one product:
-- CALL ps_sync_parent_stock_from_combinations(15161);

-- Manual processing of queued products:
-- CALL ps_process_parent_stock_sync_queue();