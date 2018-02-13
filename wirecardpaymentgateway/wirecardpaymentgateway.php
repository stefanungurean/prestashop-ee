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
use PrestaShop\PrestaShop\Adapter\StockManager;

/**
 * Class WirecardEEPaymentGateway
 */
class WirecardPaymentGateway extends PaymentModule
{
    const WDEE_OS_AWAITING = 'WDEE_OS_AWAITING';
    const WDEE_OS_FRAUD = 'WDEE_OS_FRAUD';

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
        $payment_options = array();
        if (Configuration::get($this->buildParamName('paypal', 'enable_method'))) {
            $payment_options[] = array(
                'cta_text' => $this->l('Paypal payment'),
                'logo' => Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/paymenttypes/paypal.png'),
                'action' => $this->context->link->getModuleLink($this->name, 'payment', array(), true)
            );
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
                    ),
                    array(
                        'name' => 'descriptior',
                        'label' => 'Enable and disable descriptior',
                        'default' => '1',
                        'type' => 'onoff',
                        'required' => true
                    ),
                    array(
                          'type' => 'linkbutton',
                          'required' => false,
                          'buttonText' => "Test paypal configuration",
                          'id' => "paypalConfig",
                          'method' => "paypal"
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
                    case 'linkbutton':
                        $elem['buttonText'] = $f['buttonText'];
                        $elem['id'] = $f['id'];
                        $elem['method'] = $f['method'];
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
            if (strpos($context->cookie->eeMessage, "<br />")) {
                $msgs = explode("<br />", $context->cookie->eeMessage);
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
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Validate an order in database
     * Function called from a payment module
     *
     * @param int     $id_cart
     * @param int     $id_order_state
     * @param float   $amount_paid       Amount really paid by customer (in the default currency)
     * @param string  $payment_method    Payment method (eg. 'Credit card')
     * @param null    $message           Message to attach to order
     * @param array   $extra_vars
     * @param null    $currency_special
     * @param bool    $dont_touch_amount
     * @param bool    $secure_key
     * @param Shop    $shop
     *
     * @return bool
     * @throws PrestaShopException
     */
    public function validateOrderImproved(
        $id_cart,
        $id_order_state,
        $amount_paid,
        $payment_method = 'Unknown',
        $message = null,
        $extra_vars = array(),
        $currency_special = null,
        $dont_touch_amount = false,
        $secure_key = false,
        Shop $shop = null
    )
    {
        if (self::DEBUG_MODE) {
            PrestaShopLogger::addLog(
                'PaymentModule::validateOrder - Function called',
                1,
                null,
                'Cart',
                (int)$id_cart,
                true
            );
        }

        if (!isset($this->context)) {
            $this->context = Context::getContext();
        }
        $this->context->cart = new Cart((int)$id_cart);
        $this->context->customer = new Customer((int)$this->context->cart->id_customer);
        // The tax cart is loaded before the customer so re-cache the tax calculation method
        $this->context->cart->setTaxCalculationMethod();

        $this->context->language = new Language((int)$this->context->cart->id_lang);
        $this->context->shop = ($shop ? $shop : new Shop((int)$this->context->cart->id_shop));
        // ShopUrl::resetMainDomainCache();
        $id_currency = $currency_special ? (int)$currency_special : (int)$this->context->cart->id_currency;
        $this->context->currency = new Currency((int)$id_currency, null, (int)$this->context->shop->id);
        if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery') {
            $context_country = $this->context->country;
        }

        $order_status = new OrderState((int)$id_order_state, (int)$this->context->language->id);
        if (!Validate::isLoadedObject($order_status)) {
            PrestaShopLogger::addLog(
                'PaymentModule::validateOrder - Order Status cannot be loaded',
                3,
                null,
                'Cart',
                (int)$id_cart,
                true
            );

            throw new PrestaShopException('Can\'t load Order status');
        }

        if (!$this->active) {
            PrestaShopLogger::addLog(
                'PaymentModule::validateOrder - Module is not active',
                3,
                null,
                'Cart',
                (int)$id_cart,
                true
            );

            throw new PrestaShopException('PaymentModule::validateOrder - Module is not active');
            //die(Tools::displayError());
            return;
        }

        // Does order already exists ?
        if (Validate::isLoadedObject($this->context->cart) && $this->context->cart->OrderExists() == false) {
            if ($secure_key !== false && $secure_key != $this->context->cart->secure_key) {
                PrestaShopLogger::addLog(
                    'PaymentModule::validateOrder - Secure key does not match',
                    3,
                    null,
                    'Cart',
                    (int)$id_cart,
                    true
                );
                throw new PrestaShopException('PaymentModule::validateOrder - Secure key does not match');
                return;
                //die(Tools::displayError());
            }

            // For each package, generate an order
            $delivery_option_list = $this->context->cart->getDeliveryOptionList();
            $package_list = $this->context->cart->getPackageList();
            $cart_delivery_option = $this->context->cart->getDeliveryOption();

            // If some delivery options are not defined, or not valid, use the first valid option
            foreach ($delivery_option_list as $id_address => $package) {
                if (!isset($cart_delivery_option[$id_address])
                    || !array_key_exists(
                        $cart_delivery_option[$id_address],
                        $package
                    )
                ) {
                    foreach ($package as $key => $val) {
                        $cart_delivery_option[$id_address] = $key;
                        break;
                    }
                }
            }

            $order_list = array();
            $order_detail_list = array();

            do {
                $reference = Order::generateReference();
            } while (Order::getByReference($reference)->count());

            $this->currentOrderReference = $reference;

            $cart_total_paid = (float)Tools::ps_round((float)$this->context->cart->getOrderTotal(true, Cart::BOTH), 2);

            foreach ($cart_delivery_option as $id_address => $key_carriers) {
                foreach ($delivery_option_list[$id_address][$key_carriers]['carrier_list'] as $id_carrier => $data) {
                    foreach ($data['package_list'] as $id_package) {
                        // Rewrite the id_warehouse
                        $package_list[$id_address][$id_package]['id_warehouse'] =
                            (int)$this->context->cart->getPackageIdWarehouse(
                                $package_list[$id_address][$id_package],
                                (int)$id_carrier
                            );
                        $package_list[$id_address][$id_package]['id_carrier'] = $id_carrier;
                    }
                }
            }

            foreach ($package_list as $id_address => $packageByAddress) {
                foreach ($packageByAddress as $id_package => $package) {
                    /** @var Order $order */
                    $order = new Order();
                    $order->product_list = $package['product_list'];

                    if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery') {
                        $address = new Address((int)$id_address);
                        $this->context->country = new Country(
                            (int)$address->id_country,
                            (int)$this->context->cart->id_lang
                        );
                        if (!$this->context->country->active) {
                            throw new PrestaShopException('The delivery address country is not active.');
                        }
                    }

                    $carrier = null;
                    if (!$this->context->cart->isVirtualCart() && isset($package['id_carrier'])) {
                        $carrier = new Carrier((int)$package['id_carrier'], (int)$this->context->cart->id_lang);
                        $order->id_carrier = (int)$carrier->id;
                        $id_carrier = (int)$carrier->id;
                    } else {
                        $order->id_carrier = 0;
                        $id_carrier = 0;
                    }

                    $order->id_customer = (int)$this->context->cart->id_customer;
                    $order->id_address_invoice = (int)$this->context->cart->id_address_invoice;
                    $order->id_address_delivery = (int)$id_address;
                    $order->id_currency = $this->context->currency->id;
                    $order->id_lang = (int)$this->context->cart->id_lang;
                    $order->id_cart = (int)$this->context->cart->id;
                    $order->reference = $reference;
                    $order->id_shop = (int)$this->context->shop->id;
                    $order->id_shop_group = (int)$this->context->shop->id_shop_group;

                    $order->secure_key = ($secure_key ? pSQL($secure_key) : pSQL($this->context->customer->secure_key));
                    $order->payment = $payment_method;
                    if (isset($this->name)) {
                        $order->module = $this->name;
                    }
                    $order->recyclable = $this->context->cart->recyclable;
                    $order->gift = (int)$this->context->cart->gift;
                    $order->gift_message = $this->context->cart->gift_message;
                    $order->mobile_theme = $this->context->cart->mobile_theme;
                    $order->conversion_rate = $this->context->currency->conversion_rate;
                    $amount_paid = !$dont_touch_amount ? Tools::ps_round((float)$amount_paid, 2) : $amount_paid;
                    $order->total_paid_real = 0;

                    $order->total_products = (float)$this->context->cart->getOrderTotal(
                        false,
                        Cart::ONLY_PRODUCTS,
                        $order->product_list,
                        $id_carrier
                    );
                    $order->total_products_wt = (float)$this->context->cart->getOrderTotal(
                        true,
                        Cart::ONLY_PRODUCTS,
                        $order->product_list,
                        $id_carrier
                    );
                    $order->total_discounts_tax_excl = (float)abs(
                        $this->context->cart->getOrderTotal(
                            false,
                            Cart::ONLY_DISCOUNTS,
                            $order->product_list,
                            $id_carrier
                        )
                    );
                    $order->total_discounts_tax_incl = (float)abs(
                        $this->context->cart->getOrderTotal(
                            true,
                            Cart::ONLY_DISCOUNTS,
                            $order->product_list,
                            $id_carrier
                        )
                    );
                    $order->total_discounts = $order->total_discounts_tax_incl;

                    $order->total_shipping_tax_excl = (float)$this->context->cart->getPackageShippingCost(
                        (int)$id_carrier,
                        false,
                        null,
                        $order->product_list
                    );
                    $order->total_shipping_tax_incl = (float)$this->context->cart->getPackageShippingCost(
                        (int)$id_carrier,
                        true,
                        null,
                        $order->product_list
                    );
                    $order->total_shipping = $order->total_shipping_tax_incl;

                    if (!is_null($carrier) && Validate::isLoadedObject($carrier)) {
                        $order->carrier_tax_rate = $carrier->getTaxesRate(
                            new Address((int)$this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')})
                        );
                    }

                    $order->total_wrapping_tax_excl = (float)abs(
                        $this->context->cart->getOrderTotal(
                            false,
                            Cart::ONLY_WRAPPING,
                            $order->product_list,
                            $id_carrier
                        )
                    );
                    $order->total_wrapping_tax_incl = (float)abs(
                        $this->context->cart->getOrderTotal(
                            true,
                            Cart::ONLY_WRAPPING,
                            $order->product_list,
                            $id_carrier
                        )
                    );
                    $order->total_wrapping = $order->total_wrapping_tax_incl;

                    $order->total_paid_tax_excl = (float)Tools::ps_round(
                        (float)$this->context->cart->getOrderTotal(
                            false,
                            Cart::BOTH,
                            $order->product_list,
                            $id_carrier),
                        _PS_PRICE_COMPUTE_PRECISION_
                    );
                    $order->total_paid_tax_incl = (float)Tools::ps_round(
                        (float)$this->context->cart->getOrderTotal(
                            true,
                            Cart::BOTH,
                            $order->product_list,
                            $id_carrier
                        ),
                        _PS_PRICE_COMPUTE_PRECISION_
                    );
                    $order->total_paid = $order->total_paid_tax_incl;
                    $order->round_mode = Configuration::get('PS_PRICE_ROUND_MODE');
                    $order->round_type = Configuration::get('PS_ROUND_TYPE');

                    $order->invoice_date = '0000-00-00 00:00:00';
                    $order->delivery_date = '0000-00-00 00:00:00';

                    if (self::DEBUG_MODE) {
                        PrestaShopLogger::addLog(
                            'PaymentModule::validateOrder - Order is about to be added',
                            1,
                            null,
                            'Cart',
                            (int)$id_cart,
                            true
                        );
                    }

                    // Creating order
                    $result = $order->add();

                    if (!$result) {
                        PrestaShopLogger::addLog('PaymentModule::validateOrder - Order cannot be created',
                            3,
                            null,
                            'Cart',
                            (int)$id_cart,
                            true
                        );
                        throw new PrestaShopException('Can\'t save Order');
                    }

                    // Amount paid by customer is not the right one -> Status = payment error
                    // We don't use the following condition to avoid the float precision issues :
                    // http://www.php.net/manual/en/language.types.float.php
                    // if ($order->total_paid != $order->total_paid_real)
                    // We use number_format in order to compare two string
                    if ($order_status->logable && number_format(
                        $cart_total_paid,
                        _PS_PRICE_COMPUTE_PRECISION_
                        )!= number_format(
                            $amount_paid,
                            _PS_PRICE_COMPUTE_PRECISION_
                        )) {
                        $id_order_state = Configuration::get('PS_OS_ERROR');
                    }

                    $order_list[] = $order;

                    if (self::DEBUG_MODE) {
                        PrestaShopLogger::addLog(
                            'PaymentModule::validateOrder - OrderDetail is about to be added',
                            1,
                            null,
                            'Cart',
                            (int)$id_cart,
                            true
                        );
                    }

                    // Insert new Order detail list using cart for the current order
                    $order_detail = new OrderDetail(null, null, $this->context);
                    $order_detail->createList(
                        $order,
                        $this->context->cart,
                        $id_order_state,
                        $order->product_list,
                        0,
                        true,
                        $package_list[$id_address][$id_package]['id_warehouse']
                    );
                    $order_detail_list[] = $order_detail;

                    if (self::DEBUG_MODE) {
                        PrestaShopLogger::addLog(
                            'PaymentModule::validateOrder - OrderCarrier is about to be added',
                            1,
                            null,
                            'Cart',
                            (int)$id_cart,
                            true
                        );
                    }

                    // Adding an entry in order_carrier table
                    if (!is_null($carrier)) {
                        $order_carrier = new OrderCarrier();
                        $order_carrier->id_order = (int)$order->id;
                        $order_carrier->id_carrier = (int)$id_carrier;
                        $order_carrier->weight = (float)$order->getTotalWeight();
                        $order_carrier->shipping_cost_tax_excl = (float)$order->total_shipping_tax_excl;
                        $order_carrier->shipping_cost_tax_incl = (float)$order->total_shipping_tax_incl;
                        $order_carrier->add();
                    }
                }
            }

            // The country can only change if the address used for the calculation is the delivery address,
            // and if multi-shipping is activated
            if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery') {
                $this->context->country = $context_country;
            }

            if (!$this->context->country->active) {
                PrestaShopLogger::addLog(
                    'PaymentModule::validateOrder - Country is not active',
                    3,
                    null,
                    'Cart',
                    (int)$id_cart,
                    true
                );
                throw new PrestaShopException('The order address country is not active.');
            }

            if (self::DEBUG_MODE) {
                PrestaShopLogger::addLog(
                    'PaymentModule::validateOrder - Payment is about to be added',
                    1,
                    null,
                    'Cart',
                    (int)$id_cart,
                    true
                );
            }

            // Register Payment only if the order status validate the order
            if ($order_status->logable) {
                // $order is the last order loop in the foreach
                // The method addOrderPayment of the class Order make a create a paymentOrder
                // linked to the order reference and not to the order id
                if (isset($extra_vars['transaction_id'])) {
                    $transaction_id = $extra_vars['transaction_id'];
                } else {
                    $transaction_id = null;
                }

                if (!$order->addOrderPayment($amount_paid, null, $transaction_id)) {
                    PrestaShopLogger::addLog(
                        'PaymentModule::validateOrder - Cannot save Order Payment',
                        3,
                        null,
                        'Cart',
                        (int)$id_cart,
                        true
                    );
                    throw new PrestaShopException('Can\'t save Order Payment');
                }
            }

            // Next !
            $only_one_gift = false;
            $cart_rule_used = array();
            $products = $this->context->cart->getProducts();

            // Make sure CartRule caches are empty
            CartRule::cleanCache();
            foreach ($order_detail_list as $key => $order_detail) {
                /** @var OrderDetail $order_detail */

                $order = $order_list[$key];
                if (isset($order->id)) {
                    if (!$secure_key) {
                        $message .= '<br />'
                            .$this->trans(
                                'Warning: the secure key is empty, check your payment account before validation',
                                array(),
                                'Admin.Payment.Notification'
                            );
                    }
                    // Optional message to attach to this order
                    if (isset($message) & !empty($message)) {
                        $msg = new Message();
                        $message = strip_tags($message, '<br>');
                        if (Validate::isCleanHtml($message)) {
                            if (self::DEBUG_MODE) {
                                PrestaShopLogger::addLog(
                                    'PaymentModule::validateOrder - Message is about to be added',
                                    1,
                                    null,
                                    'Cart',
                                    (int)$id_cart,
                                    true
                                );
                            }
                            $msg->message = $message;
                            $msg->id_cart = (int)$id_cart;
                            $msg->id_customer = (int)($order->id_customer);
                            $msg->id_order = (int)$order->id;
                            $msg->private = 1;
                            $msg->add();
                        }
                    }

                    // Insert new Order detail list using cart for the current order
                    //$orderDetail = new OrderDetail(null, null, $this->context);
                    //$orderDetail->createList($order, $this->context->cart, $id_order_state);

                    // Construct order detail table for the email
                    $products_list = '';
                    $virtual_product = true;

                    $product_var_tpl_list = array();
                    foreach ($order->product_list as $product) {
                        $price = Product::getPriceStatic(
                            (int)$product['id_product'],
                            false,
                            ($product['id_product_attribute'] ? (int)$product['id_product_attribute'] : null),
                            6,
                            null,
                            false,
                            true,
                            $product['cart_quantity'],
                            false,
                            (int)$order->id_customer,
                            (int)$order->id_cart,
                            (int)$order->{Configuration::get('PS_TAX_ADDRESS_TYPE')},
                            $specific_price,
                            true,
                            true,
                            null,
                            true,
                            $product['id_customization']
                        );
                        $price_wt = Product::getPriceStatic(
                            (int)$product['id_product'],
                            true,
                            ($product['id_product_attribute'] ? (int)$product['id_product_attribute'] : null),
                            2,
                            null,
                            false,
                            true,
                            $product['cart_quantity'],
                            false,
                            (int)$order->id_customer,
                            (int)$order->id_cart,
                            (int)$order->{Configuration::get('PS_TAX_ADDRESS_TYPE')},
                            $specific_price,
                            true,
                            true,
                            null,
                            true,
                            $product['id_customization']
                        );

                        $product_price = Product::getTaxCalculationMethod() == PS_TAX_EXC ?
                            Tools::ps_round($price, 2) :
                            $price_wt;

                        $product_var_tpl = array(
                            'id_product' => $product['id_product'],
                            'reference' => $product['reference'],
                            'name' => $product['name'].(
                                isset($product['attributes']) ?
                                    ' - '.$product['attributes'] :
                                    ''
                                ),
                            'price' => Tools::displayPrice(
                                $product_price * $product['quantity'],
                                $this->context->currency,
                                false
                            ),
                            'quantity' => $product['quantity'],
                            'customization' => array()
                        );

                        if (isset($product['price']) && $product['price']) {
                            $product_var_tpl['unit_price'] = Tools::displayPrice(
                                $product['price'],
                                $this->context->currency,
                                false
                            );
                            $product_var_tpl['unit_price_full'] = Tools::displayPrice(
                                $product['price'],
                                $this->context->currency,
                                false
                                )
                                .' '.$product['unity'];
                        } else {
                            $product_var_tpl['unit_price'] = $product_var_tpl['unit_price_full'] = '';
                        }

                        $customized_datas = Product::getAllCustomizedDatas(
                            (int)$order->id_cart,
                            null,
                            true,
                            null,
                            (int)$product['id_customization']
                        );
                        if (isset($customized_datas[$product['id_product']][$product['id_product_attribute']])) {
                            $product_var_tpl['customization'] = array();
                            foreach ($customized_datas[$product['id_product']][$product['id_product_attribute']][$order->id_address_delivery] as $customization) {
                                $customization_text = '';
                                if (isset($customization['datas'][Product::CUSTOMIZE_TEXTFIELD])) {
                                    foreach ($customization['datas'][Product::CUSTOMIZE_TEXTFIELD] as $text) {
                                        $customization_text .= '<strong>'
                                            .$text['name']
                                            .'</strong>: '
                                            .$text['value']
                                            .'<br />';
                                    }
                                }

                                if (isset($customization['datas'][Product::CUSTOMIZE_FILE])) {
                                    $customization_text .= $this->trans(
                                        '%d image(s)',
                                        array(count($customization['datas'][Product::CUSTOMIZE_FILE])),
                                        'Admin.Payment.Notification'
                                        )
                                        .'<br />';
                                }

                                $customization_quantity = (int)$customization['quantity'];

                                $product_var_tpl['customization'][] = array(
                                    'customization_text' => $customization_text,
                                    'customization_quantity' => $customization_quantity,
                                    'quantity' => Tools::displayPrice(
                                        $customization_quantity * $product_price,
                                        $this->context->currency,
                                        false
                                    )
                                );
                            }
                        }

                        $product_var_tpl_list[] = $product_var_tpl;
                        // Check if is not a virutal product for the displaying of shipping
                        if (!$product['is_virtual']) {
                            $virtual_product &= false;
                        }
                    } // end foreach ($products)

                    $product_list_txt = '';
                    $product_list_html = '';
                    if (count($product_var_tpl_list) > 0) {
                        $product_list_txt = $this->getEmailTemplateContent(
                            'order_conf_product_list.txt',
                            Mail::TYPE_TEXT,
                            $product_var_tpl_list
                        );
                        $product_list_html = $this->getEmailTemplateContent(
                            'order_conf_product_list.tpl',
                            Mail::TYPE_HTML,
                            $product_var_tpl_list
                        );
                    }

                    $cart_rules_list = array();

                    $cart_rules_list_txt = '';
                    $cart_rules_list_html = '';
                    if (count($cart_rules_list) > 0) {
                        $cart_rules_list_txt = $this->getEmailTemplateContent(
                            'order_conf_cart_rules.txt',
                            Mail::TYPE_TEXT,
                            $cart_rules_list
                        );
                        $cart_rules_list_html = $this->getEmailTemplateContent(
                            'order_conf_cart_rules.tpl',
                            Mail::TYPE_HTML,
                            $cart_rules_list
                        );
                    }

                    // Specify order id for message
                    $old_message = Message::getMessageByCartId((int)$this->context->cart->id);
                    if ($old_message && !$old_message['private']) {
                        $update_message = new Message((int)$old_message['id_message']);
                        $update_message->id_order = (int)$order->id;
                        $update_message->update();

                        // Add this message in the customer thread
                        $customer_thread = new CustomerThread();
                        $customer_thread->id_contact = 0;
                        $customer_thread->id_customer = (int)$order->id_customer;
                        $customer_thread->id_shop = (int)$this->context->shop->id;
                        $customer_thread->id_order = (int)$order->id;
                        $customer_thread->id_lang = (int)$this->context->language->id;
                        $customer_thread->email = $this->context->customer->email;
                        $customer_thread->status = 'open';
                        $customer_thread->token = Tools::passwdGen(12);
                        $customer_thread->add();

                        $customer_message = new CustomerMessage();
                        $customer_message->id_customer_thread = $customer_thread->id;
                        $customer_message->id_employee = 0;
                        $customer_message->message = $update_message->message;
                        $customer_message->private = 1;

                        if (!$customer_message->add()) {
                            $this->errors[] = $this->trans(
                                'An error occurred while saving message',
                                array(),
                                'Admin.Payment.Notification'
                            );
                        }
                    }

                    if (self::DEBUG_MODE) {
                        PrestaShopLogger::addLog(
                            'PaymentModule::validateOrder - Hook validateOrder is about to be called',
                            1,
                            null,
                            'Cart',
                            (int)$id_cart,
                            true
                        );
                    }

                    // Hook validate order
                    Hook::exec('actionValidateOrder', array(
                        'cart' => $this->context->cart,
                        'order' => $order,
                        'customer' => $this->context->customer,
                        'currency' => $this->context->currency,
                        'orderStatus' => $order_status
                    ));

                    foreach ($this->context->cart->getProducts() as $product) {
                        if ($order_status->logable) {
                            ProductSale::addProductSale((int)$product['id_product'], (int)$product['cart_quantity']);
                        }
                    }

                    if (self::DEBUG_MODE) {
                        PrestaShopLogger::addLog(
                            'PaymentModule::validateOrder - Order Status is about to be added',
                            1,
                            null,
                            'Cart',
                            (int)$id_cart,
                            true
                        );
                    }

                    // Set the order status
                    $new_history = new OrderHistory();
                    $new_history->id_order = (int)$order->id;
                    $new_history->changeIdOrderState((int)$id_order_state, $order, true);
                    $new_history->addWithemail(true, $extra_vars);

                    // Switch to back order if needed
                    if (Configuration::get('PS_STOCK_MANAGEMENT') &&
                        ($order_detail->getStockState() ||
                            $order_detail->product_quantity_in_stock <= 0)) {
                        $history = new OrderHistory();
                        $history->id_order = (int)$order->id;
                        $history->changeIdOrderState(
                            Configuration::get($order->valid ? 'PS_OS_OUTOFSTOCK_PAID' : 'PS_OS_OUTOFSTOCK_UNPAID'),
                            $order,
                            true
                        );
                        $history->addWithemail();
                    }

                    unset($order_detail);

                    // Order is reloaded because the status just changed
                    $order = new Order((int)$order->id);

                    // Send an e-mail to customer (one order = one email)
                    if (
                        $id_order_state != Configuration::get('PS_OS_ERROR') &&
                        $id_order_state != Configuration::get('PS_OS_CANCELED') &&
                        $this->context->customer->id) {
                        $invoice = new Address((int)$order->id_address_invoice);
                        $delivery = new Address((int)$order->id_address_delivery);
                        $delivery_state = $delivery->id_state ? new State((int)$delivery->id_state) : false;
                        $invoice_state = $invoice->id_state ? new State((int)$invoice->id_state) : false;

                        $data = array(
                            '{firstname}' => $this->context->customer->firstname,
                            '{lastname}' => $this->context->customer->lastname,
                            '{email}' => $this->context->customer->email,
                            '{delivery_block_txt}' => $this->_getFormatedAddress($delivery, "\n"),
                            '{invoice_block_txt}' => $this->_getFormatedAddress($invoice, "\n"),
                            '{delivery_block_html}' => $this->_getFormatedAddress($delivery, '<br />', array(
                                'firstname'    => '<span style="font-weight:bold;">%s</span>',
                                'lastname'    => '<span style="font-weight:bold;">%s</span>'
                            )),
                            '{invoice_block_html}' => $this->_getFormatedAddress($invoice, '<br />', array(
                                'firstname'    => '<span style="font-weight:bold;">%s</span>',
                                'lastname'    => '<span style="font-weight:bold;">%s</span>'
                            )),
                            '{delivery_company}' => $delivery->company,
                            '{delivery_firstname}' => $delivery->firstname,
                            '{delivery_lastname}' => $delivery->lastname,
                            '{delivery_address1}' => $delivery->address1,
                            '{delivery_address2}' => $delivery->address2,
                            '{delivery_city}' => $delivery->city,
                            '{delivery_postal_code}' => $delivery->postcode,
                            '{delivery_country}' => $delivery->country,
                            '{delivery_state}' => $delivery->id_state ? $delivery_state->name : '',
                            '{delivery_phone}' => ($delivery->phone) ? $delivery->phone : $delivery->phone_mobile,
                            '{delivery_other}' => $delivery->other,
                            '{invoice_company}' => $invoice->company,
                            '{invoice_vat_number}' => $invoice->vat_number,
                            '{invoice_firstname}' => $invoice->firstname,
                            '{invoice_lastname}' => $invoice->lastname,
                            '{invoice_address2}' => $invoice->address2,
                            '{invoice_address1}' => $invoice->address1,
                            '{invoice_city}' => $invoice->city,
                            '{invoice_postal_code}' => $invoice->postcode,
                            '{invoice_country}' => $invoice->country,
                            '{invoice_state}' => $invoice->id_state ? $invoice_state->name : '',
                            '{invoice_phone}' => ($invoice->phone) ? $invoice->phone : $invoice->phone_mobile,
                            '{invoice_other}' => $invoice->other,
                            '{order_name}' => $order->getUniqReference(),
                            '{date}' => Tools::displayDate(date('Y-m-d H:i:s'), null, 1),
                            '{carrier}' => (
                                $virtual_product || !isset($carrier->name)) ?
                                $this->trans(
                                    'No carrier',
                                    array(),
                                    'Admin.Payment.Notification'
                                ) :
                                $carrier->name,
                            '{payment}' => Tools::substr($order->payment, 0, 255),
                            '{products}' => $product_list_html,
                            '{products_txt}' => $product_list_txt,
                            '{discounts}' => $cart_rules_list_html,
                            '{discounts_txt}' => $cart_rules_list_txt,
                            '{total_paid}' => Tools::displayPrice(
                                $order->total_paid,
                                $this->context->currency,
                                false
                            ),
                            '{total_products}' => Tools::displayPrice(
                                Product::getTaxCalculationMethod() == PS_TAX_EXC ?
                                    $order->total_products :
                                    $order->total_products_wt,
                                $this->context->currency,
                                false),
                            '{total_discounts}' => Tools::displayPrice(
                                $order->total_discounts,
                                $this->context->currency,
                                false
                            ),
                            '{total_shipping}' => Tools::displayPrice(
                                $order->total_shipping,
                                $this->context->currency,
                                false
                            ),
                            '{total_wrapping}' => Tools::displayPrice(
                                $order->total_wrapping,
                                $this->context->currency,
                                false
                            ),
                            '{total_tax_paid}' => Tools::displayPrice(
                                ($order->total_products_wt - $order->total_products) +
                                ($order->total_shipping_tax_incl - $order->total_shipping_tax_excl),
                                $this->context->currency,
                                false));

                        if (is_array($extra_vars)) {
                            $data = array_merge($data, $extra_vars);
                        }

                        // Join PDF invoice
                        if ((int)Configuration::get('PS_INVOICE') && $order_status->invoice && $order->invoice_number) {
                            $order_invoice_list = $order->getInvoicesCollection();
                            Hook::exec('actionPDFInvoiceRender', array('order_invoice_list' => $order_invoice_list));
                            $pdf = new PDF($order_invoice_list, PDF::TEMPLATE_INVOICE, $this->context->smarty);
                            $file_attachement['content'] = $pdf->render(false);
                            $file_attachement['name'] = Configuration::get(
                                'PS_INVOICE_PREFIX',
                                (int)$order->id_lang,
                                null,
                                $order->id_shop
                                )
                                .sprintf(
                                    '%06d',
                                    $order->invoice_number
                                )
                                .'.pdf';
                            $file_attachement['mime'] = 'application/pdf';
                        } else {
                            $file_attachement = null;
                        }

                        if (self::DEBUG_MODE) {
                            PrestaShopLogger::addLog('PaymentModule::validateOrder - Mail is about to be sent',
                                1,
                                null,
                                'Cart',
                                (int)$id_cart,
                                true
                            );
                        }

                        $orderLanguage = new Language((int) $order->id_lang);

                        if (Validate::isEmail($this->context->customer->email)) {
                            Mail::Send(
                                (int)$order->id_lang,
                                'order_conf',
                                Context::getContext()->getTranslator()->trans(
                                    'Order confirmation',
                                    array(),
                                    'Emails.Subject',
                                    $orderLanguage->locale
                                ),
                                $data,
                                $this->context->customer->email,
                                $this->context->customer->firstname.' '.$this->context->customer->lastname,
                                null,
                                null,
                                $file_attachement,
                                null,
                                _PS_MAIL_DIR_,
                                false,
                                (int)$order->id_shop
                            );
                        }
                    }

                    // updates stock in shops
                    if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                        $product_list = $order->getProducts();
                        foreach ($product_list as $product) {
                            // if the available quantities depends on the physical stock
                            if (StockAvailable::dependsOnStock($product['product_id'])) {
                                // synchronizes
                                StockAvailable::synchronize($product['product_id'], $order->id_shop);
                            }
                        }
                    }

                    $order->updateOrderDetailTax();

                    // sync all stock
                    (new StockManager())->updatePhysicalProductQuantity(
                        (int)$order->id_shop,
                        (int)Configuration::get('PS_OS_ERROR'),
                        (int)Configuration::get('PS_OS_CANCELED'),
                        null,
                        (int)$order->id
                    );
                } else {
                    $error = $this->trans('Order creation failed', array(), 'Admin.Payment.Notification');
                    PrestaShopLogger::addLog($error, 4, '0000002', 'Cart', intval($order->id_cart));

                    throw new PrestaShopException($error);
                    return;
                    //die($error);
                }
            } // End foreach $order_detail_list

            // Use the last order as currentOrder
            if (isset($order) && $order->id) {
                $this->currentOrder = (int)$order->id;
            }

            if (self::DEBUG_MODE) {
                PrestaShopLogger::addLog('PaymentModule::validateOrder - End of validateOrder',
                    1,
                    null,
                    'Cart',
                    (int)$id_cart,
                    true);
            }

            return true;
        } else {
            $error = $this->trans(
                'Cart cannot be loaded or an order has already been placed using this cart',
                array(),
                'Admin.Payment.Notification'
            );
            PrestaShopLogger::addLog($error, 4, '0000001', 'Cart', intval($this->context->cart->id));
            throw new PrestaShopException($error);
            return;
            //die($error);
        }
    }
}
