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
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Transaction\SofortTransaction;
use Wirecard\PaymentSdk\TransactionService;

class WirecardPaymentGatewaySofortModuleFrontController extends ModuleFrontController
{
    private $config;
    private $method="sofort";
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
                    $descriptor = Configuration::get('PS_SHOP_NAME') . $orderNumber;

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


                    // ## Transaction


                    // The PayPal transaction holds all transaction relevant data for the payment process.
                    $transaction = new SofortTransaction();
                    $transaction->setRedirect($redirectUrls);
                    $transaction->setAmount($amount);
                    $transaction->setDescriptor($descriptor);

                    $customOrderNumber = new CustomField('customOrderNumber', $orderNumber);
                    $customFields = new CustomFieldCollection();
                    $customFields->add($customOrderNumber);
                    $transaction->setCustomFields($customFields);

                    $logger = new Logger('');
                    $transactionService = new TransactionService($this->config, $logger);
                    $response = $transactionService->pay($transaction);

                    // ## Response handling

                    if ($response instanceof InteractionResponse) {
                        die("<meta http-equiv='refresh' content='0;url={$response->getRedirectUrl()}'>");
                        // The failure state is represented by a FailureResponse object.
                        // In this case the returned errors should be stored in your system.
                    } elseif ($response instanceof FailureResponse) {
                        //alter order status to error and return to products quantities
                        $history = new OrderHistory();
                        $history->id_order = (int)$orderNumber;
                        $history->changeIdOrderState((_PS_OS_ERROR_), $history->id_order, true);

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
        $MAID = Configuration::get($this->module->buildParamName($this->method, 'maid'));
        $Key = Configuration::get($this->module->buildParamName($this->method, 'secret')) ;

        $this->config = new Config($baseUrl, $httpUser, $httpPass, $currencyIsoCode);

        $logger = new Logger();
        $transactionService = new TransactionService($this->config, $logger);

        if (!$transactionService->checkCredentials()) {
            return false;
        }

        $SofortConfig = new PaymentMethodConfig(SofortTransaction::NAME, $MAID, $Key);
        $this->config->add($SofortConfig);
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
