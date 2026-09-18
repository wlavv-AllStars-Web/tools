CREATE TABLE IF NOT EXISTS `moloni_vat_validations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `country_iso` CHAR(2) NOT NULL DEFAULT '', `vat_number` VARCHAR(64) NOT NULL DEFAULT '', `normalized_vat_number` VARCHAR(80) NOT NULL, `status` VARCHAR(32) NOT NULL, `attempts` INT UNSIGNED NOT NULL DEFAULT 0, `last_attempt_at` DATETIME NULL, `next_attempt_at` DATETIME NULL, `validated_at` DATETIME NULL, `valid_until` DATETIME NULL, `last_error` TEXT NULL, `vies_response` JSON NULL, `manual_notes` TEXT NULL, `created_at` DATETIME NULL, `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`), UNIQUE KEY `moloni_vat_validations_normalized_vat_unique` (`normalized_vat_number`), KEY `moloni_vat_validations_due_idx` (`status`,`next_attempt_at`), KEY `moloni_vat_validations_valid_until_idx` (`valid_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `moloni_vat_validation_orders` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `id_order` INT UNSIGNED NOT NULL, `id_customer` INT UNSIGNED NOT NULL, `customer_group_id` INT UNSIGNED NOT NULL, `moloni_invoice_id` BIGINT UNSIGNED NULL, `moloni_vat_validation_id` BIGINT UNSIGNED NOT NULL, `source` VARCHAR(32) NOT NULL DEFAULT 'module', `created_at` DATETIME NULL, `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`), UNIQUE KEY `moloni_vat_validation_orders_order_unique` (`id_order`), KEY `moloni_vat_validation_orders_validation_idx` (`moloni_vat_validation_id`), KEY `moloni_vat_validation_orders_customer_idx` (`id_customer`), CONSTRAINT `moloni_vat_validation_orders_validation_fk` FOREIGN KEY (`moloni_vat_validation_id`) REFERENCES `moloni_vat_validations` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
