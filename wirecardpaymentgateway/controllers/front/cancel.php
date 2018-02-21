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
use Wirecard\PaymentSdk\TransactionService;


class WirecardPaymentGatewayCancelModuleFrontController extends ModuleFrontController
{
    private $config;

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $logger = new Logger('Wirecard success');
        $message = "";
        if (!$this->module->active) {
            $message = $this->l('Module is not active');
            $logger->error($message);
        } elseif (!Configuration::get($this->module->buildParamName('paypal', 'enable_method'))) {
            $message = $this->l('Payment method not available');
            $logger->error($message);
        } else {
            $order = new Order($_GET['order']);
            if ($order == null) {
                $message = $this->l('Order do not exist: '. $_GET['order']);
                $logger->error($message);
            } else {

                $logger->error($this->l('Cancelled order : '. $_GET['order']));
                if ($order->getCurrentOrderState() != _PS_OS_CANCELED_) {
                    $this->updateStatus($order->id, _PS_OS_CANCELED_);
                }
                $this->context->smarty->assign(array(
                    'reference' => $order->reference,
                    'payment' => $order->payment
                ));
            }
        }
        $this->context->smarty->assign(array(
            'message' => $message
        ));
        $this->setTemplate('module:wirecardpaymentgateway/views/templates/front/cancel.tpl');
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
        $payPalMAID = Configuration::get($this->module->buildParamName('paypal', 'maid'));
        $payPalKey = Configuration::get($this->module->buildParamName('paypal', 'secret')) ;
        $logger = new Logger('Wirecard success');


        $this->config = new Config($baseUrl, $httpUser, $httpPass, $currencyIsoCode);
        $transactionService = new TransactionService($this->config, $logger);

        if (!$transactionService->checkCredentials()) {
            return false;
        }

        $payPalConfig = new PaymentMethodConfig(PayPalTransaction::NAME, $payPalMAID, $payPalKey);
        $this->config->add($payPalConfig);
        return true;
    }

    /**
     * updates order status
     *
     * @since 0.0.2
     *
     */
    private function updateStatus($orderNumber, $status) {

        $history = new OrderHistory();
        $history->id_order = (int)$orderNumber;
        $history->changeIdOrderState($status, $history->id_order, true);
        $history->sendEmail($orderNumber);
    }
}
