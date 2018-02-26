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
    const WDEE_OS_AWAITING = 'WDEE_OS_AWAITING';
    const WDEE_OS_FRAUD = 'WDEE_OS_FRAUD';
    private $postErrors;

    public function __construct()
    {
        ini_set(
            'include_path',
            ini_get('include_path')
            . PATH_SEPARATOR . realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor'
            . PATH_SEPARATOR . realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'models'
        );
        require_once 'wirecardee_autoload.php';

        $this->config = $this->config();
        $this->name = 'wirecardpaymentgateway';
        $this->tab = 'payments_gateways';
        $this->version = '0.0.3';
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
        if (!parent::install() || !$this->setDefaults()
            || !$this->registerHook('displayPaymentEU')
            || !$this->registerHook('actionFrontControllerSetMedia')
            || !$this->registerHook('displayHeader')) {
            return false;
        }
        if (!Configuration::get(self::WDEE_OS_AWAITING)) {

            /** @var OrderStateCore $orderState */
            $orderState = new OrderState();
            $orderState->name = array();
            foreach (Language::getLanguages() as $language) {
                $orderState->name[$language['id_lang']] = 'Checkout Wirecard Gateway payment awaiting';
            }
            $orderState->send_email = false;
            $orderState->color = 'lightblue';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = false;
            $orderState->invoice = false;
            $orderState->add();
            Configuration::updateValue(
                self::WDEE_OS_AWAITING,
                (int)($orderState->id)
            );
        }

        if (!Configuration::get(self::WDEE_OS_FRAUD)) {

            /** @var OrderStateCore $orderState */
            $orderState = new OrderState();
            $orderState->name = array();
            foreach (Language::getLanguages() as $language) {
                $orderState->name[$language['id_lang']] = 'Checkout Wirecard Gateway fraud detected';
            }
            $orderState->send_email = false;
            $orderState->color = '#8f0621';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = false;
            $orderState->invoice = false;
            $orderState->module_name = 'wirecardpaymentgateway';
            $orderState->add();

            Configuration::updateValue(
                self::WDEE_OS_FRAUD,
                (int)($orderState->id)
            );
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
     * @since 0.0.2
     *
     */

    public function hookDisplayPaymentEU($params)
    {
        if (!$this->active) {
            return;
        }
        $payment_options=array();

        foreach ($this->getPaymentTypes() as $paymentType) {
            if ($paymentType->isAvailable()) {
                $payment_options[] = array(
                    'cta_text' => $this->l($paymentType->getLabel()),
                    'logo' => Media::getMediaPath(
                        _PS_MODULE_DIR_ . $this->name . '/views/img/paymenttypes/'. $paymentType->getLogo()
                    ),
                    'action' => $this->context->link->getModuleLink(
                        $this->name,
                        'payment?paymentType='.$paymentType->getMethod(),
                        array(),
                        true
                    )
                );
            }
        }
        return $payment_options;
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
                        'label' => $this->l('Enable'),
                        'default' => '0',
                        'type' => 'onoff',
                        'className' => 'Paypal',
                        'logo' => 'paypal.png',
                        'labelMethod' => $this->l('Paypal'),

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
                    ),
                    array(
                        'name' => 'descriptor',
                        'label' => $this->l('Send descriptor'),
                        'default' => '1',
                        'type' => 'onoff',
                        'required' => true
                    ),
                    array(
                        'name' => 'basket_send',
                        'label' => $this->l('Send basket data'),
                        'default' => '0',
                        'type' => 'onoff',
                        'required' => true
                    ),
                    array(
                        'type' => 'linkbutton',
                        'required' => false,
                        'buttonText' => $this->l('Test paypal configuration'),
                        'id' => 'paypalConfig',
                        'method' => 'paypal',
                        'name' => 'paypal',
                        'send' => array(
                            $this->buildParamName('paypal', 'wirecard_server_url'),
                            $this->buildParamName('paypal', 'http_user'),
                            $this->buildParamName('paypal', 'http_password')
                        )
                    )
                )
            ),
            'sofort' => array(
                'tab' => $this->l('Sofort'),
                'fields' => array(
                    array(
                        'name' => 'enable_method',
                        'label' => $this->l('Enable'),
                        'default' => '0',
                        'type' => 'onoff',
                        'className' => 'Sofort',
                        'logo' => 'sofortbanking.png',
                        'labelMethod' => $this->l('Sofort'),

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
                        'default' => 'c021a23a-49a5-4987-aa39-e8e858d29bad',
                        'required' => true,
                        'sanitize' => 'trim'
                    ),
                    array(
                        'name' => 'secret',
                        'label' => $this->l('Secret'),
                        'type' => 'text',
                        'default' => 'dbc5a498-9a66-43b9-bf1d-a618dd39968',
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
                        'type' => 'linkbutton',
                        'required' => false,
                        'buttonText' => $this->l('Test sofort configuration'),
                        'id' => 'sofortConfig',
                        'method' => 'sofort',
                        'name' => 'sofort',
                        'send' => array(
                            $this->buildParamName('sofort', 'wirecard_server_url'),
                            $this->buildParamName('sofort', 'http_user'),
                            $this->buildParamName('sofort', 'http_password')
                        )
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
                'module_dir' => $this->_path,
                'ajax_configtest_url' => $this->context->link->getModuleLink('wirecardpaymentgateway', 'ajax')
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
                    'label' => isset($f['label'])?$this->l($f['label']):"",
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
                    case 'linkbutton':
                        $elem['buttonText'] = $f['buttonText'];
                        $elem['id'] = $f['id'];
                        $elem['method'] = $f['method'];
                        $elem['send'] = $f['send'];
                        break;

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
        $helper->module = $this;
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
    public function buildParamName($group, $name)
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
                    $this->postErrors[] = $parameter['label'] . ' ' . $this->l('is required.');
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
     * @since 0.0.2
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

    public function hookActionFrontControllerSetMedia($params)
    {
        $controllerArray = array('order');
        if (in_array($this->context->controller->php_self, $controllerArray)) {
            $this->context->controller->registerStylesheet(
                'module-' . $this->name . '-style',
                'modules/' . $this->name . '/views/css/style.css',
                array(
                    'media' => 'all',
                    'priority' => 200,
                )
            );
        }
    }

    /**
     * display error message after checkout failure
     */
    public function hookDisplayHeader()
    {
        $context = Context::getContext();
        $controller = $context->controller;
        if (is_object($controller)
            && (get_class($controller) == 'OrderController')
            && $context->cookie->eeMessage
        ) {
            if (strpos($context->cookie->eeMessage, '<br />')) {
                $msgs = explode('<br />', $context->cookie->eeMessage);
                foreach ($msgs as $msg) {
                    if (Tools::strlen($msg) < 5) {
                        continue;
                    }
                    $context->controller->errors[] = Tools::displayError(html_entity_decode($msg));
                }
            } else {
                $context->controller->errors[] = Tools::displayError(html_entity_decode($context->cookie->eeMessage));
            }
            unset($context->cookie->eeMessage);
        }
    }

    /**
     * return module display name
     *
     * @since 0.0.2
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * return module name
     *
     * @since 0.0.2
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $paymentType
     *
     * @return WirecardCheckoutSeamlessPayment |null
     */
    public function getPaymentType($paymentType)
    {
        $found = $this->getPaymentTypes($paymentType);
        if (count($found) != 1) {
            return null;
        }

        return $found[0];
    }

    /**
     * return paymenttype objects
     *
     * @param null $paymentType
     *
     * @return array
     */
    public function getPaymentTypes($paymentType = null)
    {
        $types = array();
        foreach ($this->config as $group) {
            foreach ($group['fields'] as $f) {
                if (array_key_exists('className', $f)) {
                    if ($paymentType !== null && (!isset($f['className'])||$f['className'] != $paymentType)) {
                        continue;
                    }

                    $className = 'WirecardPaymentGatewayPayment' . $f['className'];
                    $f['group'] = 'pt';
                    $pt = new $className($this, $f);

                    $types[] = $pt;
                }
            }
        }


        return $types;
    }

    public function initiatePayment($paymentTypeName = '')
    {
        try {
            if (!$this->active) {
                throw new Exception($this->l('Module is not active'));
            } elseif (!(Validate::isLoadedObject($this->getContext()->cart) &&
                !$this->getContext()->cart->OrderExists())) {
                throw new Exception($this->l(
                    'Cart cannot be loaded or an order has already been placed using this cart'
                ));
            } elseif (!$this->context->cookie->id_cart) {
                throw new Exception($this->l('Unable to load basket.'));
            }
            $paymentType = $this->getPaymentType($paymentTypeName);
            if ($paymentType === null) {
                throw new Exception($this->l('This payment method is not available.'));
            } elseif (!$paymentType->isAvailable()) {
                throw new Exception($this->l('Payment method not enabled.'));
            } elseif (!$paymentType->configuration()) {
                throw new Exception($this->l('The merchant configuration is incorrect'));
            }
            $validation = $paymentType->validations();
            if ($validation['status']!==true) {
                throw new Exception($this->l($validation['message']));
            }
            $orderNumber = $this->addOrder($this->getContext()->cart, $paymentType->getMethod());

            $paymentType->initiate($this->getContext()->cart, $orderNumber);
        } catch (Exception $e) {
            $message=$e->getMessage();
        }

        $params=array();
        if ($message!='') {
            if (isset($orderNumber)) {
                $this->updateOrder($orderNumber, _PS_OS_ERROR_);
            } else {
                $orderNumber="";
            }

            $this->module->getContext()->cookie->eeMessage = $message;
            $params = array(
                'submitReorder' => true,
                'id_order' => (int)$orderNumber
            );
        }
        Tools::redirect($this->getContext()->link->getPageLink(
            'order',
            true,
            $this->getContext()->cart->id_lang,
            $params
        ));
    }

    public function addOrder($cart, $paymentMethod)
    {
        $this->validateOrder(
            $cart->id,
            Configuration::get(self::WDEE_OS_AWAITING),
            $cart->getOrderTotal(true),
            $paymentMethod,
            null,
            array(),
            null,
            false,
            $cart->secure_key
        );
        return $this->currentOrder;
    }

    public function updateOrder($orderNumber, $orderStatus)
    {
        $history = new OrderHistory();
        $history->id_order = (int)$orderNumber;
        $history->changeIdOrderState(($orderStatus), $orderNumber, true);
    }

    /**
     * get context
     *
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * get config value, take presets into account
     *
     * @param $group
     * @param $field
     *
     * @return string
     */
    public function getConfigValue($group, $field)
    {
        if ($group == 'basicdata') {
            $mode = Configuration::get(
                $this->buildParamName(
                    'basicdata',
                    'configmode'
                )
            );

            if (isset($this->presets[$mode]) && isset($this->presets[$mode][$field])) {
                return $this->presets[$mode][$field];
            }
        }

        return Configuration::get($this->buildParamName($group, $field));
    }
}
