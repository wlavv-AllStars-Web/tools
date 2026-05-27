<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Asgwebtoolsbridge extends Module
{
    public function __construct()
    {
        $this->name = 'asgwebtoolsbridge';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'All Stars';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Webtools Back Office Bridge');
        $this->description = $this->l('Allows Webtools to open selected Back Office pages through a signed bridge.');
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        return parent::install()
            && $this->installConfiguration()
            && $this->installTab();
    }

    public function uninstall()
    {
        return $this->uninstallTab()
            && Configuration::deleteByName('ASGWEBTOOLSBRIDGE_TOKEN')
            && Configuration::deleteByName('ASGWEBTOOLSBRIDGE_USE_HMAC')
            && Configuration::deleteByName('ASGWEBTOOLSBRIDGE_HMAC_SECRET')
            && Configuration::deleteByName('ASGWEBTOOLSBRIDGE_ALLOWED_CONTROLLERS')
            && parent::uninstall();
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitAsgwebtoolsbridge')) {
            Configuration::updateValue('ASGWEBTOOLSBRIDGE_TOKEN', trim((string) Tools::getValue('ASGWEBTOOLSBRIDGE_TOKEN')));
            Configuration::updateValue('ASGWEBTOOLSBRIDGE_USE_HMAC', (int) Tools::getValue('ASGWEBTOOLSBRIDGE_USE_HMAC'));
            Configuration::updateValue('ASGWEBTOOLSBRIDGE_HMAC_SECRET', trim((string) Tools::getValue('ASGWEBTOOLSBRIDGE_HMAC_SECRET')));
            Configuration::updateValue('ASGWEBTOOLSBRIDGE_ALLOWED_CONTROLLERS', trim((string) Tools::getValue('ASGWEBTOOLSBRIDGE_ALLOWED_CONTROLLERS')));

            $output .= $this->displayConfirmation($this->l('Settings updated.'));
        }

        return $output . $this->renderForm();
    }

    private function installConfiguration()
    {
        if (!Configuration::get('ASGWEBTOOLSBRIDGE_TOKEN')) {
            Configuration::updateValue('ASGWEBTOOLSBRIDGE_TOKEN', bin2hex(random_bytes(24)));
        }

        if (Configuration::get('ASGWEBTOOLSBRIDGE_USE_HMAC') === false) {
            Configuration::updateValue('ASGWEBTOOLSBRIDGE_USE_HMAC', 0);
        }

        if (!Configuration::get('ASGWEBTOOLSBRIDGE_HMAC_SECRET')) {
            Configuration::updateValue('ASGWEBTOOLSBRIDGE_HMAC_SECRET', bin2hex(random_bytes(32)));
        }

        if (!Configuration::get('ASGWEBTOOLSBRIDGE_ALLOWED_CONTROLLERS')) {
            Configuration::updateValue(
                'ASGWEBTOOLSBRIDGE_ALLOWED_CONTROLLERS',
                implode(',', [
                    'AdminProducts',
                    'AdminOrders',
                    'AdminCustomers',
                    'AdminCategories',
                    'AdminSuppliers',
                    'AdminManufacturers',
                    'AdminModules',
                    'AdminModulesSf',
                ])
            );
        }

        return true;
    }

    private function installTab()
    {
        $className = 'AdminAsgwebtoolsbridgeRedirect';

        if ((int) Tab::getIdFromClassName($className) > 0) {
            return true;
        }

        $tab = new Tab();
        $tab->active = 0;
        $tab->class_name = $className;
        $tab->id_parent = 0;
        $tab->module = $this->name;

        foreach (Language::getLanguages(false) as $language) {
            $tab->name[(int) $language['id_lang']] = 'Webtools Bridge';
        }

        return (bool) $tab->add();
    }

    private function uninstallTab()
    {
        $idTab = (int) Tab::getIdFromClassName('AdminAsgwebtoolsbridgeRedirect');

        if ($idTab <= 0) {
            return true;
        }

        $tab = new Tab($idTab);

        return (bool) $tab->delete();
    }

    private function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAsgwebtoolsbridge';
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value = [
            'ASGWEBTOOLSBRIDGE_TOKEN' => Configuration::get('ASGWEBTOOLSBRIDGE_TOKEN'),
            'ASGWEBTOOLSBRIDGE_USE_HMAC' => (int) Configuration::get('ASGWEBTOOLSBRIDGE_USE_HMAC'),
            'ASGWEBTOOLSBRIDGE_HMAC_SECRET' => Configuration::get('ASGWEBTOOLSBRIDGE_HMAC_SECRET'),
            'ASGWEBTOOLSBRIDGE_ALLOWED_CONTROLLERS' => Configuration::get('ASGWEBTOOLSBRIDGE_ALLOWED_CONTROLLERS'),
        ];

        return $helper->generateForm([[
            'form' => [
                'legend' => [
                    'title' => $this->l('Bridge settings'),
                    'icon' => 'icon-link',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Bridge token'),
                        'name' => 'ASGWEBTOOLSBRIDGE_TOKEN',
                        'required' => true,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Require HMAC signature'),
                        'name' => 'ASGWEBTOOLSBRIDGE_USE_HMAC',
                        'values' => [
                            ['id' => 'hmac_on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'hmac_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('HMAC secret'),
                        'name' => 'ASGWEBTOOLSBRIDGE_HMAC_SECRET',
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Allowed controllers'),
                        'name' => 'ASGWEBTOOLSBRIDGE_ALLOWED_CONTROLLERS',
                        'desc' => $this->l('Comma separated controller names.'),
                        'cols' => 80,
                        'rows' => 4,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ]]);
    }
}
