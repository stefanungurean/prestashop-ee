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
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\TransactionService;

class WirecardPaymentGatewayCancelModuleFrontController extends ModuleFrontController
{
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
            $orderNumber=$_GET['order'];
            $order = new Order($orderNumber);
            if ($orderNumber == null||$order == null) {
                $message = $this->l(sprintf(
                    'Order %s do not exist %s',
                    $orderNumber
                ));
                $logger->error($message);
            } else {
                if ($order->current_state != _PS_OS_CANCELED_) {
                    $this->updateStatus($order->id, _PS_OS_CANCELED_);
                    $logger->error($this->l(sprintf('Cancelled order %s',  $orderNumber)));
                }
                else{
                    $logger->error($this->l(sprintf('Order %s is already cancelled',  $orderNumber)));
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
     * updates order status
     *
     * @since 0.0.2
     *
     */
    private function updateStatus($orderNumber, $status)
    {
        $history = new OrderHistory();
        $history->id_order = (int)$orderNumber;
        $history->changeIdOrderState($status, $history->id_order, true);
        $history->sendEmail($orderNumber);
    }
}
