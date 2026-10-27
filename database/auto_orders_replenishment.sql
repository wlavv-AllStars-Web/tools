-- Auto Orders automatic replenishment
-- Run this once on the newTools database when Laravel migrations cannot be run.

CREATE TABLE `auto_orders_calculation_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `auto_orders_candidate_id` bigint unsigned DEFAULT NULL,
  `id_order` int unsigned DEFAULT NULL,
  `id_order_detail` int unsigned DEFAULT NULL,
  `id_product` int unsigned NOT NULL,
  `id_product_attribute` int unsigned NOT NULL DEFAULT 0,
  `id_supplier` int unsigned DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `reference` varchar(128) NOT NULL,
  `sales_last_12_months` int unsigned NOT NULL DEFAULT 0,
  `physical_stock` int NOT NULL DEFAULT 0,
  `stock_arrive` int NOT NULL DEFAULT 0,
  `target_stock` int unsigned NOT NULL DEFAULT 0,
  `quantity_before` int unsigned NOT NULL DEFAULT 0,
  `quantity_calculated` int unsigned NOT NULL DEFAULT 0,
  `quantity_after` int unsigned NOT NULL DEFAULT 0,
  `action` varchar(64) NOT NULL,
  `reason` varchar(128) DEFAULT NULL,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `auto_orders_calculation_logs_candidate_idx` (`auto_orders_candidate_id`),
  KEY `auto_orders_calculation_logs_order_idx` (`id_order`),
  KEY `auto_orders_calculation_logs_order_detail_idx` (`id_order_detail`),
  KEY `auto_orders_calculation_logs_product_idx` (`id_product`),
  KEY `auto_orders_calculation_logs_supplier_idx` (`id_supplier`),
  KEY `auto_orders_calculation_logs_reference_idx` (`reference`),
  KEY `auto_orders_calculation_logs_action_idx` (`action`),
  KEY `auto_orders_calc_product_attribute_idx` (`id_product`,`id_product_attribute`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `auto_orders_purchase_list`
  ADD COLUMN `is_automatic` tinyint(1) NOT NULL DEFAULT 0 AFTER `quantity`,
  ADD COLUMN `automatic_expected_quantity` int unsigned DEFAULT NULL AFTER `is_automatic`,
  ADD COLUMN `automatic_calculated_at` timestamp NULL DEFAULT NULL AFTER `automatic_expected_quantity`;
