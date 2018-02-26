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

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Item;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Entity\Device;
use Wirecard\PaymentSdk\Entity\Amount;

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
        $transaction =$this->getTransaction($cart, $orderNumber);
        $transaction->setRedirect($this->getRedirect($cart, $orderNumber));
        $transaction->setNotificationUrl($this->getNotification($cart, $orderNumber));
        $transaction->setAmount($this->getTotalAmount($cart));
        $transaction->setDescriptor($this->getDescriptor($orderNumber));

        $transaction->setConsumerId($cart->id_customer);
        $transaction->setIpAddress($this->getConsumerIpAddress());
        $transaction->setAccountHolder($this->getConsumerData($cart));
        $transaction->setShipping($this->getShippingData($cart));
        $transaction->setDevice($this->getDevice($cart->id_customer));
        $transaction->setCustomFields($this->setCustomField(array('customOrderNumber'=>$orderNumber)));

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
    
    public function getUrlParameters($cart, $orderNumber)
    {
        return array(
            'id_cart' => (int)$cart->id,
            'id_module' => (int)$this->module->id,
            'key' => $cart->secure_key,
            'order' => $orderNumber
        );
    }

    public function getRedirect($cart, $orderNumber)
    {
        $params=$this->getUrlParameters($cart, $orderNumber);
        return new Redirect(
            $this->module->getContext()->link->getModuleLink($this->module->getName(), 'success', $params, true),
            $this->module->getContext()->link->getModuleLink($this->module->getName(), 'cancel', $params, true)
        );
    }

    public function getNotification($cart, $orderNumber)
    {
        $params=$this->getUrlParameters($cart, $orderNumber);
        return $this->module->getContext()->link->getModuleLink(
            $this->module->getName(),
            'notify',
            $params,
            true
        );
    }

    protected function getConsumerData($cart)
    {
        $customer = new Customer($cart->id_customer);

        $customerData = new AccountHolder();
        $customerData->setFirstName($customer->firstname);
        $customerData->setLastName($customer->lastname);
        $customerData->setEmail($customer->email);
        $customerData->setGender($customer->id_gender);
        if (Tools::strlen($customer->birthday) && $customer->birthday !== "0000-00-00") {
            $birthday = new DateTime($customer->birthday);
            $customerData->setDateOfBirth($birthday);
        }

        return $customerData;
    }

    protected function getShippingData($cart)
    {
        $carrier = new Carrier($cart->id_carrier);
        $addressDelivery = new Address(intval($cart->id_address_delivery));

        $shippingData = new AccountHolder();
        $shippingData->setFirstName($addressDelivery->firstname);
        $shippingData->setLastName($addressDelivery->lastname);
        $shippingData->setAddress($this->getAddress($addressDelivery));
        $shippingData->setShippingMethod($carrier->getShippingMethod());

        return $shippingData;
    }

    public function getDevice($id_customer)
    {
        $Device=new Device();
        $Device->setFingerprint(md5($id_customer . "_" . microtime()));
        return $Device;
    }

    public function getBasket($cart)
    {
        $currency = new CurrencyCore($cart->id_currency);
        $currencyIsoCode = $currency->iso_code;
        $basket = new Basket();

        foreach ($cart->getProducts() as $product) {
            $price_wt=$product['price_wt'];
            $price=$product['price'];
            $tax = ($price_wt - $price) * 100 / $price;

            $productInfo = new Item(
                $product['name'],
                new Amount(
                    number_format(
                        $price_wt,
                        2,
                        '.',
                        ''
                    ),
                    $currencyIsoCode
                ),
                $product['cart_quantity']
            );
            $productInfo->setDescription(
                Tools::substr(
                    strip_tags($product['description_short']),
                    0,
                    127
                )
            );
            $productInfo->setTaxRate(
                number_format(
                    $tax,
                    2,
                    '.',
                    ''
                )
            );
            $basket->add($productInfo);
        }

        if ($cart->getTotalShippingCost() != 0) {
            $shipping = new Item(
                'Shipping',
                new Amount(
                    number_format(
                        $cart->getTotalShippingCost(),
                        2,
                        '.',
                        ''
                    ),
                    $currencyIsoCode
                ),
                '1'
            );

            $shipping->setDescription($this->module->l('Shipping'));
            $shipping->setTaxRate(
                number_format(
                    '0',
                    2,
                    '.',
                    ''
                )
            );
            $basket->add($shipping);
        }
        return $basket;
    }

    protected function getConsumerIpAddress()
    {
        return Tools::getRemoteAddr();
    }

    public function getTotalAmount($cart)
    {
        $currency = new CurrencyCore($cart->id_currency);
        $currencyIsoCode = $currency->iso_code;
        return new Amount($cart->getOrderTotal(true), $currencyIsoCode);
    }

    public function setCustomField($CustomFieldArray)
    {
        $customFields = new CustomFieldCollection();
        if (!empty($CustomFieldArray)) {
            foreach ($CustomFieldArray as $key => $field) {
                $customOrderNumber = new CustomField($key, $field);
                $customFields->add($customOrderNumber);
            }
        }
        return $customFields;
    }

    protected function getAddress($source)
    {
        $country = new Country($source->id_country);

        $address = new \Wirecard\PaymentSdk\Entity\Address(
            $country->iso_code,
            $source->city,
            $source->address1
        );
        $address->setPostalCode($source->postcode);
        $address->setStreet2($source->address2);

        return $address;
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
