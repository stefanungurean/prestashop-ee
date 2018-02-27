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
require_once __DIR__.'/libraries/ExceptionEE.php';
require_once __DIR__.'/libraries/ConfigurationSettings.php';
require_once __DIR__.'/libraries/OrderMangement.php';

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

/**
 * Class WirecardEEPaymentGateway
 */
class WirecardPaymentGateway extends PaymentModule
{
    private $postErrors;
    const WIRECARD_SERVER_URL='wirecard_server_url';
    const HTTP_PASS='http_password';
    const HTTP_USER='http_user';
    private $config;
    public function __construct()
    {
        $this->config = new ConfigurationSettings($this);
        ConfigurationSettings::$config= $this->config();
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
        if (!parent::install() || !ConfigurationSettings::setDefaults()
            || !$this->registerHook('displayPaymentEU')
            || !$this->registerHook('actionFrontControllerSetMedia')
            || !$this->registerHook('displayHeader')) {
            return false;
        }
        $this->getConfig()->setStatus();

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

        foreach ($this->getConfig()->getPaymentTypes() as $paymentType) {
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

    protected function paypal()
    {
        $methodName=__FUNCTION__;
        $MethodName=ucfirst($methodName);
        return array(
            'tab' => $this->l($MethodName),
            'fields' => array(
                array(
                    'name' => 'enable_method',
                    'label' => $this->l('Enable'),
                    'default' => '0',
                    'type' => ConfigurationSettings::INPUT_ON_OFF,
                    ConfigurationSettings::CLASS_NAME => $MethodName,
                    'logo' => 'paypal.png',
                    'labelMethod' => $this->l($MethodName),

                ),
                array(
                    'name' => self::WIRECARD_SERVER_URL,
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
                    'name' => self::HTTP_USER,
                    'label' => $this->l('HTTP user'),
                    'type' => 'text',
                    'default' => '70000-APITEST-AP',
                    'required' => true,
                    'sanitize' => 'trim'
                ),
                array(
                    'name' => self::HTTP_PASS,
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
                    'type' => ConfigurationSettings::INPUT_ON_OFF,
                    'required' => true
                ),
                array(
                    'name' => 'basket_send',
                    'label' => $this->l('Send basket data'),
                    'default' => '0',
                    'type' => ConfigurationSettings::INPUT_ON_OFF,
                    'required' => true
                ),
                array(
                    'type' => ConfigurationSettings::LINK_BUTTON,
                    'required' => false,
                    'buttonText' => $this->l('Test paypal configuration'),
                    'id' => 'paypalConfig',
                    ConfigurationSettings::METHOD_NAME  => $methodName,
                    'name' => $methodName,
                    'send' => $this->getCheckArray($methodName)
                )
            )
        );
    }

    protected function sofort()
    {
        $methodName=__FUNCTION__;
        $MethodName=ucfirst($methodName);
        return array(
            'tab' => $this->l($MethodName),
            'fields' => array(
                array(
                    'name' => 'enable_method',
                    'label' => $this->l('Enable'),
                    'default' => '0',
                    'type' => ConfigurationSettings::INPUT_ON_OFF,
                    ConfigurationSettings::CLASS_NAME => $MethodName,
                    'logo' => 'sofortbanking.png',
                    'labelMethod' => $this->l($MethodName),

                ),
                array(
                    'name' => self::WIRECARD_SERVER_URL,
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
                    'name' => self::HTTP_USER,
                    'label' => $this->l('HTTP user'),
                    'type' => 'text',
                    'default' => '70000-APITEST-AP',
                    'required' => true,
                    'sanitize' => 'trim'
                ),
                array(
                    'name' => self::HTTP_PASS,
                    'label' => $this->l('HTTP Password'),
                    'type' => 'text',
                    'default' => 'qD2wzQ_hrc!8',
                    'required' => true,
                    'sanitize' => 'trim'
                ),
                array(
                    'type' => ConfigurationSettings::LINK_BUTTON,
                    'required' => false,
                    'buttonText' => $this->l('Test sofort configuration'),
                    'id' => 'sofortConfig',
                    ConfigurationSettings::METHOD_NAME => $methodName,
                    'name' => $methodName,
                    'send' => $this->getCheckArray($methodName)
                )
            )
        );
    }

    public function getCheckArray($methodName)
    {
        return array(
            ConfigurationSettings::buildParamName($methodName, self::WIRECARD_SERVER_URL),
            ConfigurationSettings::buildParamName($methodName, self::HTTP_USER),
            ConfigurationSettings::buildParamName($methodName, self::HTTP_PASS)
        );
    }

    protected function config()
    {
        $configurationArray=array();
        $configurationArray['paypal']=$this->paypal();
        $configurationArray['sofort']=$this->sofort();
        return $configurationArray;
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
            $this->getConfig()->postValidation();
            if (!count($this->postErrors)) {
                $this->html.=$this->getConfig()->postProcess();
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
        $this->html .= $this->getConfig()->renderForm();

        return $this->html;
    }

    /**
     * return options for transaction types select
     *
     * @since 0.0.2
     *
     * @return array
     */
    public function getTransactionTypes()
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

    public function initiatePayment($paymentTypeName = '')
    {
        try {
            if (!$this->active) {
                throw new ExceptionEE($this->l('Module is not active'));
            } elseif (!(Validate::isLoadedObject($this->getContext()->cart) &&
                !$this->getContext()->cart->OrderExists())) {
                throw new ExceptionEE($this->l(
                    'Cart cannot be loaded or an order has already been placed using this cart'
                ));
            } elseif (!$this->context->cookie->id_cart) {
                throw new ExceptionEE($this->l('Unable to load basket.'));
            }
            $paymentType = $this->getConfig()->getPaymentType($paymentTypeName);
            if ($paymentType === null) {
                throw new ExceptionEE($this->l('This payment method is not available.'));
            } elseif (!$paymentType->isAvailable()) {
                throw new ExceptionEE($this->l('Payment method not enabled.'));
            } elseif (!$paymentType->configuration()) {
                throw new ExceptionEE($this->l('The merchant configuration is incorrect'));
            }
            $validation = $paymentType->validations();
            if ($validation['status']!==true) {
                throw new ExceptionEE($this->l($validation['message']));
            }

            $orderNumber = $this->getOrderMangement()->addOrder($this->getContext()->cart, $paymentType->getMethod());
            $paymentType->initiate($this->getContext()->cart, $orderNumber);
        } catch (Exception $e) {
            $message=$e->getMessage();
        }

        $params=array();
        if ($message!='') {
            if (isset($orderNumber)) {
                $this->getOrderMangement()->updateOrder($orderNumber, _PS_OS_ERROR_);
            } else {
                $orderNumber="";
            }

            $this->getContext()->cookie->eeMessage = $message;
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

    /**
     * get context
     *
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    public function getOrderMangement()
    {
        return new OrderMangement($this);
    }
    public function getConfig()
    {
        return $this->config;
    }
    
    function HelperRender($fields_form_settings, $fields_value)
    {
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
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm(array($fields_form_settings));
    }
}
