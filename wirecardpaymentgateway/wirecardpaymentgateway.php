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
 * @author Wirecard AG
 * @copyright Wirecard AG
 * @license GPLv3
 */

require_once dirname(__FILE__) . '/libraries/ExceptionEE.php';
require_once dirname(__FILE__) . '/libraries/ConfigurationSettings.php';
require_once dirname(__FILE__) . '/libraries/OrderMangement.php';

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

/**
 * Class WirecardEEPaymentGateway
 */
class WirecardPaymentGateway extends PaymentModule
{
    private $postErrors;
    private $config;

    /**
     * initiate module
     *
     * @since 0.0.3
     *
     */
    public function __construct()
    {
        $this->config = new ConfigurationSettings($this);
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

    /**
     * install module
     *
     * @since 0.0.3
     *
     * @return boolean
     */
    public function install()
    {
        if (!parent::install() || !ConfigurationSettings::setDefaults()
            || !$this->registerHook('paymentOptions')
            || !$this->registerHook('actionFrontControllerSetMedia')
            || !$this->registerHook('displayHeader')) {
            return false;
        }
        $this->getOrderMangement()->setStatus();

        return true;
    }

    /**
     * uninstall module
     *
     * @since 0.0.3
     *
     * @return boolean
     */
    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        return true;
    }

    /**
     * display module payment methods
     *
     * @since 0.0.3
     *
     * @param $params
     *
     * @return array
     */
    public function hookPaymentOptions($params)
    {
        $payment_options = array();
        if (!$this->active) {
            return $payment_options;
        }

        foreach ($this->getConfig()->getPaymentTypes() as $paymentType) {
            if ($paymentType->isAvailable()) {
                $payment = new PaymentOption();
                $payment->setLogo(Media::getMediaPath(
                    _PS_MODULE_DIR_ . $this->name . '/views/img/paymenttypes/'. $paymentType->getLogo()
                ))->setCallToActionText($paymentType->getLabel())
                ->setAction($this->context->link->getModuleLink(
                    $this->name,
                    'payment?paymentType='.$paymentType->getMethod(),
                    array(),
                    true
                ));
                $payment_options[] = $payment;
            }
        }

        return $payment_options;
    }

    /**
     * show module content
     *
     * @since 0.0.3
     *
     * @return string
     * @throws Exception
     */
    public function getContent()
    {
        $this->html = '<h2>' . $this->displayName . '</h2>';

        if (Tools::isSubmit(ConfigurationSettings::SUBMIT_BUTTON)) {
            $this->getConfig()->postValidation();
            if (!count($this->postErrors)) {
                $this->html .= $this->getConfig()->postProcess();
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
     * set frontend media
     *
     * @since 0.0.3
     *
     */
    public function hookActionFrontControllerSetMedia()
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
     * set frontend order header
     *
     * @since 0.0.3
     *
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
     * get module display name
     *
     * @since 0.0.3
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * get module name
     *
     * @since 0.0.3
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * initiate module payment
     *
     * @since 0.0.3
     *
     * @param $paymentTypeName
     *
     */
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
                throw new ExceptionEE($this->l('Unable to load basket'));
            }

            $paymentType = $this->getConfig()->getPaymentType($paymentTypeName);
            if ($paymentType === null) {
                throw new ExceptionEE($this->l('This payment method is not available'));
            } elseif (!$paymentType->isAvailable()) {
                throw new ExceptionEE($this->l('Payment method not enabled'));
            } elseif (!$paymentType->configuration()) {
                throw new ExceptionEE($this->l('The merchant configuration is incorrect'));
            }
            $validation = $paymentType->validations();
            if ($validation['status'] !== true) {
                throw new ExceptionEE($validation['message']);
            }

            $orderNumber = $this->getOrderMangement()->addOrder($this->getContext()->cart, $paymentType->getMethod());
            $paymentType->initiate($this->getContext()->cart, $orderNumber);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        $params = array();
        if ($message != '') {
            if (isset($orderNumber)) {
                $this->getOrderMangement()->updateOrder($orderNumber, _PS_OS_ERROR_);
            } else {
                $orderNumber = "";
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
     * get module context
     *
     * @since 0.0.3
     *
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * get module order management
     *
     * @since 0.0.3
     *
     * @return OrderMangement
     */
    public function getOrderMangement()
    {
        return new OrderMangement($this);
    }

    /**
     * get module configuration settings
     *
     * @since 0.0.3
     *
     * @return ConfigurationSettings
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * render module form
     *
     * @since 0.0.3
     * @param $fields_form_settings
     * @param $fields_value
     *
     * @return string
     */
    public function helperRender($fields_form_settings, $fields_value)
    {
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->show_toolbar = false;
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

    /**
     *  change module name by order payment method
     *
     * @since 0.0.3
     *
     * @param $orderNumber
     *
     * @return WirecardEEPaymentGateway
     */
    public function changeModuleNameByOrder($orderNumber)
    {
        $order = new Order($orderNumber);
        $paymentType = $this->getConfig()->getPaymentType($order->payment);
        $this->displayName = $paymentType->getLabel();

        return $this;
    }
}
