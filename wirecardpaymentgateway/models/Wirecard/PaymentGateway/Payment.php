<?php

/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 2 (GPLv2) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 *
 * @author    WirecardCEE
 * @copyright WirecardCEE
 * @license   GPLv2
 */

require_once __DIR__.'/../../../vendor/autoload.php';
require_once __DIR__.'/../../../libraries/Logger.php';


use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Transaction\SofortTransaction;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Item;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Device;

class WirecardPaymentGatewayPayment
{
    /** @var  array */
    public $config;

    /** @var  WirecardCEECheckoutSeamless */
    public $module;

    protected $forceSendAdditionalData = false;

    protected $paymentMethod = null;

    /**
     * @var WirecardCheckoutSeamlessTransaction
     */
    protected $transaction;

    public function __construct($module, $config)
    {
        $this->module = $module;
        $this->config = $config;
    }

    /**
     * whether payment method is available on checkoutpage
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->isEnabled();
    }

    /**
     * whether paymenttype is enabled or not
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->module->getConfigValue($this->paymentMethod, 'enable_method');
    }

    /**
     * return the stored configvalue for the given field
     *
     * @param $field
     *
     * @return string
     */
    public function getConfigValue($field)
    {
        return (bool)Configuration::get($field);
    }

    /**
     * return the internal paymenttype name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->config['name'];
    }

    /**
     * return the label of the paymenttype
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->config['labelMethod'];
    }

    /**
     * whether paymenttype supports seamless integration
     *
     * @return bool
     */
    public function isSeamless()
    {
        return isset($this->config['seamless']) && $this->config['seamless'];
    }

    /**
     * return the logofilename
     *
     * @return mixed
     */
    public function getLogo()
    {
        return $this->config['logo'];
    }

    /**
     * return the template snippet
     *
     * @return mixed|null
     */
    public function getTemplate()
    {
        if (!isset($this->config['template'])) {
            return null;
        }
        return $this->config['template'];
    }

    /**
     * return a list of financialinstitutions, if any
     *
     * @return array
     */
    public function getFinancialInstitutions()
    {
        return array();
    }

    /**
     * @param $cart
     * @param $orderNumber
     *
     *
     * @throws Exception
     */
    public function initiate($cart, $orderNumber)
    {
        // ### printTransaction Service
        $logger = new Logger();
        $transaction =$this->getTransaction($cart,$orderNumber);
        $transactionService = new TransactionService($this->config, $logger);
        $response = $transactionService->pay($transaction);

        if ($response instanceof InteractionResponse) {
            die("<meta http-equiv='refresh' content='0;url={$response->getRedirectUrl()}'>");
        } elseif ($response instanceof FailureResponse) {
            $errors = array();
            foreach ($response->getStatusCollection() as $status) {
                $severity = ucfirst($status->getSeverity());
                $code = $status->getCode();
                $description = $status->getDescription();
                $errors[] = $description;
                $logger->warning(sprintf(
                    '%s with code %s and message "%s" occurred.<br>',
                    $severity,
                    $code,
                    $description
                ));
            }
            $message = implode(',', $errors);
            if (Tools::strlen($message)) {
                throw new Exception($message);
            }
        }
    }

    /**
     * @param Cart $cart
    $message
     * @return WirecardCEE_Stdlib_ConsumerData
     */
    protected function getConsumerData($cart)
    {
        /** @var Customer $customer */
        $customer = new Customer($cart->id_customer);

        $consumerData = new \WirecardCEE_Stdlib_ConsumerData();
        $consumerData->setIpAddress($this->getConsumerIpAddress());
        $consumerData->setUserAgent($this->getConsumerUserAgent());

        if (Tools::strlen($customer->birthday) && $customer->birthday !== "0000-00-00") {
            $dob = new DateTime($customer->birthday);
            $consumerData->setBirthDate($dob);
        }
        $consumerData->setEmail($customer->email);

        /** @var Address $billingAddress */
        $billingAddress = new Address($cart->id_address_invoice);
        /** @var Address $deliveryAddress */
        $deliveryAddress = new Address($cart->id_address_delivery);

        if (Tools::strlen($billingAddress->company)) {
            $consumerData->setCompanyName($billingAddress->company);
        }

        if (Tools::strlen($billingAddress->vat_number)) {
            $consumerData->setCompanyVatId($billingAddress->vat_number);
        }

        if ($this->forceSendAdditionalData || $this->module->getConfigValue('options', 'send_billingdata')) {
            $consumerData->addAddressInformation($this->getAddress($billingAddress, 'billing'));
        }

        if ($this->forceSendAdditionalData || $this->module->getConfigValue('options', 'send_shippingdata')) {
            $consumerData->addAddressInformation($this->getAddress($deliveryAddress, 'shipping'));
        }

        return $consumerData;
    }

    /**
     * @param Address $source
     * @param string $type
     *
     * @return WirecardCEE_Stdlib_ConsumerData_Address
     */
    protected function getAddress($source, $type = 'billing')
    {
        switch ($type) {
            case 'shipping':
                $address = new \WirecardCEE_Stdlib_ConsumerData_Address(
                    \WirecardCEE_Stdlib_ConsumerData_Address::TYPE_SHIPPING
                );
                break;

            default:
                $address = new \WirecardCEE_Stdlib_ConsumerData_Address(
                    \WirecardCEE_Stdlib_ConsumerData_Address::TYPE_BILLING
                );
                break;
        }

        $country = new Country($source->id_country);
        $state = new State($source->id_state);

        $address->setFirstname($source->firstname);
        $address->setLastname($source->lastname);
        $address->setAddress1($source->address1);
        $address->setAddress2($source->address2);
        $address->setZipCode($source->postcode);
        $address->setCity($source->city);
        $address->setCountry($country->iso_code);
        $address->setPhone($source->phone);

        if ($country->iso_code == 'US' || $country->iso_code == 'CA') {
            $address->setState($state->iso_code);
        } else {
            $address->setState($state->name);
        }

        return $address;
    }

    /**
     * build basket
     *
     * @param Cart $cart
     *
     * @return WirecardCEE_Stdlib_Basket
     */
    public function getBasket(Cart $cart)
    {
        $basket = new WirecardCEE_Stdlib_Basket();

        foreach ($cart->getProducts() as $product) {
            $item = new WirecardCEE_Stdlib_Basket_Item($product['reference']);
            $item->setUnitGrossAmount(number_format($product['price_wt'], 2, '.', ''))
                ->setUnitNetAmount(number_format($product['price'], 2, '.', ''))
                ->setUnitTaxAmount(number_format($product['price_wt'] - $product['price'], 2, '.', ''))
                ->setUnitTaxRate($product['rate'])
                ->setDescription(Tools::substr(strip_tags($product['description_short']), 0, 127))
                ->setName(Tools::substr($product['name'], 0, 127))
                ->setImageUrl(
                    $this->module->getContext()->link->getImageLink($product['link_rewrite'], $product['id_image'])
                );

            $basket->addItem($item, $product['cart_quantity']);
        }

        if ($cart->getTotalShippingCost(null, true) > 0) {
            $item = new WirecardCEE_Stdlib_Basket_Item('shipping');
            $item->setDescription('Shipping')
                ->setName('Shipping')
                ->setUnitGrossAmount($cart->getTotalShippingCost(null, true))
                ->setUnitNetAmount($cart->getTotalShippingCost(null, false))
                ->setUnitTaxAmount($item->getUnitGrossAmount() - $item->getUnitNetAmount())
                ->setUnitTaxRate((($item->getUnitGrossAmount() / $item->getUnitNetAmount()) - 1) * 100);

            $basket->addItem($item);
        }

        return $basket;
    }

    /**
     * set legacy basket parameters
     *
     * @param Cart $cart
     *
     * @param \WirecardCEEQMoreFrontendClient $client
     *
     * @return $this
     */
    public function setLegacyBasket(Cart $cart, \WirecardCEE_QMore_FrontendClient $client)
    {
        $productCounter=1;
        foreach ($cart->getProducts() as $product) {
            $name = 'basketItem'.$productCounter++;

            $client->__set($name.'UnitPrice', number_format($product['price'], 2, '.', ''));
            $client->__set($name.'Tax', number_format($product['price_wt'] - $product['price'], 2, '.', ''));
            $client->__set($name.'Quantity', $product['cart_quantity']);
            $client->__set($name.'ArticleNumber', $product['reference']);
        }

        if ($cart->getTotalShippingCost(null, true) > 0) {
            $name = 'basketItem'.$productCounter;
            $client->__set($name.'UnitPrice', $cart->getTotalShippingCost(null, false));
            $client->__set(
                $name.'Tax',
                $cart->getTotalShippingCost(null, true)-$cart->getTotalShippingCost(null, false)
            );
            $client->__set($name.'Quantity', 1);
            $client->__set($name.'ArticleNumber', 'shipping');
        }

        return $this;
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    protected function isAvailablePayolution($cart)
    {
        /** @var CustomerCore $customer */
        $customer = new Customer($cart->id_customer);

        /** @var AddressCore $billingAddress */
        $billingAddress = new Address($cart->id_address_invoice);

        /** @var AddressCore $shippingAddress */
        $shippingAddress = new Address($cart->id_address_delivery);

        $d1 = new DateTime($customer->birthday);
        $diff = $d1->diff(new DateTime);
        $customerAge = $diff->format('%y');

        if ($customerAge < $this->getMinAge()) {
            return false;
        }

        $total = $cart->getOrderTotal();

        if ($this->getBillingShippingAddressSame() && $billingAddress->id != $shippingAddress->id) {
            $fields = array(
                'country',
                'company',
                'firstname',
                'lastname',
                'address1',
                'address2',
                'postcode',
                'city'
            );
            foreach ($fields as $f) {
                if ($billingAddress->$f != $shippingAddress->$f) {
                    return false;
                }
            }
        }

        /** @var CurrencyCore $currency */
        $currency = new Currency($cart->id_currency);
        if (!in_array($currency->iso_code, $this->getAllowedCurrencies())) {
            return false;
        }

        if (count($this->getAllowedShippingCountries())) {
            $c = new Country($shippingAddress->id_country);
            if (!in_array($c->iso_code, $this->getAllowedShippingCountries())) {
                return false;
            }
        }

        if (count($this->getAllowedBillingCountries())) {
            $c = new Country($billingAddress->id_country);
            if (!in_array($c->iso_code, $this->getAllowedBillingCountries())) {
                return false;
            }
        }

        if ($this->getMaxBasketSize()) {
            if ($cart->nbProducts() > $this->getMaxBasketSize()) {
                return false;
            }
        }

        if ($this->getMinBasketSize()) {
            if ($cart->nbProducts() < $this->getMinBasketSize()) {
                return false;
            }
        }

        if ($this->getMinAmount() && $this->getMinAmount() > $total) {
            return false;
        }

        if ($this->getMaxAmount() && $this->getMaxAmount() < $total) {
            return false;
        }

        return true;
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    protected function isAvailableRatePay($cart)
    {
        if ($this->getMinAge() <= 0) {
            return false;
        }

        /** @var CustomerCore $customer */
        $customer = new Customer($cart->id_customer);

        /** @var AddressCore $billingAddress */
        $billingAddress = new Address($cart->id_address_invoice);

        /** @var AddressCore $shippingAddress */
        $shippingAddress = new Address($cart->id_address_delivery);

        $d1 = new DateTime($customer->birthday);
        $diff = $d1->diff(new DateTime);
        $customerAge = $diff->format('%y');

        if ($customerAge < $this->getMinAge()) {
            return false;
        }

        $total = $cart->getOrderTotal();

        if ($this->getBillingShippingAddressSame() && $billingAddress->id != $shippingAddress->id) {
            $fields = array(
                'country',
                'company',
                'firstname',
                'lastname',
                'address1',
                'address2',
                'postcode',
                'city'
            );
            foreach ($fields as $f) {
                if ($billingAddress->$f != $shippingAddress->$f) {
                    return false;
                }
            }
        }

        /** @var CurrencyCore $currency */
        $currency = new Currency($cart->id_currency);
        if (!in_array($currency->iso_code, $this->getAllowedCurrencies())) {
            return false;
        }

        if (count($this->getAllowedShippingCountries())) {
            $c = new Country($shippingAddress->id_country);
            if (!in_array($c->iso_code, $this->getAllowedShippingCountries())) {
                return false;
            }
        }

        if (count($this->getAllowedBillingCountries())) {
            $c = new Country($billingAddress->id_country);
            if (!in_array($c->iso_code, $this->getAllowedBillingCountries())) {
                return false;
            }
        }

        if ($this->getMaxBasketSize()) {
            if ($cart->nbProducts() > $this->getMaxBasketSize()) {
                return false;
            }
        }

        if ($this->getMinBasketSize()) {
            if ($cart->nbProducts() < $this->getMinBasketSize()) {
                return false;
            }
        }

        if ($this->getMinAmount() && $this->getMinAmount() > $total) {
            return false;
        }

        if ($this->getMaxAmount() && $this->getMaxAmount() < $total) {
            return false;
        }

        return true;
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    protected function isAvailableWirecard($cart)
    {
        return $this->isAvailableRatePay($cart);
    }

    /**
     * read consumer ip address
     *
     * @return string
     */
    protected function getConsumerIpAddress()
    {
        if (!method_exists('Tools', 'getRemoteAddr')) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) and $_SERVER['HTTP_X_FORWARDED_FOR']) {
                if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')) {
                    $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

                    return $ips[0];
                } else {
                    return $_SERVER['HTTP_X_FORWARDED_FOR'];
                }
            }

            return $_SERVER['REMOTE_ADDR'];
        } else {
            return Tools::getRemoteAddr();
        }
    }

    /**
     * get consumer user agent
     *
     * @return mixed
     */
    protected function getConsumerUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }


    /**
     * return wirecard payment method code
     *
     * @return null
     */
    public function getMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * whether sending of basket is forced
     *
     * @return bool
     */
    public function forceSendingBasket()
    {
        return false;
    }

    /**
     * enable automated debiting of payments.
     *
     * @return string
     */
    protected function getAutoDeposit()
    {
        return $this->getConfigValue('autodeposit');
    }

    /**
     * min amount limit for this payment method
     *
     * @return int
     */
    protected function getMinAmount()
    {
        return 0;
    }

    /**
     * max amount limit for this payment method
     *
     * @return int
     */
    protected function getMaxAmount()
    {
        return 0;
    }

    /**
     * min basket size limit for this payment method
     *
     * @return int
     */
    protected function getMinBasketSize()
    {
        return 0;
    }

    /**
     * max basket size limit for this payment method
     *
     * @return int
     */
    protected function getMaxBasketSize()
    {
        return 0;
    }

    /**
     * allowed currencies for this payment method
     *
     * @return array
     */
    protected function getAllowedCurrencies()
    {
        return array();
    }

    /**
     * allowed shipping countries for this payment method
     *
     * @return array
     */
    protected function getAllowedShippingCountries()
    {
        return array();
    }

    /**
     * allowed shipping countries for this payment method
     *
     * @return array
     */
    protected function getAllowedBillingCountries()
    {
        return array();
    }

    /**
     * allowed billing shipping countries for this payment method
     *
     * @return bool
     */
    protected function getBillingShippingAddressSame()
    {
        return true;
    }

    /**
     * min consumer age for this payment method
     *
     * @return int
     */
    public function getMinAge()
    {
        return 0;
    }

    /**
     * payolution consent text
     *
     * @return string
     */
    public function getConsentTxt()
    {
        $txt = $this->module->getPaymentTranslations()['consentTxt'];

        return utf8_decode(sprintf($txt, $this->getPayolutionLink()));
    }

    /**
     * payolution consent error message
     *
     * @return string
     */
    public function getConsentErrorMessage()
    {
        return $this->module->getPaymentTranslations()['consentErrorMessage'];
    }

    /**
     * min age error message
     *
     * @return string
     */
    public function getMinAgeMessage()
    {
        $txt = $this->module->getPaymentTranslations()['minAgeMessage'];

        return utf8_decode(sprintf($txt, $this->getMinAge()));
    }

    /**
     * return payolution mid
     *
     * @return null|string
     */
    public function getPayolutionMid()
    {
        return Configuration::get('WCS_OPTIONS_PAYOLUTION_MID');
    }

    /**
     * return link to payolution consent page
     *
     * @return string
     */
    public function getPayolutionLink()
    {
        $mid = Configuration::get('WCS_OPTIONS_PAYOLUTION_MID');

        if (!Tools::strlen($mid)) {
            return $this->module->getPaymentTranslations()['consent'];
        }

        //$Swift_Message_Encoder = new Swift_Message_Encoder()
        $mId = urlencode(base64_encode($mid));

        if (Tools::strlen($mId)) {
            return sprintf(
                '<a href="https://payment.payolution.com/payolution-payment/infoport/dataprivacyconsent?mId=%s" 
                target="_blank">%s</a>',
                $mId,
                $this->module->getPaymentTranslations()['consent']
            );
        } else {
            return $this->module->getPaymentTranslations()['consent'];
        }
    }

    /**
     * return payment method
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * sets the configuration for the payment method
     *
     * @since 0.0.2
     *
     */
    public function configuration()
    {
        $currency = new CurrencyCore($this->module->getContext()->cart->id_currency);
        $currencyIsoCode = $currency->iso_code;

        $baseUrl = $this->module->getConfigValue($this->paymentMethod, 'wirecard_server_url');
        $httpUser = $this->module->getConfigValue($this->paymentMethod, 'http_user');
        $httpPass = $this->module->getConfigValue($this->paymentMethod, 'http_password');
        $MAID = $this->module->getConfigValue($this->paymentMethod, 'maid');
        $key = $this->module->getConfigValue($this->paymentMethod, 'secret') ;

        $this->config = new Config($baseUrl, $httpUser, $httpPass, $currencyIsoCode);

        $logger = new Logger();
        $transactionService = new TransactionService($this->config, $logger);

        if (!$transactionService->checkCredentials()) {
            return false;
        }

        $Config = new PaymentMethodConfig($this->getTransactionName(), $MAID, $key);
        $this->config->add($Config);
        return true;
    }

    /**
     * checks if order is valid:
     * - check if products are available
     *
     * @since 0.0.2
     *
     */
    public function validations()
    {
        $cart = $this->module->getContext()->cart;
        if (!$cart->checkQuantities()) {
            return array('status'=> false,'message'=>$this->module->l('Products out of stock'));
        }
        return array('status'=> true,'message'=>'');
    }

    public function getTransaction($cart,$orderNumber)
    {
    }
    public function getTransactionName()
    {
    }
    public function setCertificate($certifcate)
    {
        $this->config->setPublicKey(file_get_contents($certifcate));
    }

}
