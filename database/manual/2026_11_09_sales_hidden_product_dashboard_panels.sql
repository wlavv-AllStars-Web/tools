-- Sales dashboard panels: active products hidden from their assigned store.
-- End-of-life products are excluded in the PHP query only when
-- wmdeprecated = 1 AND total available stock is below 1.

INSERT INTO `dashboard` (
    `tab`, `store`, `panel`, `name`, `function`, `description`,
    `counter`, `created_at`, `updated_at`
)
SELECT
    'sales',
    'ASM',
    'asm_active_hidden_products',
    'ASM ACTIVE PRODUCTS HIDDEN FROM STORE',
    'product::dashboard_asm_active_hidden_products',
    'Active ASM products with visibility set to none, excluding end-of-life products without stock.',
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1
    FROM `dashboard`
    WHERE `tab` = 'sales'
      AND `store` = 'ASM'
      AND `panel` = 'asm_active_hidden_products'
);

INSERT INTO `dashboard` (
    `tab`, `store`, `panel`, `name`, `function`, `description`,
    `counter`, `created_at`, `updated_at`
)
SELECT
    'sales',
    'ASD',
    'asd_active_hidden_products',
    'ASD ACTIVE PRODUCTS HIDDEN FROM STORE',
    'product::dashboard_asd_active_hidden_products',
    'Active ASD products with visibility set to none, excluding end-of-life products without stock.',
    0,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1
    FROM `dashboard`
    WHERE `tab` = 'sales'
      AND `store` = 'ASD'
      AND `panel` = 'asd_active_hidden_products'
);
