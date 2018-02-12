<?php
require __DIR__.'/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Item;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\TransactionService;

class WirecardPaymentGatewayPaymentModuleFrontController extends ModuleFrontController
{

    private $config;
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        if (Configuration::get($this->module->buildParamName('paypal', 'enable_method'))) {
            $this->configuration();

            $cart = $this->context->cart;
            $currency = new CurrencyCore($cart->id_currency);
            $currencyIsoCode = $currency->iso_code;
            $basket = new Basket();

            $orderNumber = intval($cart->id) + rand();
            $orderDetail = $this->module->getDisplayName();

            $descriptor = "";
            if (Configuration::get($this->module->buildParamName('paypal', 'descriptior'))) {
                $descriptor = Configuration::get('PS_SHOP_NAME') . $cart->id;
            }
            foreach ($cart->getProducts() as $product) {
                $productInfo = new Item(
                    $product['name'],
                    new Amount(
                        number_format(
                            $product['price'],
                            2,
                            '.',
                            ''
                        ),
                        $currencyIsoCode
                    ),
                    $product['cart_quantity']
                );
                $productInfo->setDescription(Tools::substr(strip_tags($product['description_short']), 0, 127));
                $productInfo->setTaxRate(
                    number_format(
                        $product['price_wt'] - $product['price'],
                        2,
                        '.',
                        ''
                    )
                );
                $basket->add($productInfo);
            }

            $shipping = new Item(
                "Shipping",
                new Amount(
                    number_format(
                        $cart->getTotalShippingCost(),
                        2,
                        '.',
                        ''
                    ),
                    $currencyIsoCode
                ),
                "1"
            );
            $shipping->setDescription($this->l('Shipping'));
            $shipping->setTaxRate(
                number_format(
                    "0",
                    2,
                    '.',
                    ''
                )
            );
            $basket->add($shipping);

            $amount = new Amount($cart->getOrderTotal(true), $currencyIsoCode);

            $redirectUrls = new Redirect(
                $this->context->link->getModuleLink($this->name, 'paypal/success', array(), true),
                $this->context->link->getModuleLink($this->name, 'paypal/cancel', array(), true)
            );


            $notificationUrl =$this->context->link->getModuleLink($this->name, 'paypal/notify', array(), true);

            // ## Transaction

            // The PayPal transaction holds all transaction relevant data for the payment process.
            $transaction = new PayPalTransaction();
            $transaction->setNotificationUrl($notificationUrl);
            $transaction->setRedirect($redirectUrls);
            $transaction->setAmount($amount);
            $transaction->setBasket($basket);
            $transaction->setOrderNumber($orderNumber);
            $transaction->setOrderDetail($orderDetail);
            $transaction->setDescriptor($descriptor);
            $transaction->setEntryMode('ecommerce');

            // ### Transaction Service

            // The service is used to execute the payment operation itself. A response object is returne
            $transactionService = new TransactionService($this->config);
            $response = $transactionService->pay($transaction);

            // ## Response handling

            // The response of the service must be handled depending on it's class
            // In case of an `InteractionResponse`, a browser interaction by the consumer is required
            // in order to continue the payment process. In this example we proceed with a header redirect
            // to the given _redirectUrl_. IFrame integration using this URL is also possible.
            if ($response instanceof InteractionResponse) {
                die("<meta http-equiv='refresh' content='0;url={$response->getRedirectUrl()}'>");

            // The failure state is represented by a FailureResponse object.
            // In this case the returned errors should be stored in your system.
            } elseif ($response instanceof FailureResponse) {
                // In our example we iterate over all errors and echo them out. You should display them as
                // error, warning or information based on the given severity.
                $errors = array();
                foreach ($response->getStatusCollection() as $status) {
                    /**
                     * @var $status \Wirecard\PaymentSdk\Entity\Status
                     */
                    $severity = ucfirst($status->getSeverity());
                    $code = $status->getCode();
                    $description = $status->getDescription();
                    $errors[] = sprintf(
                        '%s with code %s and message "%s" occurred.<br>',
                        $severity,
                        $code,
                        $description
                    );
                }

                $messageTemp = implode(',', $errors);
                if (Tools::strlen($messageTemp)) {
                    $message = $messageTemp;
                }
            }
        }
        else {
            $message="Payment method not avaible";
        }
        $this->context->cookie->eeMessage = $message;
        Tools::redirect($this->context->link->getPageLink('order', true, $cart->id_lang));
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
        $baseUrl = Configuration::get($this->module->buildParamName('paypal', 'wirecard_server_url'));
        $httpUser = Configuration::get($this->module->buildParamName('paypal', 'http_user'));
        $httpPass = Configuration::get($this->module->buildParamName('paypal', 'http_password'));
        $paypalMAID = Configuration::get($this->module->buildParamName('paypal', 'maid'));
        $paypalKey = Configuration::get($this->module->buildParamName('paypal', 'secret')) ;

        $this->config = new Config($baseUrl, $httpUser, $httpPass, $currencyIsoCode);
        $paypalConfig = new PaymentMethodConfig(PayPalTransaction::NAME, $paypalMAID, $paypalKey);
        $this->config->add($paypalConfig);
    }
}
