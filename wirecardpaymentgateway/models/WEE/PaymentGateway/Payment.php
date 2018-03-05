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

require_once dirname(__FILE__) . '/../../../vendor/autoload.php';
require_once dirname(__FILE__) . '/../../../libraries/Logger.php';
require_once dirname(__FILE__) . '/../../../libraries/ConfigurationSettings.php';
require_once dirname(__FILE__) . '/Cart.php';

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\TransactionService;

class WEEPaymentGatewayPayment
{
    /** @var  array */
    protected $config;
    /** @var  WirecardPaymentGateway */
    protected $module;
    protected $connection;
    protected $paymentMethod = null;
    protected $cart;
    protected $orderNumber;

    /**
     * initiate payment
     *
     * @since 0.0.3
     *
     * @param $module
     * @param $config
     *
     */
    public function __construct($module, $config)
    {
        $this->module = $module;
        $this->config = $config;
    }

    /**
     * check if payment is available
     *
     * @since 0.0.3
     *
     * @return boolean
     */
    public function isAvailable()
    {
        return (bool)ConfigurationSettings::getConfigValue($this->paymentMethod, TabData::INPUT_NAME_ENABLE_METHOD);
    }

    /**
     * get payment label
     *
     * @since 0.0.3
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->config[ConfigurationSettings::TEXT_METHOD_LABEL];
    }

    /**
     * get payment logo
     *
     * @since 0.0.3
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->config[ConfigurationSettings::TEXT_LOGO];
    }

    /**
     * get payment method name
     *
     * @since 0.0.3
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * check/set payment connection
     *
     * @since 0.0.3
     *
     * @return boolean
     */
    public function configuration()
    {
        $currency = new CurrencyCore($this->module->getContext()->cart->id_currency);
        $currencyIsoCode = $currency->iso_code;

        $baseUrl = ConfigurationSettings::getConfigValue($this->paymentMethod, 'wirecard_server_url');
        $httpUser = ConfigurationSettings::getConfigValue($this->paymentMethod, 'http_user');
        $httpPass = ConfigurationSettings::getConfigValue($this->paymentMethod, 'http_password');

        $this->connection = new Config($baseUrl, $httpUser, $httpPass, $currencyIsoCode);
        $logger = new Logger();
        $transactionService = new TransactionService($this->connection, $logger);
        if (!$transactionService->checkCredentials()) {
            return false;
        }
        $Config = $this->configMethod();
        $this->connection->add($Config);

        return true;
    }

    public function configMethod()
    {
        $MAID = ConfigurationSettings::getConfigValue($this->paymentMethod, 'maid');
        $key = ConfigurationSettings::getConfigValue($this->paymentMethod, 'secret') ;
        return new PaymentMethodConfig($this->getTransactionName(), $MAID, $key);
    }

    /**
     * get payment connection
     *
     * @since 0.0.3
     *
     * @return boolean
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * set payment connection certificate
     *
     * @since 0.0.3
     *
     */
    public function setCertificate($certificate)
    {
        $this->connection->setPublicKey(Tools::file_get_contents($certificate));
    }

    /**
     * check payment preconditions
     *
     * @since 0.0.3
     *
     * @return array
     */
    public function validations()
    {
        $cartData = $this->module->getContext()->cart;
        if (!$cartData->checkQuantities()) {
            return array('status'=> false,'message'=>$this->module->l('Products out of stock'));
        }

        return array('status'=> true,'message'=>'');
    }

    /**
     * initiate payment
     *
     * @since 0.0.3
     *
     * @param $cart
     * @param $orderNumber
     *
     */
    public function initiate($cart, $orderNumber)
    {
        $this->cart = $cart;
        $this->orderNumber = $orderNumber;
        $CartData = new WEEPaymentGatewayCart($this->module);

        $transaction =$this->getTransaction();
        $transaction->setRedirect($CartData->getRedirect($cart, $orderNumber));
        $transaction->setNotificationUrl($CartData->getNotification($cart, $orderNumber));
        $transaction->setAmount($CartData->getTotalAmount($cart));
        $transaction->setConsumerId($cart->id_customer);
        $transaction->setIpAddress($CartData->getConsumerIpAddress());
        $transaction->setAccountHolder($CartData->getConsumerData($cart));
        $transaction->setShipping($CartData->getShippingData($cart));
        $transaction->setDevice($CartData->getDevice($cart->id_customer));
        $transaction->setCustomFields($CartData->setCustomField(array('customOrderNumber'=>$orderNumber)));

        $logger = new Logger();
        $transactionService = new TransactionService($this->getConnection(), $logger);
        $response = $transactionService->pay($transaction);
        $this->processResponse($response);
    }

    /**
     * process payment response
     *
     * @since 0.0.3
     *
     * @param $response
     * @param $orderNumber
     *
     * @throw ExceptionEE
     */
    protected function processResponse($response)
    {
        if (!$this->processResponseSuccess($response)) {
            $this->processResponseFailed($response);
        }
    }

    /**
     * process payment response success
     *
     * @since 0.0.3
     *
     * @param $response
     *
     * @return boolean
     */
    protected function processResponseSuccess($response)
    {
        if (!($response instanceof InteractionResponse)) {
            return false;
        }
        die("<meta http-equiv='refresh' content='0;url={$response->getRedirectUrl()}'>");
    }

    /**
     * process payment response failed
     *
     * @since 0.0.3
     *
     * @param $response
     * @param $orderNumber
     *
     * @throw ExceptionEE
     */
    protected function processResponseFailed($response)
    {
        $errors = array();
        foreach ($response->getStatusCollection() as $status) {
            $severity = Tools::ucfirst($status->getSeverity());
            $code = $status->getCode();
            $description = $status->getDescription();
            $errors[] = $description;
            $logger = new Logger();
            $logger->warning(sprintf(
                $this->module->l('%s with code %s and message "%s" occurred'),
                $severity,
                $code,
                $description
            ));
        }
        $message = implode(',', $errors);
        if (Tools::strlen($message)) {
            throw new ExceptionEE($message);
        }
    }

    /**
     * get default paypal transaction name
     *
     * @since 0.0.3
     *
     * @return string
     */
    protected function getTransactionName()
    {
    }

    /**
     * get default paypal transaction data
     *
     * @since 0.0.3
     *
     * @return string
     */
    protected function getTransaction()
    {
    }

    /**
     * return response data
     *
     * @since 0.0.3
     *
     * @return array
     */
    public function getResponseData()
    {
        return $_POST;
    }

    public function getForm()
    {

        return $this->config[ConfigurationSettings::TEXT_IS_FORM];
    }
}
