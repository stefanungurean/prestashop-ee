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
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Transaction\SofortTransaction;
use Wirecard\PaymentSdk\TransactionService;

class WirecardPaymentGatewaySuccessModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $logger = new Logger();
        $message = "";
        if (!$this->module->active) {
            $message = $this->l('Module is not active');
            $logger->error($message);
        } elseif (!Configuration::get($this->module->buildParamName('paypal', 'enable_method'))) {
            $message = $this->l('Payment method not available');
            $logger->error($message);
        } elseif (($config = $this->configuration())===false) {
            $message = $this->l('The merchant configuration is incorrect');
            $logger->error($message);
        } else {
            if ($_POST) {
                $config->setPublicKey(file_get_contents(
                    __DIR__ . '/../../certificates/api-test.wirecard.com.crt'
                ));

                $service = new TransactionService($config, $logger);
                $response = $service->handleResponse($_POST);
                // ## Payment results
                 if (!$response->isValidSignature()) {
                    $message = $this->l('The data has been modified by 3rd Party');
                    $logger->error($message);
                } elseif ($response instanceof SuccessResponse) {
                    $orderId = $response->getCustomFields()->get('customOrderNumber');
                    $order = new Order((int)($orderId));
                    if ($order== null || $orderId!=$_GET['order']) {
                        $message = $this->l('The data has been modified by 3rd Party');
                        $logger->error($message);
                    } else {
                        $logger->log(1, sprintf(
                            'Order %s confirm successfully ',
                            $orderId
                        ));
                        $carrier = new Carrier((int)$order->id_carrier, (int)$order->id_lang);
                        $customer = new Customer($order->id_customer);

                        $this->context->smarty->assign(array(
                            'email' => $customer->email,
                            'reference' => $order->reference,
                            'payment' => $order->payment,
                            'carrier' => $carrier->name,
                            'delay' => $carrier->delay
                        ));
                    }
                } elseif ($response instanceof FailureResponse) {
                    foreach ($response->getStatusCollection() as $status) {
                        $severity = ucfirst($status->getSeverity());
                        $code = $status->getCode();
                        $description = $status->getDescription();
                        $message = $description;
                        $logger->warning(sprintf(
                            '%s with code %s and message "%s" occurred.<br>',
                            $severity,
                            $code,
                            $description
                        ));
                    }
                }
                // Otherwise a cancel information is printed
            } else {
                $message = $this->l('The transaction has been cancelled.');
                $logger->warning($message);
            }
        }

        $this->context->smarty->assign(array(
            'message' => $message
        ));
        $this->setTemplate('module:wirecardpaymentgateway/views/templates/front/confirmation.tpl');
    }

    private function configuration()
    {
        $paymentMethod="paypal";
        $currency = new CurrencyCore($this->context->cart->id_currency);
        $currencyIsoCode = $currency->iso_code;
        $baseUrl = Configuration::get($this->module->buildParamName($paymentMethod, 'wirecard_server_url'));
        $httpUser = Configuration::get($this->module->buildParamName($paymentMethod, 'http_user'));
        $httpPass = Configuration::get($this->module->buildParamName($paymentMethod, 'http_password'));
        $MAID = Configuration::get($this->module->buildParamName($paymentMethod, 'maid'));
        $Key = Configuration::get($this->module->buildParamName($paymentMethod, 'secret')) ;

        $config = new Config($baseUrl, $httpUser, $httpPass, $currencyIsoCode);
        $logger = new Logger();
        $transactionService = new TransactionService($config, $logger);

        if (!$transactionService->checkCredentials()) {
            return false;
        }

        $ConfigPayment = new PaymentMethodConfig(PayPalTransaction::NAME, $MAID, $Key);
        $config->add($ConfigPayment);
        return $config;
    }
}
