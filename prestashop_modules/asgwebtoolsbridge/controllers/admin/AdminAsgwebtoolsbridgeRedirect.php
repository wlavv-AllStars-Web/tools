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

    private function buildAdminUrl($targetController, array $targetParams)
    {
        if ($targetController === 'AdminModulesSf' && !empty($targetParams['configure'])) {
            $moduleName = (string) $targetParams['configure'];
            unset($targetParams['configure'], $targetParams['module_name']);

            $url = $this->generateModuleConfigureUrl($moduleName, $targetParams);

            if ($url !== null) {
                return $url;
            }

            throw new Exception('Unable to generate module configure route inside Back Office.');
        }

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

    private function generateModuleConfigureUrl($moduleName, array $queryParams)
    {
        $routeParams = ['module_name' => $moduleName];
        $routeNames = [
            'admin_module_configure_action',
            'admin_module_configure',
        ];

        foreach ($routeNames as $routeName) {
            $url = $this->generateSymfonyRouteWithLink($routeName, $routeParams, $queryParams);

            if ($this->isValidModuleConfigureUrl($url, $moduleName)) {
                return $url;
            }
        }

        foreach ($routeNames as $routeName) {
            $url = $this->generateSymfonyRouteWithRouter($routeName, array_merge($routeParams, $queryParams));

            if ($this->isValidModuleConfigureUrl($url, $moduleName)) {
                return $url;
            }
        }

        return null;
    }

    private function generateSymfonyRouteWithLink($routeName, array $routeParams, array $queryParams)
    {
        if (!isset($this->context->link)) {
            return null;
        }

        try {
            $sfRouteParams = array_merge(['route' => $routeName], $routeParams);
            $reflection = new ReflectionMethod($this->context->link, 'getAdminLink');

            if ($reflection->getNumberOfParameters() >= 4) {
                $url = $this->context->link->getAdminLink('AdminModulesSf', true, $sfRouteParams, $queryParams);

                if ($this->isValidModuleConfigureUrl($url, $routeParams['module_name'])) {
                    return $url;
                }

                return $this->context->link->getAdminLink('AdminModulesSf', true, [], array_merge($sfRouteParams, $queryParams));
            }

            return $this->context->link->getAdminLink('AdminModulesSf', true, array_merge($sfRouteParams, $queryParams));
        } catch (Throwable $e) {
            return null;
        }
    }

    private function generateSymfonyRouteWithRouter($routeName, array $params)
    {
        if (!class_exists('PrestaShop\PrestaShop\Adapter\SymfonyContainer')) {
            return null;
        }

        $container = \PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance();

        if (!$container || !$container->has('router')) {
            return null;
        }

        try {
            $url = $container->get('router')->generate($routeName, $params, 0);
        } catch (Throwable $e) {
            return null;
        }

        if (is_string($url) && strpos($url, '/') === 0) {
            return rtrim(Tools::getShopDomainSsl(true, true), '/') . $url;
        }

        return $url;
    }

    private function isValidModuleConfigureUrl($url, $moduleName)
    {
        if (!is_string($url) || $url === '' || $moduleName === '') {
            return false;
        }

        return strpos($url, '/modules/manage/action/configure/' . rawurlencode($moduleName)) !== false
            || strpos($url, '/modules/manage/action/configure/' . $moduleName) !== false;
    }
}
