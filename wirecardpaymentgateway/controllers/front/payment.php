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
require __DIR__.'/../../vendor/autoload.php';
require __DIR__.'/../../libraries/Logger.php';

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Item;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Device;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\TransactionService;

class WirecardPaymentGatewayPaymentModuleFrontController extends ModuleFrontController
{
    private $config;
    private $method="paypal";
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $orderNumber='';
        if (!$this->module->active) {
            $message = $this->l('Module is not active');
        } elseif (!(Validate::isLoadedObject($this->context->cart) && !$this->context->cart->OrderExists())) {
            $message = $this->l('Cart cannot be loaded or an order has already been placed using this cart');
        } elseif (!Configuration::get($this->module->buildParamName($this->method, 'enable_method'))) {
            $message = $this->l('Payment method not available');
        } else {
            $cart = $this->context->cart;
            $validation = $this->validations();
            if ($validation['status']!==true) {
                $message = $this->l($validation['message']);
            } elseif (!$this->configuration()) {
                $message = $this->l('The merchant configuration is incorrect');
            } else {
                try {
                    $this->module->validateOrder(
                        $cart->id,
                        Configuration::get('WDEE_OS_AWAITING'),
                        $cart->getOrderTotal(true),
                        $this->method,
                        null,
                        array(),
                        null,
                        false,
                        $cart->secure_key
                    );

                    $currency = new CurrencyCore($cart->id_currency);
                    $currencyIsoCode = $currency->iso_code;

                    $orderNumber = $this->module->currentOrder;
                    $orderDetail = $this->module->getDisplayName();
                    $descriptor = '';
                    if (Configuration::get($this->module->buildParamName($this->method, 'descriptor'))) {
                        $descriptor = Configuration::get('PS_SHOP_NAME') . $orderNumber;
                    }

                    if (Configuration::get($this->module->buildParamName($this->method, 'basket_send'))) {
                        $basket = new Basket();

                        foreach ($cart->getProducts() as $product) {
                            $productInfo = new Item(
                                $product['name'],
                                new Amount(
                                    number_format(
                                        $product['price_wt'],
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
                            $tax = ($product['price_wt'] - $product['price']) * 100 / $product['price_wt'];
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
                            $shipping->setDescription($this->l('Shipping'));
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
                    }
                    $amount = new Amount($cart->getOrderTotal(true), $currencyIsoCode);
                    $params = array(
                        'id_cart' => (int)$cart->id,
                        'id_module' => (int)$this->module->id,
                        'key' => $cart->secure_key,
                        'order' => $orderNumber
                    );
                    $redirectUrls = new Redirect(
                        $this->context->link->getModuleLink($this->module->getName(), 'success', $params, true),
                        $this->context->link->getModuleLink($this->module->getName(), 'cancel', $params, true)
                    );
                    $notificationUrl = $this->context->link->getModuleLink(
                        $this->module->getName(),
                        'notify',
                        $params,
                        true
                    );

                    $customer = new Customer($cart->id_customer);
                    $addressDelivery = new Address(intval($cart->id_address_delivery));
                    $carrier = new Carrier($cart->id_carrier);
                    $countryDelivery = Country::getIsoById($addressDelivery->id_country);

                    $customerData = new AccountHolder();
                    $customerData->setFirstName($customer->firstname);
                    $customerData->setLastName($customer->lastname);
                    $customerData->setEmail($customer->email);
                    $customerData->setGender($customer->id_gender);
                    if ($customer->birthday!="0000-00-00") {
                        $birthday = new DateTime($customer->birthday);
                        $customerData->setDateOfBirth($birthday);
                    }

                    $cityDelivery = $addressDelivery->city;
                    $streetDelivery = $addressDelivery->address1;
                    $postcodeDelivery = $addressDelivery->postcode;
                    $addressDeliverySdk = new \Wirecard\PaymentSdk\Entity\Address(
                        $countryDelivery,
                        $cityDelivery,
                        $streetDelivery
                    );
                    $addressDeliverySdk->setPostalCode($postcodeDelivery);

                    $shippingData = new AccountHolder();
                    $shippingData->setFirstName($addressDelivery->firstname);
                    $shippingData->setLastName($addressDelivery->lastname);
                    $shippingData->setAddress($addressDeliverySdk);
                    $shippingData->setShippingMethod($carrier->getShippingMethod());

                    $Device=new Device();
                    $Device->setFingerprint(md5($cart->id_customer . "_" . microtime()));

                    // ## Transaction

                    $transaction = new PayPalTransaction();
                    $transaction->setNotificationUrl($notificationUrl);
                    $transaction->setRedirect($redirectUrls);
                    $transaction->setAmount($amount);
                    if (Configuration::get($this->module->buildParamName($this->method, 'basket_send'))) {
                        $transaction->setBasket($basket);
                    }

                    //transaction identification
                    $customOrderNumber = new CustomField('customOrderNumber', $orderNumber);
                    $customFields = new CustomFieldCollection();
                    $customFields->add($customOrderNumber);
                    $transaction->setCustomFields($customFields);

                    $transaction->setOrderNumber($orderNumber);
                    $transaction->setOrderDetail($orderDetail);
                    $transaction->setDescriptor($descriptor);
                    $transaction->setEntryMode('ecommerce');

                    //fraud detection
                    $transaction->setIpAddress(Tools::getRemoteAddr());
                    $transaction->setAccountHolder($customerData);
                    $transaction->setShipping($shippingData);
                    $transaction->setConsumerId($cart->id_customer);
                    $transaction->setDevice($Device);

                    // ### Transaction Service
                    $logger = new Logger();
                    $transactionService = new TransactionService($this->config, $logger);
                    $response = $transactionService->pay($transaction);

                    // ## Response handling

                     if ($response instanceof InteractionResponse) {
                        die("<meta http-equiv='refresh' content='0;url={$response->getRedirectUrl()}'>");
                     } elseif ($response instanceof FailureResponse) {
                        $history = new OrderHistory();
                        $history->id_order = (int)$orderNumber;
                        $history->changeIdOrderState((_PS_OS_ERROR_), $history->id_order, true);

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
                        $messageTemp = implode(',', $errors);
                        if (Tools::strlen($messageTemp)) {
                            $message = $messageTemp;
                        }
                    }
                } catch (Exception $e) {
                    $message=$e->getMessage();
                }
            }
        }
        $params=array();
        if ($message!='') {
            $this->context->cookie->eeMessage = $message;
            $params = array(
                'submitReorder' => true,
                'id_order' => (int)$orderNumber
            );
        }
        Tools::redirect($this->context->link->getPageLink('order', true, $cart->id_lang, $params));
    }

    /**
     * sets the configuration for the payment method
     *
     * @since 0.0.2
     *
     */
    private function configuration()
    {
        $currency = new CurrencyCore($this->context->cart->id_currency);
        $currencyIsoCode = $currency->iso_code;
        $baseUrl = Configuration::get($this->module->buildParamName($this->method, 'wirecard_server_url'));
        $httpUser = Configuration::get($this->module->buildParamName($this->method, 'http_user'));
        $httpPass = Configuration::get($this->module->buildParamName($this->method, 'http_password'));
        $payPalMAID = Configuration::get($this->module->buildParamName($this->method, 'maid'));
        $payPalKey = Configuration::get($this->module->buildParamName($this->method, 'secret')) ;

        $this->config = new Config($baseUrl, $httpUser, $httpPass, $currencyIsoCode);

        $logger = new Logger();
        $transactionService = new TransactionService($this->config, $logger);

        if (!$transactionService->checkCredentials()) {
            return false;
        }

        $payPalConfig = new PaymentMethodConfig(PayPalTransaction::NAME, $payPalMAID, $payPalKey);
        $this->config->add($payPalConfig);
        return true;
    }

    /**
     * checks if order is valid:
     * - check if products are available
     *
     * @since 0.0.2
     *
     */
    private function validations()
    {
        $cart = $this->context->cart;
        if (!$cart->checkQuantities()) {
            return array('status'=> false,'message'=>$this->l('Products out of stock'));
        }
        return array('status'=> true,'message'=>'');
    }
}
