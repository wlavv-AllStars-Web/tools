-- OMS technical image selection.
-- Store the PrestaShop ps_image.id_image that must be displayed as technical.
-- Run once against the PrestaShop database (adjust the ps_ prefix if needed).

ALTER TABLE `ps_custom_product`
    ADD COLUMN `technical_image_id` INT UNSIGNED NULL DEFAULT NULL;

ALTER TABLE `ps_custom_product_attribute`
    ADD COLUMN `technical_image_id` INT UNSIGNED NULL DEFAULT NULL;

-- Example assignments:
-- UPDATE ps_custom_product SET technical_image_id = 123 WHERE id_product = 456;
-- UPDATE ps_custom_product_attribute SET technical_image_id = 123
-- WHERE id_product_attribute = 789;

-- Set technical_image_id back to NULL to fall back to the product cover image.
