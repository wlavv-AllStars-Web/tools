-- Sales dashboard: products with extra vehicle compatibilities stored in ps_custom_product.extra_compats.
-- Safe to run more than once.

INSERT INTO `dashboard` (
    `tab`, `store`, `panel`, `name`, `function`, `description`,
    `counter`, `created_at`, `updated_at`
)
SELECT
    'sales',
    'ASM',
    'extra_compats',
    'EXTRA COMPATS',
    'product::dashboard_extra_compats',
    'Products with extra vehicle compatibilities stored in ps_custom_product.extra_compats.',
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1
    FROM `dashboard`
    WHERE `tab` = 'sales'
      AND `store` = 'ASM'
      AND `panel` = 'extra_compats'
);
