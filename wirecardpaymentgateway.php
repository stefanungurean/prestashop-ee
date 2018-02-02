<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

/**
 * Class WirecardEEPaymentGateway
 */
class WirecardPaymentGateway extends PaymentModule
{
    public function __construct()
    {
        $this->config = $this->config();
        $this->name = 'wirecardpaymentgateway';
        $this->tab = 'payments_gateways';
        $this->version = '0.0.2';
        $this->author = 'Wirecard';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => '1.7.2.4');
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Wirecard payment proccesing gateway');
        $this->description = $this->l('Wirecard payment methods.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install()
    {
        if (!parent::install() || !$this->setDefaults()) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        return true;
    }

    /**
     * returns the config array
     *
     * @since 0.0.2
     *
     * @return array
     */
    protected function config()
    {
        return array(
            'paypal' => array(
                'tab' => $this->l('PayPal'),
                'fields' => array(
                    array(
                        'name' => 'enable_method',
                        'label' => 'Enable and disable this payment method',
                        'default' => '0',
                        'type' => 'onoff'
                    ),
                    array(
                        'name' => 'wirecard_server_url',
                        'label' => $this->l('URL of Wirecard server'),
                        'type' => 'text',
                        'default' => 'https://api-test.wirecard.com',
                        'required' => true,
                        'sanitize' => 'trim'
                    ),
                    array(
                        'name' => 'maid',
                        'label' => $this->l('MAID'),
                        'type' => 'text',
                        'default' => '9abf05c1-c266-46ae-8eac-7f87ca97af28',
                        'required' => true,
                        'sanitize' => 'trim'
                    ),
                    array(
                        'name' => 'secret',
                        'label' => $this->l('Secret'),
                        'type' => 'text',
                        'default' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
                        'required' => true,
                        'sanitize' => 'trim'
                    ),
                    array(
                        'name' => 'http_user',
                        'label' => $this->l('HTTP user'),
                        'type' => 'text',
                        'default' => '70000-APITEST-AP',
                        'required' => true,
                        'sanitize' => 'trim'
                    ),
                    array(
                        'name' => 'http_password',
                        'label' => $this->l('HTTP Password'),
                        'type' => 'text',
                        'default' => 'qD2wzQ_hrc!8',
                        'required' => true,
                        'sanitize' => 'trim'
                    ),
                    array(
                        'name' => 'transaction_type',
                        'label' => $this->l('Transaction type'),
                        'type' => 'select',
                        'default' => 'purchase',
                        'required' => true,
                        'options' => 'getTransactionTypes'
                    )
                )
            )
        );
    }

    /**
     * @return string
     * @throws Exception
     * @throws SmartyException
     */
    public function getContent()
    {
        $this->html = '<h2>' . $this->displayName . '</h2>';

        if (Tools::isSubmit('btnSubmit')) {
            $this->postValidation();
            if (!count($this->postErrors)) {
                $this->postProcess();
            } else {
                foreach ($this->postErrors as $err) {
                    $this->html .= $this->displayError(html_entity_decode($err));
                }
            }
        }

        $this->context->smarty->assign(
            array(
                'module_dir' => $this->_path
            )
        );

        $this->html .= $this->context->smarty->fetch(
            dirname(__FILE__) . '/views/templates/admin/configuration.tpl'
        );
        $this->html .= $this->renderForm();

        return $this->html;
    }

    /**
     * render form
     *
     * @since 0.0.2
     *
     * @return string
     */
    private function renderForm()
    {
        $radio_type = 'switch';

        $radio_options = array(
            array(
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->l('Enabled')
            ),
            array(
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->l('Disabled')
            )
        );

        $input_fields = array();
        $tabs = array();

        foreach ($this->config as $groupKey => $group) {
            $tabs[$groupKey] = $this->l($group['tab']);
            foreach ($group['fields'] as $f) {
                $configGroup = isset($f['group']) ? $f['group'] : $groupKey;
                if (isset($f['class'])) {
                    $configGroup = 'pt';
                }

                $elem = array(
                    'name' => $this->buildParamName($configGroup, $f['name']),
                    'label' => $this->l($f['label']),
                    'tab' => $groupKey,
                    'type' => $f['type'],
                    'required' => isset($f['required']) && $f['required']
                );

                if (isset($f['cssclass'])) {
                    $elem['class'] = $f['cssclass'];
                }

                if (isset($f['doc'])) {
                    if (is_array($f['doc'])) {
                        $elem['desc'] = '';
                        foreach ($f['doc'] as $d) {
                            if (Tools::strlen($elem['desc'])) {
                                $elem['desc'] .= '<br/>';
                            }

                            $elem['desc'] .= $this->l($d);
                        }
                    } else {
                        $elem['desc'] = $this->l($f['doc']);
                    }
                }

                if (isset($f['docref'])) {
                    $elem['desc'] = isset($elem['desc']) ? $elem['desc'] . ' ' : '';
                    $elem['desc'] .= sprintf(
                        '<a target="_blank" href="%s">%s <i class="icon-external-link"></i></a>',
                        $f['docref'],
                        $this->l('More information')
                    );
                }

                switch ($f['type']) {
                    case 'text':
                        if (!isset($elem['class'])) {
                            $elem['class'] = 'fixed-width-xl';
                        }

                        if (isset($f['maxchar'])) {
                            $elem['maxlength'] = $elem['maxchar'] = $f['maxchar'];
                        }
                        break;

                    case 'onoff':
                        $elem['type'] = $radio_type;
                        $elem['class'] = 't';
                        $elem['is_bool'] = true;
                        $elem['values'] = $radio_options;
                        break;

                    case 'select':
                        if (isset($f['multiple'])) {
                            $elem['multiple'] = $f['multiple'];
                        }

                        if (isset($f['size'])) {
                            $elem['size'] = $f['size'];
                        }

                        if (isset($f['options'])) {
                            $optfunc = $f['options'];
                            $options = array();
                            if (is_array($optfunc)) {
                                $options = $optfunc;
                            }

                            if (method_exists($this, $optfunc)) {
                                $options = $this->$optfunc();
                            }

                            $elem['options'] = array(
                                'query' => $options,
                                'id' => 'key',
                                'name' => 'value'
                            );
                        }
                        break;

                    default:
                        break;
                }

                $input_fields[] = $elem;
            }
        }

        $fields_form_settings = array(
            'form' => array(
                'tabs' => $tabs,
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => $input_fields,
                'submit' => array(
                    'title' => $this->l('Save')
                )
            )
        );

        /** @var HelperFormCore $helper */
        $helper = new HelperForm();
        $helper->show_toolbar = false;

        /** @var LanguageCore $lang */
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get(
            'PS_BO_ALLOW_EMPLOYEE_FORM_LANG'
        ) : 0;
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm(array($fields_form_settings));
    }

    /**
     * build prestashop internal parameter name
     *
     * @since 0.0.2
     *
     * @param $group
     * @param $name
     *
     * @return string
     */
    protected function buildParamName($group, $name)
    {
        return sprintf(
            'WDEE_%s_%s',
            Tools::strtoupper($group),
            Tools::strtoupper($name)
        );
    }

    /**
     * return saved config parameter values
     *
     * @since 0.0.2
     *
     * @return array
     */
    public function getConfigFieldsValues()
    {
        $values = array();
        foreach ($this->getAllConfigurationParameters() as $parameter) {
            $val = Configuration::get($parameter['param_name']);
            if (isset($parameter['multiple']) && $parameter['multiple']) {
                if (!is_array($val)) {
                    $val = Tools::strlen($val) ? Tools::jsonDecode($val) : array();
                }

                $x = array();
                foreach ($val as $v) {
                    $x[$v] = $v;
                }
                $pname = $parameter['param_name'] . '[]';
                $values[$pname] = $x;
            } else {
                $values[$parameter['param_name']] = $val;
            }
        }

        return $values;
    }

    /**
     * return alls configuration parameters
     *
     * @since 0.0.2
     *
     * @return array
     */
    public function getAllConfigurationParameters()
    {
        $params = array();
        foreach ($this->config as $groupKey => $group) {
            foreach ($group['fields'] as $f) {
                $configGroup = isset($f['group']) ? $f['group'] : $groupKey;

                if (isset($f['class'])) {
                    $configGroup = 'pt';
                }

                $f['param_name'] = $this->buildParamName(
                    $configGroup,
                    $f['name']
                );
                $params[] = $f;
            }
        }

        return $params;
    }

    /**
     * validate post parameters
     *
     * @since 0.0.2
     *
     */
    private function postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $configmode = Tools::getValue('WCS_BASICDATA_CONFIGMODE');

            foreach ($this->getAllConfigurationParameters() as $parameter) {
                $val = Tools::getValue($parameter['param_name']);

                if (isset($parameter['sanitize'])) {
                    switch ($parameter['sanitize']) {
                        case 'trim':
                            $val = trim($val);
                            break;
                    }
                }

                if (isset($parameter['required']) && $parameter['required'] && !Tools::strlen($val)) {
                    if (in_array(
                        $parameter['name'],
                        array(
                            'customer_id',
                            'shop_id',
                            'secret',
                            'backendpw'
                        )
                    )) {
                        if ($configmode == 'production') {
                            $this->postErrors[] = $parameter['label'] . ' ' . $this->l('is required.');
                        }
                    } else {
                        $this->postErrors[] = $parameter['label'] . ' ' . $this->l('is required.');
                    }
                }

                if (!isset($parameter['validator'])) {
                    continue;
                }

                switch ($parameter['validator']) {
                    case 'numeric':
                        if (Tools::strlen($val) && !is_numeric($val)) {
                            $this->postErrors[] = $parameter['label'] . ' ' . $this->l(' must be a number.');
                        }
                        break;
                }
            }
        }
    }

    /**
     * process form post
     *
     * @since 0.0.2
     *
     */
    private function postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            foreach ($this->getAllConfigurationParameters() as $parameter) {
                $val = Tools::getValue($parameter['param_name']);

                if (isset($parameter['sanitize'])) {
                    switch ($parameter['sanitize']) {
                        case 'trim':
                            $val = trim($val);
                            break;
                    }
                }

                if (is_array($val)) {
                    $val = Tools::jsonEncode($val);
                }
                Configuration::updateValue($parameter['param_name'], $val);
            }
        }
        $this->html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    /**
     * set configuration value defaults
     *
     * @since 0.0.2
     *
     * @return bool
     */
    private function setDefaults()
    {
        foreach ($this->config as $groupKey => $group) {
            foreach ($group['fields'] as $f) {
                if (array_key_exists('default', $f)) {
                    $configGroup = isset($f['group']) ? $f['group'] : $groupKey;

                    if (isset($f['class'])) {
                        $configGroup = 'pt';
                    }
                    $p = $this->buildParamName($configGroup, $f['name']);
                    $defVal = $f['default'];
                    if (is_array($defVal)) {
                        $defVal = Tools::jsonEncode($defVal);
                    }

                    if (!Configuration::updateValue($p, $defVal)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * return options for transaction types select
     *
     * @return array
     */
    private function getTransactionTypes()
    {
        return array(
            array('key' => 'authorization', 'value' => $this->l('Authorization')),
            array('key' => 'purchase', 'value' => $this->l('Purchase'))
        );
    }
}
