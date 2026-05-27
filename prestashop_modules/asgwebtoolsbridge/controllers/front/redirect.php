<?php

class AsgwebtoolsbridgeRedirectModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        try {
            $this->validateBridgeRequest();

            $targetController = trim((string) Tools::getValue('target_controller'));
            $targetParams = (string) Tools::getValue('target_params');
            $idEmployee = (int) Tools::getValue('id_employee');

            $url = $this->buildBridgeAdminUrl($targetController, $targetParams, $idEmployee);

            header('Location: ' . $url);
            exit;
        } catch (Throwable $e) {
            header('HTTP/1.1 403 Forbidden');
            exit($e->getMessage());
        }
    }

    private function validateBridgeRequest()
    {
        $token = (string) Configuration::get('ASGWEBTOOLSBRIDGE_TOKEN');
        $providedToken = (string) Tools::getValue('bridge_key');

        if ($token === '' || !hash_equals($token, $providedToken)) {
            throw new Exception('Invalid bridge token.');
        }

        $targetController = trim((string) Tools::getValue('target_controller'));

        if ($targetController === '') {
            throw new Exception('Missing target controller.');
        }

        $allowedControllers = array_filter(array_map('trim', explode(
            ',',
            (string) Configuration::get('ASGWEBTOOLSBRIDGE_ALLOWED_CONTROLLERS')
        )));

        if (!in_array($targetController, $allowedControllers, true)) {
            throw new Exception('Target controller is not allowed.');
        }

        if ((bool) Configuration::get('ASGWEBTOOLSBRIDGE_USE_HMAC')) {
            $this->validateHmac($targetController);
        }
    }

    private function validateHmac($targetController)
    {
        $targetParams = (string) Tools::getValue('target_params');
        $timestamp = (int) Tools::getValue('bridge_ts');
        $signature = (string) Tools::getValue('bridge_signature');
        $secret = (string) Configuration::get('ASGWEBTOOLSBRIDGE_HMAC_SECRET');

        if ($timestamp <= 0 || abs(time() - $timestamp) > 300) {
            throw new Exception('Bridge signature expired.');
        }

        if ($secret === '' || $signature === '') {
            throw new Exception('Missing bridge signature.');
        }

        $expected = hash_hmac('sha256', $targetController . '|' . $targetParams . '|' . $timestamp, $secret);

        if (!hash_equals($expected, $signature)) {
            throw new Exception('Invalid bridge signature.');
        }
    }

    private function buildBridgeAdminUrl($targetController, $targetParams, $idEmployee)
    {
        if ($idEmployee <= 0) {
            throw new Exception('Missing employee id.');
        }

        $adminFolder = trim((string) Tools::getValue('admin_folder'), '/');

        if ($adminFolder === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $adminFolder)) {
            throw new Exception('Missing admin folder.');
        }

        $idTab = (int) Tab::getIdFromClassName('AdminAsgwebtoolsbridgeRedirect');

        if ($idTab <= 0) {
            throw new Exception('Bridge admin controller is not installed.');
        }

        $baseUrl = rtrim(Tools::getShopDomainSsl(true, true), '/');
        $token = Tools::getAdminToken('AdminAsgwebtoolsbridgeRedirect' . $idTab . $idEmployee);

        $params = array_merge([
            'controller' => 'AdminAsgwebtoolsbridgeRedirect',
            'token' => $token,
            'bridge_key' => (string) Tools::getValue('bridge_key'),
            'target_controller' => $targetController,
            'target_params' => $targetParams,
        ], $this->hmacForwardParams());

        return $baseUrl . '/' . $adminFolder . '/index.php?' . http_build_query($params);
    }

    private function hmacForwardParams()
    {
        if (!(bool) Configuration::get('ASGWEBTOOLSBRIDGE_USE_HMAC')) {
            return [];
        }

        return [
            'bridge_ts' => (int) Tools::getValue('bridge_ts'),
            'bridge_signature' => (string) Tools::getValue('bridge_signature'),
        ];
    }
}
