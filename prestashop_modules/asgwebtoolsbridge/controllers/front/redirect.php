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
            $targetParams = $this->decodeTargetParams((string) Tools::getValue('target_params'));
            $idEmployee = (int) Tools::getValue('id_employee');

            $url = $this->buildAdminUrl($targetController, $targetParams, $idEmployee);

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

    private function buildAdminUrl($targetController, array $targetParams, $idEmployee)
    {
        if ($idEmployee <= 0) {
            throw new Exception('Missing employee id.');
        }

        $idTab = (int) Tab::getIdFromClassName($targetController);

        if ($idTab <= 0 && $targetController === 'AdminModules') {
            $targetController = 'AdminModulesSf';
            $idTab = (int) Tab::getIdFromClassName($targetController);
        }

        if ($idTab <= 0) {
            throw new Exception('Unknown target controller.');
        }

        $adminFolder = trim((string) Tools::getValue('admin_folder'), '/');

        if ($adminFolder === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $adminFolder)) {
            throw new Exception('Missing admin folder.');
        }

        $this->ensureAdminDirConstant($adminFolder);

        $baseUrl = rtrim(Tools::getShopDomainSsl(true, true), '/');
        $token = Tools::getAdminToken($targetController . $idTab . $idEmployee);

        if ($targetController === 'AdminModulesSf' && !empty($targetParams['configure'])) {
            $moduleName = (string) $targetParams['configure'];
            unset($targetParams['configure'], $targetParams['module_name'], $targetParams['tab_module']);

            $symfonyUrl = $this->generateSymfonyRoute('admin_module_configure_action', array_merge([
                'module_name' => $moduleName,
            ], $targetParams));

            if ($symfonyUrl !== null) {
                return $symfonyUrl;
            }

            throw new Exception('Unable to generate module configure route.');
        }

        $params = array_merge([
            'controller' => $targetController,
            'token' => $token,
        ], $targetParams);

        return $baseUrl . '/' . $adminFolder . '/index.php?' . http_build_query($params);
    }

    private function ensureAdminDirConstant($adminFolder)
    {
        if (defined('_PS_ADMIN_DIR_')) {
            return;
        }

        $adminDir = rtrim(_PS_ROOT_DIR_, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $adminFolder;

        if (is_dir($adminDir)) {
            define('_PS_ADMIN_DIR_', $adminDir);
        }
    }

    private function generateSymfonyRoute($routeName, array $params)
    {
        $linkUrl = $this->generateSymfonyRouteWithLink($routeName, $params);

        if ($this->isValidModuleConfigureUrl($linkUrl, $params)) {
            return $linkUrl;
        }

        if (!class_exists('PrestaShop\PrestaShop\Adapter\SymfonyContainer')) {
            return null;
        }

        $container = \PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance();

        if (!$container || !$container->has('router')) {
            return null;
        }

        $routerUrl = $container->get('router')->generate($routeName, $params, 0);

        return $this->isValidModuleConfigureUrl($routerUrl, $params) ? $routerUrl : null;
    }

    private function generateSymfonyRouteWithLink($routeName, array $params)
    {
        if (!isset($this->context->link)) {
            return null;
        }

        try {
            $sfRouteParams = array_merge(['route' => $routeName], $params);
            $reflection = new ReflectionMethod($this->context->link, 'getAdminLink');

            if ($reflection->getNumberOfParameters() >= 4) {
                return $this->context->link->getAdminLink('AdminModulesSf', true, $sfRouteParams, []);
            }

            return $this->context->link->getAdminLink('AdminModulesSf', true, $sfRouteParams);
        } catch (Throwable $e) {
            return null;
        }
    }

    private function isValidModuleConfigureUrl($url, array $params)
    {
        if (!is_string($url) || $url === '') {
            return false;
        }

        $moduleName = isset($params['module_name']) ? (string) $params['module_name'] : '';

        if ($moduleName === '') {
            return false;
        }

        return strpos($url, '/modules/manage/action/configure/' . rawurlencode($moduleName)) !== false
            || strpos($url, '/modules/manage/action/configure/' . $moduleName) !== false;
    }
}
