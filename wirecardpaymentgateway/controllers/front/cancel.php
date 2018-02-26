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

class WirecardPaymentGatewayCancelModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $logger = new Logger();
        $message = "";
        try {
            if (!$this->module->active) {
                throw new Exception($this->l('Module is not activ'));
            } else {
                $orderNumber = $_GET['order'];
                $order = new Order($orderNumber);
                if ($orderNumber == null || $order == null) {
                    throw new Exception($this->l(sprintf(
                        'Order %s do not exist',
                        $orderNumber
                    )));
                } else {
                    $paymentType = $this->module->getPaymentType($order->payment);
                    if ($paymentType === null) {
                        throw new Exception($this->l('This payment method is not available.'));
                    } elseif (!$paymentType->isAvailable()) {
                        throw new Exception($this->l('Payment method not enabled.'));
                    } elseif (!$paymentType->configuration()) {
                        throw new Exception($this->l('The merchant configuration is incorrect'));
                    } else {
                        $this->context->smarty->assign(array(
                            'reference' => $order->reference,
                            'payment' => $order->payment
                        ));
                        if ($order->current_state == _PS_OS_CANCELED_) {
                            throw new Exception($this->l('Order is already cancelled'));
                        } else {
                            $logger->log(1, $this->l(sprintf('Cancelled order %s', $orderNumber)));
                            $this->module->updateOrder($order->id, _PS_OS_CANCELED_);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $message=$e->getMessage();
        }

        if ($message!="") {
            $logger->error($message);
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
