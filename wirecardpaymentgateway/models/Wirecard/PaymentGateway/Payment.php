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

require_once __DIR__.'/../../../vendor/autoload.php';
require_once __DIR__.'/../../../libraries/Logger.php';
require_once __DIR__.'/Cart.php';

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\TransactionService;

class WirecardPaymentGatewayPayment
{
    /** @var  array */
    private $config;
    /** @var  WirecardPaymentGateway */
    public $module;
    public $connection;
    protected $paymentMethod = null;

    public function __construct($module, $config)
    {
        $this->module = $module;
        $this->config = $config;
    }

    public function isAvailable()
    {
        return (bool)$this->module->getConfigValue($this->paymentMethod, 'enable_method');
    }

    public function getName()
    {
        return $this->config['name'];
    }

    public function getLabel()
    {
        return $this->config['labelMethod'];
    }

    public function getLogo()
    {
        return $this->config['logo'];
    }

    public function getMethod()
    {
        return $this->paymentMethod;
    }

    public function configuration()
    {
        $currency = new CurrencyCore($this->module->getContext()->cart->id_currency);
        $currencyIsoCode = $currency->iso_code;

        $baseUrl = $this->module->getConfigValue($this->paymentMethod, 'wirecard_server_url');
        $httpUser = $this->module->getConfigValue($this->paymentMethod, 'http_user');
        $httpPass = $this->module->getConfigValue($this->paymentMethod, 'http_password');
        $MAID = $this->module->getConfigValue($this->paymentMethod, 'maid');
        $key = $this->module->getConfigValue($this->paymentMethod, 'secret') ;

        $this->connection = new Config($baseUrl, $httpUser, $httpPass, $currencyIsoCode);

        $logger = new Logger();
        $transactionService = new TransactionService($this->connection, $logger);

        if (!$transactionService->checkCredentials()) {
            return false;
        }

        $Config = new PaymentMethodConfig($this->getTransactionName(), $MAID, $key);
        $this->connection->add($Config);
        return true;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function setCertificate($certifcate)
    {
        $this->connection->setPublicKey(file_get_contents($certifcate));
    }

    public function validations()
    {
        $cart = $this->module->getContext()->cart;
        if (!$cart->checkQuantities()) {
            return array('status'=> false,'message'=>$this->module->l('Products out of stock'));
        }
        return array('status'=> true,'message'=>'');
    }

    public function initiate($cart, $orderNumber)
    {
        $CartData=new WirecardPaymentGatewayCart($this->module);
        $transaction =$this->getTransaction($cart, $orderNumber);
        $transaction->setRedirect($CartData->getRedirect($cart, $orderNumber));
        $transaction->setNotificationUrl($CartData->getNotification($cart, $orderNumber));
        $transaction->setAmount($CartData->getTotalAmount($cart));
        $transaction->setDescriptor($this->getDescriptor($orderNumber));

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


    public function processResponse($response)
    {
        if ($response instanceof InteractionResponse) {
            die("<meta http-equiv='refresh' content='0;url={$response->getRedirectUrl()}'>");
        } elseif ($response instanceof FailureResponse) {
            $errors = array();
            foreach ($response->getStatusCollection() as $status) {
                $severity = ucfirst($status->getSeverity());
                $code = $status->getCode();
                $description = $status->getDescription();
                $errors[] = $description;
                $logger = new Logger();
                $logger->warning(sprintf(
                    '%s with code %s and message "%s" occurred.<br>',
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
    }

    public function getTransaction($cart, $orderNumber)
    {
    }

    public function getTransactionName()
    {
    }

    public function getDescriptor($orderNumber)
    {
    }
}
