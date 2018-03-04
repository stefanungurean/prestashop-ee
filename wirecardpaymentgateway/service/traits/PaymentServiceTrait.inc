<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/25/18
 * Time: 07:25 AM
 */

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\TransactionService;

require _WPC_MODULE_DIR_ .'/vendor/autoload.php';
require _WPC_MODULE_DIR_ .'/libraries/Logger.php';
require _WPC_MODULE_DIR_ .'/service/impl/ResponseHandlerServiceImpl.php';
require _WPC_MODULE_DIR_ .'/models/TransactionParams.inc';


trait PaymentServiceTrait
{

    protected $config;
    protected $context;
    protected $module;
    protected $basket;

    public function initiatePayment($context, $module) {
        $this->context = $context;
        $this->module = $module;
        $orderNumber='';
        $message = '';
        if (!$this->module->active) {
            $message = $this->module->l('Module is not active');
        } elseif (!(Validate::isLoadedObject($this->context->cart) && $this->context->cart->OrderExists() == false)) {
            $message = $this->module->l('Cart cannot be loaded or an order has already been placed using this cart');
        } else {
            $cart = $this->context->cart;
            $validation = $this->validations();
            $this->commonConfiguration();
            if ($validation['status']!==true) {
                $message = $this->module->l($validation['message']);
            } else {
                try {
                    $this->module->validateOrder(
                        $cart->id,
                        Configuration::get('WDEE_OS_AWAITING'),
                        $cart->getOrderTotal(true),
                        $this->module->getDisplayName(),
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
                    if (Configuration::get($this->module->buildParamName($this->paymentMethod, 'descriptor'))) {
                        $descriptor = Configuration::get('PS_SHOP_NAME') . $orderNumber;
                    }
                    if (Configuration::get($this->module->buildParamName($this->paymentMethod, 'basket_send'))) {
                        $this->basket = new Basket();

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
                            $this->basket->add($productInfo);
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
                            $this->basket->add($shipping);
                        }
                    }
                    $amount = new Amount($cart->getOrderTotal(true), $currencyIsoCode);

                    $redirectUrls = new Redirect(
                        $this->context->link->getModuleLink($this->module->getName(), 'success', array(), true),
                        $this->context->link->getModuleLink($this->module->getName(), 'cancel', array("id_order" =>$orderNumber), true)
                    );

                    $notificationUrl = $this->context->link->getModuleLink(
                        $this->module->getName(),
                        'notify',
                        array(),
                        true
                    );

                    $cartId = new CustomField("cart_id", $this->context->cart->id);
                    $orderId = new CustomField("order_id", $orderNumber);
                    $customFields = new CustomFieldCollection();
                    $customFields->add($cartId);
                    $customFields->add($orderId);
                    // ## Transaction

                    $params = new TransactionParams();
                    $params->setNotificationUrl($notificationUrl);
                    $params->setRedirectUrls($redirectUrls);
                    $params->setBasket($this->basket);
                    $params->setOrderNumber($orderNumber);
                    $params->setOrderDetail($orderDetail);
                    $params->setDescriptor($descriptor);
                    $params->setAmount($amount);

                    $transaction = $this->prepareTransaction($params);

                    $transaction->setCustomFields($customFields);

                    // ### Transaction Service


                    // The service is used to execute the payment operation itself. A response object is returne
                    $logger = new Logger();
                    $transactionService = new TransactionService($this->config, $logger);

                    if (!$transactionService->checkCredentials()) {
                        if ($message!='') {
                            $this->context->cookie->eeMessage = $message;
                            $params = array(
                                'submitReorder' => true,
                                'id_order' => (int)$orderNumber
                            );
                        }
                        Tools::redirect($this->context->link->getPageLink('order', true, $cart->id_lang, $params));
                        die();
                    }
                    $response = $transactionService->pay($transaction);

                    // ## Response handling

                    // The response of the service must be handled depending on it's class
                    // In case of an `InteractionResponse`, a browser interaction by the consumer is required
                    // in order to continue the payment process. In this example we proceed with a header redirect
                    // to the given _redirectUrl_. IFrame integration using this URL is also possible.
                    $responseHandler = new ResponseHandlerServiceImpl();
                    if ($response instanceof FormInteractionResponse) {
                        die("<meta http-equiv='refresh' content='0;url={$response->getRedirectUrl()}'>");
                    } elseif ($response instanceof InteractionResponse) {
                        die("<meta http-equiv='refresh' content='0;url={$response->getRedirectUrl()}'>");
                        // The failure state is represented by a FailureResponse object.
                        // In this case the returned errors should be stored in your system.
                    } elseif ($response instanceof SuccessResponse) {

                        $responseHandler->successfulResponse($response, $this->context, $this->module);
                        die();
                    } elseif ($response instanceof FailureResponse) {
                        //alter order status to error and return to products quantities
                        $errors = $responseHandler->failResponse();


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
     * checks if order is valid:
     * - check if products are available
     *
     * @since 0.0.2
     *
     */
    protected function validations()
    {
        $cart = $this->context->cart;
        if (!$cart->checkQuantities()) {
            return array('status'=> false,'message'=>$this->module->l('Products out of stock'));
        }
        return array('status'=> true,'message'=>'');
    }


    protected function commonConfiguration() {

        $this->config = $this->getConfiguration();
    }

}