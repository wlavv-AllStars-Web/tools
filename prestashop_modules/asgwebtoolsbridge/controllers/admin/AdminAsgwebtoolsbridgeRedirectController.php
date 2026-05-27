<?php

class AdminAsgwebtoolsbridgeRedirectController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initContent()
    {
        parent::initContent();

        try {
            $this->validateBridgeRequest();

            $targetController = trim((string) Tools::getValue('target_controller'));
            $targetParams = $this->decodeTargetParams((string) Tools::getValue('target_params'));
            $url = $this->buildAdminUrl($targetController, $targetParams);

            Tools::redirectAdmin($url);
        } catch (Exception $e) {
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

    private function decodeTargetParams($encodedParams)
    {
        $decoded = base64_decode($encodedParams, true);

        if ($decoded === false || $decoded === '') {
            return [];
        }

        $params = [];
        parse_str($decoded, $params);

        return is_array($params) ? $params : [];
    }

    private function buildAdminUrl($targetController, array $targetParams)
    {
        $link = $this->context->link;
        $reflection = new ReflectionMethod($link, 'getAdminLink');

        if ($reflection->getNumberOfParameters() >= 4) {
            return $link->getAdminLink($targetController, true, [], $targetParams);
        }

        $url = $link->getAdminLink($targetController);

        if (!empty($targetParams)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($targetParams);
        }

        return $url;
    }
}
