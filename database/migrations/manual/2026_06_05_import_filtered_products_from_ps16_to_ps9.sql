/*
  Product-only migration script.

  Source: allstar1_01062026.ps_*      (PrestaShop 1.6)
  Target: allstars01_18032026m.ps_*   (PrestaShop 9)

  Scope:
  - Import only products from the 64 manufacturers selected to continue.
  - Do not import customers, addresses, carts, orders, stock, specific prices, or third-party module data.
  - Preserve product IDs, combination IDs, image IDs, and product SEO fields.
  - Publish normal products in target shops 2 and 3.
  - Publish operational distribution products only in target shop 3.
  - Also import operational distribution products from allstarsdistribution:
    SHIPPING-%, VAT-%, and ccFee.
  - Build ps_custom_product and ps_custom_product_attribute from extra fields in A.

  Important:
  - This script intentionally clears B product graph tables in scope before inserting.
  - Product image files on disk are not copied by this SQL.
  - Stock is intentionally cleared and not imported; it must be rebuilt by the global inventory process.
  - Search/layered indexes are cleared and must be regenerated in PrestaShop.
  - Review price_base_currency mapping in ps_custom_* before running.
*/

SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;

DROP TEMPORARY TABLE IF EXISTS tmp_migrate_manufacturer;
CREATE TEMPORARY TABLE tmp_migrate_manufacturer (
  id_manufacturer INT UNSIGNED NOT NULL PRIMARY KEY
) ENGINE=MEMORY;

INSERT INTO tmp_migrate_manufacturer (id_manufacturer) VALUES
(19),(27),(47),(67),(69),(72),(76),(78),(85),(86),(88),(89),(91),(93),(99),(102),
(103),(104),(109),(110),(111),(114),(116),(120),(122),(123),(126),(127),(131),
(133),(135),(136),(138),(139),(140),(141),(142),(143),(144),(151),(152),(153),
(155),(159),(161),(162),(164),(166),(171),(173),(175),(177),(182),(183),(185),
(186),(189),(190),(192),(193),(196),(197),(198),(199);

DROP TEMPORARY TABLE IF EXISTS tmp_migrate_product;
CREATE TEMPORARY TABLE tmp_migrate_product (
  id_product INT UNSIGNED NOT NULL PRIMARY KEY
) ENGINE=MEMORY;

INSERT INTO tmp_migrate_product (id_product)
SELECT p.id_product
FROM allstar1_01062026.ps_product p
JOIN tmp_migrate_manufacturer m ON m.id_manufacturer = p.id_manufacturer;

DROP TEMPORARY TABLE IF EXISTS tmp_migrate_product_attribute;
CREATE TEMPORARY TABLE tmp_migrate_product_attribute (
  id_product_attribute INT UNSIGNED NOT NULL PRIMARY KEY,
  id_product INT UNSIGNED NOT NULL,
  KEY idx_product (id_product)
) ENGINE=MEMORY;

INSERT INTO tmp_migrate_product_attribute (id_product_attribute, id_product)
SELECT pa.id_product_attribute, pa.id_product
FROM allstar1_01062026.ps_product_attribute pa
JOIN tmp_migrate_product mp ON mp.id_product = pa.id_product;

DROP TEMPORARY TABLE IF EXISTS tmp_migrate_image;
CREATE TEMPORARY TABLE tmp_migrate_image (
  id_image INT UNSIGNED NOT NULL PRIMARY KEY,
  id_product INT UNSIGNED NOT NULL,
  KEY idx_product (id_product)
) ENGINE=MEMORY;

INSERT INTO tmp_migrate_image (id_image, id_product)
SELECT i.id_image, i.id_product
FROM allstar1_01062026.ps_image i
JOIN tmp_migrate_product mp ON mp.id_product = i.id_product;

DROP TEMPORARY TABLE IF EXISTS tmp_migrate_feature_value;
CREATE TEMPORARY TABLE tmp_migrate_feature_value (
  id_feature_value INT UNSIGNED NOT NULL PRIMARY KEY,
  id_feature INT UNSIGNED NOT NULL,
  KEY idx_feature (id_feature)
);

INSERT INTO tmp_migrate_feature_value (id_feature_value, id_feature)
SELECT DISTINCT fp.id_feature_value, fp.id_feature
FROM allstar1_01062026.ps_feature_product fp
JOIN tmp_migrate_product mp ON mp.id_product = fp.id_product;

DROP TEMPORARY TABLE IF EXISTS tmp_migrate_feature;
CREATE TEMPORARY TABLE tmp_migrate_feature (
  id_feature INT UNSIGNED NOT NULL PRIMARY KEY
);

INSERT INTO tmp_migrate_feature (id_feature)
SELECT DISTINCT id_feature
FROM tmp_migrate_feature_value;

DROP TEMPORARY TABLE IF EXISTS tmp_migrate_tag;
CREATE TEMPORARY TABLE tmp_migrate_tag (
  id_tag INT UNSIGNED NOT NULL,
  id_lang INT UNSIGNED NOT NULL,
  PRIMARY KEY (id_tag, id_lang)
);

INSERT INTO tmp_migrate_tag (id_tag, id_lang)
SELECT DISTINCT pt.id_tag, pt.id_lang
FROM allstar1_01062026.ps_product_tag pt
JOIN tmp_migrate_product mp ON mp.id_product = pt.id_product;

DROP TEMPORARY TABLE IF EXISTS tmp_migrate_distribution_product;
CREATE TEMPORARY TABLE tmp_migrate_distribution_product (
  id_product INT UNSIGNED NOT NULL PRIMARY KEY
) ENGINE=MEMORY;

INSERT INTO tmp_migrate_distribution_product (id_product)
SELECT p.id_product
FROM allstarsdistribution.psnz_product p
WHERE p.reference LIKE 'SHIPPING-%'
   OR p.reference LIKE 'VAT-%'
   OR p.reference = 'ccFee';

DROP TEMPORARY TABLE IF EXISTS tmp_lang_fallback_a;
CREATE TEMPORARY TABLE tmp_lang_fallback_a (
  target_id_lang INT UNSIGNED NOT NULL PRIMARY KEY,
  source_id_lang INT UNSIGNED NOT NULL,
  iso_code VARCHAR(8) NOT NULL
) ENGINE=MEMORY;

INSERT INTO tmp_lang_fallback_a (target_id_lang, source_id_lang, iso_code)
SELECT
  tl.id_lang,
  COALESCE(sl_same.id_lang, sl_en.id_lang),
  tl.iso_code
FROM allstars01_18032026m.ps_lang tl
LEFT JOIN allstar1_01062026.ps_lang sl_same
  ON sl_same.iso_code = tl.iso_code
LEFT JOIN allstar1_01062026.ps_lang sl_en
  ON sl_en.iso_code = 'en'
WHERE tl.active = 1
  AND COALESCE(sl_same.id_lang, sl_en.id_lang) IS NOT NULL;

DROP TEMPORARY TABLE IF EXISTS tmp_lang_exact_a;
CREATE TEMPORARY TABLE tmp_lang_exact_a (
  source_id_lang INT UNSIGNED NOT NULL PRIMARY KEY,
  target_id_lang INT UNSIGNED NOT NULL,
  iso_code VARCHAR(8) NOT NULL
) ENGINE=MEMORY;

INSERT INTO tmp_lang_exact_a (source_id_lang, target_id_lang, iso_code)
SELECT sl.id_lang, tl.id_lang, tl.iso_code
FROM allstar1_01062026.ps_lang sl
JOIN allstars01_18032026m.ps_lang tl
  ON tl.iso_code = sl.iso_code
WHERE tl.active = 1;

DROP TEMPORARY TABLE IF EXISTS tmp_lang_fallback_dist;
CREATE TEMPORARY TABLE tmp_lang_fallback_dist (
  target_id_lang INT UNSIGNED NOT NULL PRIMARY KEY,
  source_id_lang INT UNSIGNED NOT NULL,
  iso_code VARCHAR(8) NOT NULL
) ENGINE=MEMORY;

INSERT INTO tmp_lang_fallback_dist (target_id_lang, source_id_lang, iso_code)
SELECT
  tl.id_lang,
  COALESCE(sl_same.id_lang, sl_en.id_lang),
  tl.iso_code
FROM allstars01_18032026m.ps_lang tl
LEFT JOIN allstarsdistribution.psnz_lang sl_same
  ON sl_same.iso_code = tl.iso_code
LEFT JOIN allstarsdistribution.psnz_lang sl_en
  ON sl_en.iso_code = 'en'
WHERE tl.active = 1
  AND COALESCE(sl_same.id_lang, sl_en.id_lang) IS NOT NULL;

START TRANSACTION;

/*
  Cleanup target product graph.
  These tables are either reloaded below or regenerated later by PrestaShop/inventory processes.

  Tables cleaned and reloaded:
  - ps_product, ps_product_shop, ps_product_lang
  - ps_custom_product, ps_custom_product_attribute
  - ps_product_attribute, ps_product_attribute_shop, ps_product_attribute_lang
  - ps_product_attribute_combination, ps_product_attribute_image
  - ps_image, ps_image_lang, ps_image_shop
  - ps_category_product, ps_feature*, ps_product_tag, ps_tag
  - ps_product_supplier, ps_accessory, ps_pack
  - ps_product_attachment, ps_product_download, ps_product_carrier

  Tables cleaned and regenerated later:
  - ps_search_index, ps_layered_price_index, ps_layered_product_attribute
  - ps_product_sale, ps_product_group_reduction_cache

  Tables cleaned intentionally but not imported:
  - ps_stock_available, because stock will be rebuilt by global inventory.
*/
DELETE FROM allstars01_18032026m.ps_product_attribute_lang;
DELETE FROM allstars01_18032026m.ps_custom_product_attribute;
DELETE FROM allstars01_18032026m.ps_custom_product;

DELETE FROM allstars01_18032026m.ps_product_attribute_image;
DELETE FROM allstars01_18032026m.ps_product_attribute_combination;
DELETE FROM allstars01_18032026m.ps_product_attribute_shop;
DELETE FROM allstars01_18032026m.ps_product_attribute;

DELETE FROM allstars01_18032026m.ps_product_supplier;
DELETE FROM allstars01_18032026m.ps_product_tag;
DELETE FROM allstars01_18032026m.ps_product_attachment;
DELETE FROM allstars01_18032026m.ps_product_download;
DELETE FROM allstars01_18032026m.ps_product_carrier;
DELETE FROM allstars01_18032026m.ps_category_product;
DELETE FROM allstars01_18032026m.ps_feature_product;
DELETE FROM allstars01_18032026m.ps_accessory;
DELETE FROM allstars01_18032026m.ps_pack;

DELETE FROM allstars01_18032026m.ps_image_shop;
DELETE FROM allstars01_18032026m.ps_image_lang;
DELETE FROM allstars01_18032026m.ps_image;

DELETE FROM allstars01_18032026m.ps_search_index;
DELETE FROM allstars01_18032026m.ps_layered_price_index;
DELETE FROM allstars01_18032026m.ps_layered_product_attribute;
DELETE FROM allstars01_18032026m.ps_product_sale;
DELETE FROM allstars01_18032026m.ps_product_group_reduction_cache;
DELETE FROM allstars01_18032026m.ps_stock_available;

DELETE FROM allstars01_18032026m.ps_tag_count;
DELETE FROM allstars01_18032026m.ps_tag;
DELETE FROM allstars01_18032026m.ps_feature_value_lang;
DELETE FROM allstars01_18032026m.ps_feature_value;
DELETE FROM allstars01_18032026m.ps_feature_lang;
DELETE FROM allstars01_18032026m.ps_feature;

DELETE FROM allstars01_18032026m.ps_product_lang;
DELETE FROM allstars01_18032026m.ps_product_shop;
DELETE FROM allstars01_18032026m.ps_product;

/*
  Root products.
  Redirect mapping:
  - PS 1.6 "301" -> PS9 "301-product"
  - PS 1.6 "302" -> PS9 "302-product"
*/
INSERT INTO allstars01_18032026m.ps_product (
  id_product, id_supplier, id_manufacturer, id_category_default, id_shop_default,
  id_tax_rules_group, on_sale, online_only, ean13, isbn, upc, mpn, ecotax, quantity,
  minimal_quantity, low_stock_threshold, low_stock_alert, price, wholesale_price,
  unity, unit_price, unit_price_ratio, additional_shipping_cost, reference,
  supplier_reference, location, width, height, depth, weight, out_of_stock,
  additional_delivery_times, quantity_discount, customizable, uploadable_files,
  text_fields, active, redirect_type, id_product_redirected, id_type_redirected,
  available_for_order, available_date, show_condition, `condition`, show_price,
  indexed, visibility, cache_is_pack, cache_has_attachments, is_virtual,
  cache_default_attribute, date_add, date_upd, advanced_stock_management,
  pack_stock_type, availability, state, product_type
)
SELECT
  p.id_product,
  NULLIF(p.id_supplier, 0),
  NULLIF(p.id_manufacturer, 0),
  NULLIF(p.id_category_default, 0),
  2,
  CASE
    WHEN EXISTS (
      SELECT 1 FROM allstars01_18032026m.ps_tax_rules_group trg
      WHERE trg.id_tax_rules_group = p.id_tax_rules_group
    ) THEN p.id_tax_rules_group
    ELSE 5
  END,
  p.on_sale,
  p.online_only,
  NULLIF(p.ean13, ''),
  NULL,
  NULLIF(p.upc, ''),
  NULL,
  p.ecotax,
  p.quantity,
  p.minimal_quantity,
  NULL,
  0,
  p.price,
  p.wholesale_price,
  p.unity,
  CASE WHEN p.unit_price_ratio > 0 THEN ROUND(p.price / p.unit_price_ratio, 6) ELSE 0 END,
  p.unit_price_ratio,
  p.additional_shipping_cost,
  p.reference,
  p.supplier_reference,
  COALESCE(p.location, ''),
  p.width,
  p.height,
  p.depth,
  p.weight,
  p.out_of_stock,
  1,
  p.quantity_discount,
  p.customizable,
  p.uploadable_files,
  p.text_fields,
  1,
  CASE p.redirect_type
    WHEN '' THEN ''
    WHEN '404' THEN '404'
    WHEN '301' THEN '301-product'
    WHEN '302' THEN '302-product'
    ELSE 'default'
  END,
  p.id_product_redirected,
  0,
  1,
  p.available_date,
  0,
  p.`condition`,
  p.show_price,
  p.indexed,
  p.visibility,
  p.cache_is_pack,
  p.cache_has_attachments,
  p.is_virtual,
  p.cache_default_attribute,
  COALESCE(p.date_add, NOW()),
  COALESCE(p.date_upd, NOW()),
  p.advanced_stock_management,
  p.pack_stock_type,
  p.availability,
  1,
  CASE
    WHEN p.is_virtual = 1 THEN 'virtual'
    WHEN p.cache_is_pack = 1 THEN 'pack'
    WHEN EXISTS (
      SELECT 1 FROM tmp_migrate_product_attribute tpa
      WHERE tpa.id_product = p.id_product
    ) THEN 'combinations'
    ELSE 'standard'
  END
FROM allstar1_01062026.ps_product p
JOIN tmp_migrate_product mp ON mp.id_product = p.id_product;

INSERT INTO allstars01_18032026m.ps_product (
  id_product, id_supplier, id_manufacturer, id_category_default, id_shop_default,
  id_tax_rules_group, on_sale, online_only, ean13, isbn, upc, mpn, ecotax, quantity,
  minimal_quantity, low_stock_threshold, low_stock_alert, price, wholesale_price,
  unity, unit_price, unit_price_ratio, additional_shipping_cost, reference,
  supplier_reference, location, width, height, depth, weight, out_of_stock,
  additional_delivery_times, quantity_discount, customizable, uploadable_files,
  text_fields, active, redirect_type, id_product_redirected, id_type_redirected,
  available_for_order, available_date, show_condition, `condition`, show_price,
  indexed, visibility, cache_is_pack, cache_has_attachments, is_virtual,
  cache_default_attribute, date_add, date_upd, advanced_stock_management,
  pack_stock_type, availability, state, product_type
)
SELECT
  p.id_product,
  NULL,
  NULL,
  CASE
    WHEN EXISTS (
      SELECT 1 FROM allstars01_18032026m.ps_category c
      WHERE c.id_category = p.id_category_default
    ) THEN p.id_category_default
    ELSE 2
  END,
  3,
  CASE
    WHEN EXISTS (
      SELECT 1 FROM allstars01_18032026m.ps_tax_rules_group trg
      WHERE trg.id_tax_rules_group = p.id_tax_rules_group
    ) THEN p.id_tax_rules_group
    ELSE 5
  END,
  p.on_sale,
  p.online_only,
  NULLIF(p.ean13, ''),
  NULL,
  NULLIF(p.upc, ''),
  NULL,
  p.ecotax,
  p.quantity,
  p.minimal_quantity,
  NULL,
  0,
  p.price,
  p.wholesale_price,
  p.unity,
  CASE WHEN p.unit_price_ratio > 0 THEN ROUND(p.price / p.unit_price_ratio, 6) ELSE 0 END,
  p.unit_price_ratio,
  p.additional_shipping_cost,
  p.reference,
  p.supplier_reference,
  COALESCE(p.location, ''),
  p.width,
  p.height,
  p.depth,
  p.weight,
  p.out_of_stock,
  1,
  p.quantity_discount,
  p.customizable,
  p.uploadable_files,
  p.text_fields,
  1,
  CASE p.redirect_type
    WHEN '' THEN ''
    WHEN '404' THEN '404'
    WHEN '301' THEN '301-product'
    WHEN '302' THEN '302-product'
    ELSE 'default'
  END,
  p.id_product_redirected,
  0,
  1,
  p.available_date,
  0,
  p.`condition`,
  p.show_price,
  p.indexed,
  p.visibility,
  p.cache_is_pack,
  p.cache_has_attachments,
  p.is_virtual,
  p.cache_default_attribute,
  COALESCE(p.date_add, NOW()),
  COALESCE(p.date_upd, NOW()),
  p.advanced_stock_management,
  p.pack_stock_type,
  1,
  COALESCE(p.state, 1),
  CASE
    WHEN p.is_virtual = 1 THEN 'virtual'
    WHEN p.cache_is_pack = 1 THEN 'pack'
    ELSE 'standard'
  END
FROM allstarsdistribution.psnz_product p
JOIN tmp_migrate_distribution_product mdp
  ON mdp.id_product = p.id_product;

INSERT INTO allstars01_18032026m.ps_product_shop (
  id_product, id_shop, id_category_default, id_tax_rules_group, on_sale, online_only,
  ecotax, minimal_quantity, low_stock_threshold, low_stock_alert, price, wholesale_price,
  unity, unit_price, unit_price_ratio, additional_shipping_cost, customizable,
  uploadable_files, text_fields, active, redirect_type, id_type_redirected,
  available_for_order, available_date, show_condition, `condition`, show_price,
  indexed, visibility, cache_default_attribute, advanced_stock_management,
  date_add, date_upd, pack_stock_type, id_product_redirected
)
SELECT
  ps.id_product,
  s.id_shop,
  NULLIF(ps.id_category_default, 0),
  CASE
    WHEN EXISTS (
      SELECT 1 FROM allstars01_18032026m.ps_tax_rules_group trg
      WHERE trg.id_tax_rules_group = ps.id_tax_rules_group
    ) THEN ps.id_tax_rules_group
    ELSE 5
  END,
  ps.on_sale,
  ps.online_only,
  ps.ecotax,
  ps.minimal_quantity,
  NULL,
  0,
  ps.price,
  ps.wholesale_price,
  ps.unity,
  CASE WHEN ps.unit_price_ratio > 0 THEN ROUND(ps.price / ps.unit_price_ratio, 6) ELSE 0 END,
  ps.unit_price_ratio,
  ps.additional_shipping_cost,
  ps.customizable,
  ps.uploadable_files,
  ps.text_fields,
  1,
  CASE ps.redirect_type
    WHEN '' THEN ''
    WHEN '404' THEN '404'
    WHEN '301' THEN '301-product'
    WHEN '302' THEN '302-product'
    ELSE 'default'
  END,
  0,
  1,
  ps.available_date,
  1,
  ps.`condition`,
  ps.show_price,
  ps.indexed,
  ps.visibility,
  ps.cache_default_attribute,
  ps.advanced_stock_management,
  COALESCE(ps.date_add, NOW()),
  COALESCE(ps.date_upd, NOW()),
  ps.pack_stock_type,
  ps.id_product_redirected
FROM allstar1_01062026.ps_product_shop ps
JOIN tmp_migrate_product mp ON mp.id_product = ps.id_product
CROSS JOIN allstars01_18032026m.ps_shop s
WHERE s.id_shop IN (2, 3);

INSERT INTO allstars01_18032026m.ps_product_shop (
  id_product, id_shop, id_category_default, id_tax_rules_group, on_sale, online_only,
  ecotax, minimal_quantity, low_stock_threshold, low_stock_alert, price, wholesale_price,
  unity, unit_price, unit_price_ratio, additional_shipping_cost, customizable,
  uploadable_files, text_fields, active, redirect_type, id_type_redirected,
  available_for_order, available_date, show_condition, `condition`, show_price,
  indexed, visibility, cache_default_attribute, advanced_stock_management,
  date_add, date_upd, pack_stock_type, id_product_redirected
)
SELECT
  ps.id_product,
  s.id_shop,
  CASE
    WHEN EXISTS (
      SELECT 1 FROM allstars01_18032026m.ps_category c
      WHERE c.id_category = ps.id_category_default
    ) THEN ps.id_category_default
    ELSE 2
  END,
  CASE
    WHEN EXISTS (
      SELECT 1 FROM allstars01_18032026m.ps_tax_rules_group trg
      WHERE trg.id_tax_rules_group = ps.id_tax_rules_group
    ) THEN ps.id_tax_rules_group
    ELSE 5
  END,
  ps.on_sale,
  ps.online_only,
  ps.ecotax,
  ps.minimal_quantity,
  NULL,
  0,
  ps.price,
  ps.wholesale_price,
  ps.unity,
  CASE WHEN ps.unit_price_ratio > 0 THEN ROUND(ps.price / ps.unit_price_ratio, 6) ELSE 0 END,
  ps.unit_price_ratio,
  ps.additional_shipping_cost,
  ps.customizable,
  ps.uploadable_files,
  ps.text_fields,
  1,
  CASE ps.redirect_type
    WHEN '' THEN ''
    WHEN '404' THEN '404'
    WHEN '301' THEN '301-product'
    WHEN '302' THEN '302-product'
    ELSE 'default'
  END,
  0,
  1,
  ps.available_date,
  1,
  ps.`condition`,
  ps.show_price,
  ps.indexed,
  ps.visibility,
  ps.cache_default_attribute,
  ps.advanced_stock_management,
  COALESCE(ps.date_add, NOW()),
  COALESCE(ps.date_upd, NOW()),
  ps.pack_stock_type,
  ps.id_product_redirected
FROM allstarsdistribution.psnz_product_shop ps
JOIN tmp_migrate_distribution_product mdp
  ON mdp.id_product = ps.id_product
CROSS JOIN allstars01_18032026m.ps_shop s
WHERE s.id_shop = 3;

INSERT INTO allstars01_18032026m.ps_product_lang (
  id_product, id_shop, id_lang, description, description_short, link_rewrite,
  meta_description, meta_keywords, meta_title, name, available_now, available_later,
  available_soon_text, warranty_conditions, delivery_in_stock, delivery_out_stock
)
SELECT
  pl.id_product,
  s.id_shop,
  lm.target_id_lang,
  pl.description,
  pl.description_short,
  COALESCE(NULLIF(pl.link_rewrite, ''), CONCAT('product-', pl.id_product)),
  pl.meta_description,
  pl.meta_keywords,
  pl.meta_title,
  COALESCE(NULLIF(pl.name, ''), CONCAT('Product ', pl.id_product)),
  pl.available_now,
  pl.available_later,
  pl.available_soon_text,
  COALESCE(pl.warranty_conditions, ''),
  NULL,
  NULL
FROM allstar1_01062026.ps_product_lang pl
JOIN tmp_migrate_product mp ON mp.id_product = pl.id_product
JOIN allstars01_18032026m.ps_shop s ON s.id_shop IN (2, 3)
JOIN tmp_lang_fallback_a lm
  ON lm.source_id_lang = pl.id_lang;

INSERT INTO allstars01_18032026m.ps_product_lang (
  id_product, id_shop, id_lang, description, description_short, link_rewrite,
  meta_description, meta_keywords, meta_title, name, available_now, available_later,
  available_soon_text, warranty_conditions, delivery_in_stock, delivery_out_stock
)
SELECT
  pl.id_product,
  s.id_shop,
  lm.target_id_lang,
  pl.description,
  pl.description_short,
  COALESCE(NULLIF(pl.link_rewrite, ''), CONCAT('product-', pl.id_product)),
  pl.meta_description,
  pl.meta_keywords,
  pl.meta_title,
  COALESCE(NULLIF(pl.name, ''), CONCAT('Product ', pl.id_product)),
  pl.available_now,
  pl.available_later,
  NULL,
  '',
  NULL,
  NULL
FROM allstarsdistribution.psnz_product_lang pl
JOIN tmp_migrate_distribution_product mdp
  ON mdp.id_product = pl.id_product
JOIN allstars01_18032026m.ps_shop s ON s.id_shop = 3
JOIN tmp_lang_fallback_dist lm
  ON lm.source_id_lang = pl.id_lang;

/*
  Product extension.
  price_base_currency fields are mapped from the first old base-currency set:
  price_pound / wholesale_price_pound / price_displayed_pound.
*/
INSERT INTO allstars01_18032026m.ps_custom_product (
  id_product, image_code, stock_arrive, wmdeprecated, price_base_currency,
  wholesale_price_base_currency, price_display_base_currency, nc, discount_percentage,
  parcels, youtube_1, youtube_2, wmpending, universal, show_compat_exception,
  difficulty, ec_approved, not_to_order, disallow_stock, notes, dim_verify, wmpackqt,
  shipping_restrictions, real_photos, fc
)
SELECT
  p.id_product,
  NULL,
  COALESCE(p.stock_arrive, 0),
  COALESCE(p.wmdeprecated, 0),
  COALESCE(p.price_pound, 0),
  COALESCE(p.wholesale_price_pound, 0),
  COALESCE(p.price_displayed_pound, 0),
  LEFT(NULLIF(p.nc, ''), 10),
  COALESCE(p.discount_percentage, 0),
  COALESCE(p.parcels, 1),
  COALESCE(p.youtube_1, ''),
  COALESCE(p.youtube_2, ''),
  COALESCE(p.wmpending, 0),
  COALESCE(p.universal, 0),
  COALESCE(p.show_compat_exception, 0),
  COALESCE(p.difficulty, 0),
  0,
  COALESCE(p.not_to_order, 0),
  0,
  COALESCE(p.notes, ''),
  COALESCE(p.dim_verify, 0),
  COALESCE(p.wmpackqt, 0),
  COALESCE(p.shipping_restrictions, 0),
  COALESCE(p.real_photos, 0),
  COALESCE(p.fc, 0)
FROM allstar1_01062026.ps_product p
JOIN tmp_migrate_product mp ON mp.id_product = p.id_product;

INSERT INTO allstars01_18032026m.ps_custom_product (
  id_product, image_code, stock_arrive, wmdeprecated, price_base_currency,
  wholesale_price_base_currency, price_display_base_currency, nc, discount_percentage,
  parcels, youtube_1, youtube_2, wmpending, universal, show_compat_exception,
  difficulty, ec_approved, not_to_order, disallow_stock, notes, dim_verify, wmpackqt,
  shipping_restrictions, real_photos, fc
)
SELECT
  p.id_product,
  NULL,
  COALESCE(p.stock_arrive, 0),
  COALESCE(p.wmdeprecated, 0),
  COALESCE(p.price_pound, 0),
  COALESCE(p.wholesale_price_pound, 0),
  COALESCE(p.price_displayed_pound, 0),
  LEFT(NULLIF(p.nc, ''), 10),
  COALESCE(p.discount_percentage, 0),
  COALESCE(p.parcels, 1),
  '',
  '',
  0,
  0,
  0,
  0,
  0,
  0,
  COALESCE(p.disallow_stock, 0),
  COALESCE(p.notes, ''),
  0,
  COALESCE(p.wmpackqt, 0),
  0,
  0,
  0
FROM allstarsdistribution.psnz_product p
JOIN tmp_migrate_distribution_product mdp
  ON mdp.id_product = p.id_product;

/*
  Combinations.
*/
INSERT INTO allstars01_18032026m.ps_product_attribute (
  id_product_attribute, id_product, reference, supplier_reference, ean13, isbn, upc,
  mpn, wholesale_price, price, ecotax, weight, unit_price_impact, default_on,
  minimal_quantity, low_stock_threshold, low_stock_alert, available_date
)
SELECT
  pa.id_product_attribute,
  pa.id_product,
  pa.reference,
  pa.supplier_reference,
  NULLIF(pa.ean13, ''),
  NULL,
  NULLIF(pa.upc, ''),
  NULL,
  pa.wholesale_price,
  pa.price,
  pa.ecotax,
  pa.weight,
  pa.unit_price_impact,
  pa.default_on,
  pa.minimal_quantity,
  NULL,
  0,
  pa.available_date
FROM allstar1_01062026.ps_product_attribute pa
JOIN tmp_migrate_product_attribute mpa
  ON mpa.id_product_attribute = pa.id_product_attribute;

INSERT INTO allstars01_18032026m.ps_product_attribute_shop (
  id_product, id_product_attribute, id_shop, wholesale_price, price, ecotax, weight,
  unit_price_impact, default_on, minimal_quantity, low_stock_threshold, low_stock_alert,
  available_date
)
SELECT
  pas.id_product,
  pas.id_product_attribute,
  s.id_shop,
  pas.wholesale_price,
  pas.price,
  pas.ecotax,
  pas.weight,
  pas.unit_price_impact,
  pas.default_on,
  pas.minimal_quantity,
  NULL,
  0,
  pas.available_date
FROM allstar1_01062026.ps_product_attribute_shop pas
JOIN tmp_migrate_product_attribute mpa
  ON mpa.id_product_attribute = pas.id_product_attribute
CROSS JOIN allstars01_18032026m.ps_shop s
WHERE s.id_shop IN (2, 3);

INSERT INTO allstars01_18032026m.ps_product_attribute_combination (
  id_attribute, id_product_attribute
)
SELECT pac.id_attribute, pac.id_product_attribute
FROM allstar1_01062026.ps_product_attribute_combination pac
JOIN tmp_migrate_product_attribute mpa
  ON mpa.id_product_attribute = pac.id_product_attribute
WHERE EXISTS (
  SELECT 1 FROM allstars01_18032026m.ps_attribute a WHERE a.id_attribute = pac.id_attribute
);

INSERT INTO allstars01_18032026m.ps_product_attribute_image (
  id_product_attribute, id_image
)
SELECT pai.id_product_attribute, pai.id_image
FROM allstar1_01062026.ps_product_attribute_image pai
JOIN tmp_migrate_product_attribute mpa
  ON mpa.id_product_attribute = pai.id_product_attribute
JOIN tmp_migrate_image mi
  ON mi.id_image = pai.id_image;

INSERT INTO allstars01_18032026m.ps_product_attribute_lang (
  id_product_attribute, id_lang, available_now, available_later
)
SELECT DISTINCT
  pa.id_product_attribute,
  pl.id_lang,
  NULL,
  NULL
FROM allstars01_18032026m.ps_product_attribute pa
JOIN allstars01_18032026m.ps_product_lang pl
  ON pl.id_product = pa.id_product;

/*
  Combination extension.
  stock_arrive maps from old stock_arrivepa.
  wmdeprecated maps from old cdeprecated.
  price_base_currency fields are mapped from the first old base-currency set.
*/
INSERT INTO allstars01_18032026m.ps_custom_product_attribute (
  id_product_attribute, image_code, id_product, stock_arrive, wmdeprecated,
  price_base_currency, wholesale_price_base_currency, price_display_base_currency,
  location
)
SELECT
  pa.id_product_attribute,
  NULL,
  pa.id_product,
  COALESCE(pa.stock_arrivepa, 0),
  COALESCE(pa.cdeprecated, 0),
  COALESCE(pa.price_pound, 0),
  COALESCE(pa.wholesale_price_pound, 0),
  0,
  COALESCE(LEFT(NULLIF(pa.location, ''), 10), '')
FROM allstar1_01062026.ps_product_attribute pa
JOIN tmp_migrate_product_attribute mpa
  ON mpa.id_product_attribute = pa.id_product_attribute;

/*
  Product media.
*/
INSERT INTO allstars01_18032026m.ps_image (
  id_image, id_product, position, cover
)
SELECT i.id_image, i.id_product, i.position, i.cover
FROM allstar1_01062026.ps_image i
JOIN tmp_migrate_image mi ON mi.id_image = i.id_image;

INSERT INTO allstars01_18032026m.ps_image_lang (
  id_image, id_lang, legend
)
SELECT il.id_image, lm.target_id_lang, il.legend
FROM allstar1_01062026.ps_image_lang il
JOIN tmp_migrate_image mi ON mi.id_image = il.id_image
JOIN tmp_lang_fallback_a lm
  ON lm.source_id_lang = il.id_lang;

INSERT INTO allstars01_18032026m.ps_image_shop (
  id_product, id_image, id_shop, cover
)
SELECT ish.id_product, ish.id_image, s.id_shop, ish.cover
FROM allstar1_01062026.ps_image_shop ish
JOIN tmp_migrate_image mi ON mi.id_image = ish.id_image
CROSS JOIN allstars01_18032026m.ps_shop s
WHERE s.id_shop IN (2, 3);

/*
  Catalog relations.
*/
INSERT INTO allstars01_18032026m.ps_feature (
  id_feature, position
)
SELECT f.id_feature, f.position
FROM allstar1_01062026.ps_feature f
JOIN tmp_migrate_feature mf ON mf.id_feature = f.id_feature;

INSERT INTO allstars01_18032026m.ps_feature_lang (
  id_feature, id_lang, name
)
SELECT fl.id_feature, lm.target_id_lang, fl.name
FROM allstar1_01062026.ps_feature_lang fl
JOIN tmp_migrate_feature mf ON mf.id_feature = fl.id_feature
JOIN tmp_lang_fallback_a lm
  ON lm.source_id_lang = fl.id_lang;

INSERT INTO allstars01_18032026m.ps_feature_value (
  id_feature_value, id_feature, position, custom
)
SELECT fv.id_feature_value, fv.id_feature, fv.position, fv.custom
FROM allstar1_01062026.ps_feature_value fv
JOIN tmp_migrate_feature_value mfv
  ON mfv.id_feature_value = fv.id_feature_value;

INSERT INTO allstars01_18032026m.ps_feature_value_lang (
  id_feature_value, id_lang, value
)
SELECT fvl.id_feature_value, lm.target_id_lang, fvl.value
FROM allstar1_01062026.ps_feature_value_lang fvl
JOIN tmp_migrate_feature_value mfv
  ON mfv.id_feature_value = fvl.id_feature_value
JOIN tmp_lang_fallback_a lm
  ON lm.source_id_lang = fvl.id_lang;

INSERT INTO allstars01_18032026m.ps_category_product (
  id_category, id_product, position
)
SELECT cp.id_category, cp.id_product, cp.position
FROM allstar1_01062026.ps_category_product cp
JOIN tmp_migrate_product mp ON mp.id_product = cp.id_product
WHERE EXISTS (
  SELECT 1 FROM allstars01_18032026m.ps_category c WHERE c.id_category = cp.id_category
);

INSERT INTO allstars01_18032026m.ps_category_product (
  id_category, id_product, position
)
SELECT DISTINCT
  CASE
    WHEN EXISTS (
      SELECT 1 FROM allstars01_18032026m.ps_category c
      WHERE c.id_category = cp.id_category
    ) THEN cp.id_category
    ELSE 2
  END,
  cp.id_product,
  MIN(cp.position)
FROM allstarsdistribution.psnz_category_product cp
JOIN tmp_migrate_distribution_product mdp
  ON mdp.id_product = cp.id_product
GROUP BY
  CASE
    WHEN EXISTS (
      SELECT 1 FROM allstars01_18032026m.ps_category c
      WHERE c.id_category = cp.id_category
    ) THEN cp.id_category
    ELSE 2
  END,
  cp.id_product;

INSERT INTO allstars01_18032026m.ps_feature_product (
  id_feature, id_product, id_feature_value, position
)
SELECT fp.id_feature, fp.id_product, fp.id_feature_value, fp.position
FROM allstar1_01062026.ps_feature_product fp
JOIN tmp_migrate_product mp ON mp.id_product = fp.id_product
WHERE EXISTS (
  SELECT 1 FROM allstars01_18032026m.ps_feature f WHERE f.id_feature = fp.id_feature
)
AND EXISTS (
  SELECT 1 FROM allstars01_18032026m.ps_feature_value fv
  WHERE fv.id_feature_value = fp.id_feature_value
);

INSERT INTO allstars01_18032026m.ps_tag (
  id_tag, id_lang, name
)
SELECT t.id_tag, lm.target_id_lang, t.name
FROM allstar1_01062026.ps_tag t
JOIN tmp_migrate_tag mt
  ON mt.id_tag = t.id_tag
 AND mt.id_lang = t.id_lang
JOIN tmp_lang_exact_a lm
  ON lm.source_id_lang = t.id_lang;

INSERT INTO allstars01_18032026m.ps_product_tag (
  id_product, id_tag, id_lang
)
SELECT pt.id_product, pt.id_tag, lm.target_id_lang
FROM allstar1_01062026.ps_product_tag pt
JOIN tmp_migrate_product mp ON mp.id_product = pt.id_product
JOIN tmp_lang_exact_a lm
  ON lm.source_id_lang = pt.id_lang
WHERE EXISTS (
  SELECT 1 FROM allstars01_18032026m.ps_tag t WHERE t.id_tag = pt.id_tag
)
AND EXISTS (
  SELECT 1 FROM allstars01_18032026m.ps_tag t
  WHERE t.id_tag = pt.id_tag
    AND t.id_lang = lm.target_id_lang
);

INSERT INTO allstars01_18032026m.ps_product_supplier (
  id_product_supplier, id_product, id_product_attribute, id_supplier,
  product_supplier_reference, product_supplier_price_te, id_currency
)
SELECT
  psu.id_product_supplier,
  psu.id_product,
  psu.id_product_attribute,
  psu.id_supplier,
  psu.product_supplier_reference,
  psu.product_supplier_price_te,
  psu.id_currency
FROM allstar1_01062026.ps_product_supplier psu
JOIN tmp_migrate_product mp ON mp.id_product = psu.id_product
WHERE (psu.id_product_attribute = 0 OR EXISTS (
  SELECT 1 FROM tmp_migrate_product_attribute mpa
  WHERE mpa.id_product_attribute = psu.id_product_attribute
))
AND EXISTS (
  SELECT 1 FROM allstars01_18032026m.ps_supplier s WHERE s.id_supplier = psu.id_supplier
)
AND EXISTS (
  SELECT 1 FROM allstars01_18032026m.ps_currency c WHERE c.id_currency = psu.id_currency
);

INSERT INTO allstars01_18032026m.ps_accessory (
  id_product_1, id_product_2
)
SELECT a.id_product_1, a.id_product_2
FROM allstar1_01062026.ps_accessory a
JOIN tmp_migrate_product p1 ON p1.id_product = a.id_product_1
JOIN tmp_migrate_product p2 ON p2.id_product = a.id_product_2;

INSERT INTO allstars01_18032026m.ps_pack (
  id_product_pack, id_product_item, id_product_attribute_item, quantity
)
SELECT pk.id_product_pack, pk.id_product_item, pk.id_product_attribute_item, pk.quantity
FROM allstar1_01062026.ps_pack pk
JOIN tmp_migrate_product pack_product
  ON pack_product.id_product = pk.id_product_pack
JOIN tmp_migrate_product item_product
  ON item_product.id_product = pk.id_product_item
WHERE pk.id_product_attribute_item = 0 OR EXISTS (
  SELECT 1 FROM tmp_migrate_product_attribute mpa
  WHERE mpa.id_product_attribute = pk.id_product_attribute_item
);

INSERT INTO allstars01_18032026m.ps_product_attachment (
  id_product, id_attachment
)
SELECT pa.id_product, pa.id_attachment
FROM allstar1_01062026.ps_product_attachment pa
JOIN tmp_migrate_product mp ON mp.id_product = pa.id_product
WHERE EXISTS (
  SELECT 1 FROM allstars01_18032026m.ps_attachment a
  WHERE a.id_attachment = pa.id_attachment
);

INSERT INTO allstars01_18032026m.ps_product_download (
  id_product_download, id_product, display_filename, filename, date_add,
  date_expiration, nb_days_accessible, nb_downloadable, active, is_shareable
)
SELECT
  pd.id_product_download,
  pd.id_product,
  pd.display_filename,
  pd.filename,
  COALESCE(pd.date_add, NOW()),
  pd.date_expiration,
  pd.nb_days_accessible,
  pd.nb_downloadable,
  pd.active,
  pd.is_shareable
FROM allstar1_01062026.ps_product_download pd
JOIN tmp_migrate_product mp ON mp.id_product = pd.id_product;

INSERT INTO allstars01_18032026m.ps_product_carrier (
  id_product, id_carrier_reference, id_shop
)
SELECT pc.id_product, pc.id_carrier_reference, s.id_shop
FROM allstar1_01062026.ps_product_carrier pc
JOIN tmp_migrate_product mp ON mp.id_product = pc.id_product
CROSS JOIN allstars01_18032026m.ps_shop s
WHERE EXISTS (
  SELECT 1 FROM allstars01_18032026m.ps_carrier c
  WHERE c.id_reference = pc.id_carrier_reference
)
AND s.id_shop IN (2, 3);

COMMIT;

SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;

/*
  Validation queries to run after import.
*/
SELECT 'products_imported' AS metric, COUNT(*) AS value
FROM allstars01_18032026m.ps_product;

SELECT 'custom_product_rows' AS metric, COUNT(*) AS value
FROM allstars01_18032026m.ps_custom_product;

SELECT 'combinations_imported' AS metric, COUNT(*) AS value
FROM allstars01_18032026m.ps_product_attribute;

SELECT 'custom_combination_rows' AS metric, COUNT(*) AS value
FROM allstars01_18032026m.ps_custom_product_attribute;

SELECT 'orphan_product_lang' AS metric, COUNT(*) AS value
FROM allstars01_18032026m.ps_product_lang pl
LEFT JOIN allstars01_18032026m.ps_product p ON p.id_product = pl.id_product
WHERE p.id_product IS NULL;

SELECT 'orphan_product_attribute' AS metric, COUNT(*) AS value
FROM allstars01_18032026m.ps_product_attribute pa
LEFT JOIN allstars01_18032026m.ps_product p ON p.id_product = pa.id_product
WHERE p.id_product IS NULL;

SELECT 'orphan_custom_product' AS metric, COUNT(*) AS value
FROM allstars01_18032026m.ps_custom_product cp
LEFT JOIN allstars01_18032026m.ps_product p ON p.id_product = cp.id_product
WHERE p.id_product IS NULL;

SELECT 'orphan_custom_product_attribute' AS metric, COUNT(*) AS value
FROM allstars01_18032026m.ps_custom_product_attribute cpa
LEFT JOIN allstars01_18032026m.ps_product_attribute pa
  ON pa.id_product_attribute = cpa.id_product_attribute
WHERE pa.id_product_attribute IS NULL;
