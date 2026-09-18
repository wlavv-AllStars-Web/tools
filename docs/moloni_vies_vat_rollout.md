# Moloni deferred VIES VAT validation — rollout

## Webtools components included in this Git change

- API registration: `POST /api/moloni/vat-validations/orders`
- API for the PrestaShop order-detail notice: `GET /api/moloni/vat-validations/orders/{idOrder}`
- Web panel: `/web/moloni-vat`
- Scheduled commands:
  - `php artisan moloni-vat:validate-due --limit=25` (every minute)
  - `php artisan moloni-vat:reconcile --limit=250` (every 15 minutes)
- SQL to run first: [database/sql/moloni_vat_validation.sql](../database/sql/moloni_vat_validation.sql)

The Webtools API does not trust the VAT, country, customer or group sent by the module. It reads the order, billing address and customer groups again from PrestaShop. Only membership in group 4 (ASM professional) or group 6 (ASD dealer) creates a tracking record.

## Environment

Add one new secret to the Webtools environment, then clear Laravel's config cache on deployment:

```dotenv
ALLSTARS_MOLONI_VAT_API_TOKEN=<long-random-shared-secret>
MOLONI_VAT_VALID_DAYS=7
MOLONI_VAT_MAX_ATTEMPTS=6
MOLONI_VAT_RECONCILIATION_LOOKBACK_DAYS=14
```

The first value must exactly match the value configured in PrestaShop below. Do not reuse `TOOLS_KEY`.

## PrestaShop / Moloni configuration

Define these two constants in the PrestaShop deployment configuration, not in the module source:

```php
define('MOLONI_WEBTOOLS_VAT_ENDPOINT', 'https://webtools.all-stars-motorsport.com/api/moloni/vat-validations/orders');
define('MOLONI_WEBTOOLS_VAT_TOKEN', '<the same long random shared secret>');
```

## Changes in the live Moloni module

The supplied live copy places the relevant logic in:

- `modules/moloni/src/Classes/General.php`
- `modules/moloni/moloni.php`

### 1. Remove the synchronous VIES decision from invoice status

In `General.php`, inside `General::makeInvoice()`, locate the block beginning:

```php
// VIES: só relevante para M16 (intra-UE B2B). Se falhar => DRAFT
if ($fiscalMode === 'M16') {
```

and ending immediately before:

```php
// aplica status final (AUTO default = CLOSED, rebaixa para DRAFT se necessário)
```

Replace that VIES block with:

```php
// VIES is intentionally deferred to Webtools. It must never decide CLOSED vs DRAFT.
if ($fiscalMode === 'M16') {
    $log('AUTO_VIES_DEFERRED_TO_WEBTOOLS', [
        'order_id' => (int) $order_id,
        'reason' => 'Invoice issuance must not wait for VIES.',
    ], 1);
}
```

Keep the separate Canary/CIF logic unchanged. This change specifically removes VIES and missing-VAT checks as causes of a Moloni draft.

### 2. Notify Webtools after a successful invoice

In the same `General.php`, locate:

```php
private function dealWithDocumentSuccess(Order $orderPS, $documentProps, $documentId){
```

At the end of that method, after the existing `LoggerFacade::info(...)`, add:

```php
$this->queueViesVatValidation($orderPS, (int) $documentId);
```

Then add this private method immediately after `dealWithDocumentSuccess()`:

```php
private function queueViesVatValidation(Order $orderPS, int $documentId): void
{
    if (!defined('MOLONI_WEBTOOLS_VAT_ENDPOINT')
        || !defined('MOLONI_WEBTOOLS_VAT_TOKEN')
        || !function_exists('curl_init')) {
        return;
    }

    $groups = array_map('intval', (array) Customer::getGroupsStatic((int) $orderPS->id_customer));
    if (!array_intersect([4, 6], $groups)) {
        return;
    }

    $ch = curl_init(rtrim((string) MOLONI_WEBTOOLS_VAT_ENDPOINT, '/'));

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'id_order' => (int) $orderPS->id,
            'moloni_invoice_id' => $documentId,
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . MOLONI_WEBTOOLS_VAT_TOKEN,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 1,
        CURLOPT_TIMEOUT => 2,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        LoggerFacade::warning('Webtools VAT registration failed; reconciliation will retry it.', [
            'tag' => 'service:vies:deferred-registration',
            'orderId' => (int) $orderPS->id,
            'documentId' => $documentId,
            'httpCode' => $httpCode,
            'error' => $error,
        ]);
    }
}
```

This central success method covers normal creation and the module's successful recovery-by-reference paths. The call is deliberately fire-and-forget: a failed HTTP request cannot affect invoice issue or packing.

### 3. Display the visible PrestaShop order alert

In `moloni.php`, add this hook to the `install()` chain:

```php
&& $this->registerHook('displayAdminOrderSide')
```

Add the following method in the `Moloni` module class:

```php
public function hookDisplayAdminOrderSide($params)
{
    $idOrder = (int) ($params['id_order'] ?? Tools::getValue('id_order'));

    if ($idOrder <= 0
        || !defined('MOLONI_WEBTOOLS_VAT_ENDPOINT')
        || !defined('MOLONI_WEBTOOLS_VAT_TOKEN')
        || !function_exists('curl_init')) {
        return '';
    }

    $ch = curl_init(rtrim((string) MOLONI_WEBTOOLS_VAT_ENDPOINT, '/') . '/' . $idOrder);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 1,
        CURLOPT_TIMEOUT => 2,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . MOLONI_WEBTOOLS_VAT_TOKEN],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        return '';
    }

    $data = json_decode($response, true);
    if (empty($data['success']) || empty($data['eligible']) || empty($data['alert'])) {
        return '';
    }

    $message = Tools::safeOutput((string) ($data['message'] ?? 'VAT VIES requires attention.'));
    $detail = Tools::safeOutput((string) ($data['last_error'] ?? ''));

    return '<div class="alert alert-danger" style="margin-bottom:15px">'
        . '<strong>VAT VIES — atenção</strong><br>' . $message
        . ($detail !== '' ? '<br><small>' . $detail . '</small>' : '')
        . '</div>';
}
```

For an installed module, increment the module version (for example `3.0.5` to `3.0.6`) and add `modules/moloni/upgrade/upgrade-3.0.6.php`:

```php
<?php
function upgrade_module_3_0_6($module)
{
    return $module->registerHook('displayAdminOrderSide');
}
```

The `displayAdminOrderSide` hook places the alert in the order-detail side area, which is the upper-right column in the PrestaShop back office. No alert is displayed while the VAT has a fresh `valid` result; all other states show the warning without blocking the team's work.

## Initial operation

1. Deploy this Webtools change.
2. Execute the SQL file against the Webtools database.
3. Add the environment secret and deploy/clear Laravel config cache.
4. Apply the module changes and its upgrade script to live.
5. Run once after deployment:

```bash
php artisan moloni-vat:reconcile --since="2026-09-18 00:00:00" --limit=1000
php artisan moloni-vat:validate-due --limit=25
```

The reconciliation command is the safety net for missed HTTP posts. It scans only Moloni invoices in the configured lookback period and only customers currently in group 4 or 6.
