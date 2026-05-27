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
            $decodedTargetParams = $this->decodeTargetParams($targetParams);

            $url = $this->buildDirectAdminUrl($targetController, $decodedTargetParams);

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

    private function buildDirectAdminUrl($targetController, array $targetParams)
    {
        $adminFolder = trim((string) Tools::getValue('admin_folder'), '/');

        if ($adminFolder === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $adminFolder)) {
            throw new Exception('Missing admin folder.');
        }

        $baseUrl = rtrim(Tools::getShopDomainSsl(true, true), '/');

        if ($targetController === 'AdminModulesSf' && !empty($targetParams['configure'])) {
            $moduleName = (string) $targetParams['configure'];
            unset($targetParams['configure'], $targetParams['module_name']);

            return $this->moduleConfigureUrl($baseUrl, $adminFolder, $moduleName, $targetParams);
        }

        throw new Exception('Unsupported direct target.');
    }

    private function moduleConfigureUrl($baseUrl, $adminFolder, $moduleName, array $queryParams)
    {
        $route = $this->generateSymfonyModuleConfigureUrl($moduleName, $queryParams);

        if ($route !== null) {
            return $route;
        }

        return $baseUrl
            . '/'
            . $adminFolder
            . '/index.php/improve/modules/manage/action/configure/'
            . rawurlencode($moduleName)
            . (!empty($queryParams) ? '?' . http_build_query($queryParams) : '');
    }

    private function generateSymfonyModuleConfigureUrl($moduleName, array $queryParams)
    {
        if (!class_exists('PrestaShop\PrestaShop\Adapter\SymfonyContainer')) {
            return null;
        }

        $container = \PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance();

        if (!$container || !$container->has('router')) {
            return null;
        }

        foreach (['admin_module_configure_action', 'admin_module_configure'] as $routeName) {
            try {
                $url = $container->get('router')->generate(
                    $routeName,
                    array_merge(['module_name' => $moduleName], $queryParams),
                    0
                );
            } catch (Throwable $e) {
                continue;
            }

            if (is_string($url) && $url !== '') {
                return strpos($url, '/') === 0
                    ? rtrim(Tools::getShopDomainSsl(true, true), '/') . $url
                    : $url;
            }
        }

        return null;
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
