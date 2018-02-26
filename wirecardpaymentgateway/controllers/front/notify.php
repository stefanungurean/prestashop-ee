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
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../../libraries/Logger.php';
require_once __DIR__.'/../../libraries/ExceptionEE.php';

use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\TransactionService;

class WirecardPaymentGatewayNotifyModuleFrontController extends ModuleFrontController
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
                throw new ExceptionEE($this->l('Module is not activ'));
            }
            $orderNumber = $_GET['order'];
            $order = new Order($orderNumber);
            if ($orderNumber == null || $order == null) {
                throw new ExceptionEE($this->l(sprintf(
                    'Order %s do not exist',
                    $orderNumber
                )));
            }

            $paymentType = $this->module->getConfig()->getPaymentType($order->payment);
            if ($paymentType === null) {
                throw new ExceptionEE($this->l('This payment method is not available.'));
            }
            if (!$paymentType->isAvailable()) {
                throw new ExceptionEE($this->l('Payment method not enabled.'));
            }
            if (!$paymentType->configuration()) {
                throw new ExceptionEE($this->l('The merchant configuration is incorrect'));
            }

            $paymentType->setCertificate(__DIR__ . '/../../certificates/api-test.wirecard.com.crt');
            $service = new TransactionService($paymentType->getConnection(), $logger);
            $notification = $service->handleNotification(file_get_contents('php://input'));
            $this->processResponse($notification);
        } catch (Exception $e) {
            $message=$e->getMessage();
        }

        if ($message!="") {
            $logger->error($message);
        }
        exit;
    }


    public function processResponse($notification)
    {
        $logger = new Logger();
        if (!$notification->isValidSignature()) {
            throw new ExceptionEE($this->l('The data has been modified by 3rd Party'));
        }
        if ($notification instanceof SuccessResponse) {
            $responseArray = $notification->getData();
            $orderId = $notification->getCustomFields()->get('customOrderNumber');
            if ($orderId!=$_GET['order']) {
                throw new ExceptionEE($this->l('The data has been modified by 3rd Party'));
            }
            $order = new Order($orderId);
            if ($order->current_state == $this->getStatus($responseArray['transaction-state']) ||
                $order->current_state == _PS_OS_PAYMENT_ ||
                $order->current_state == _PS_OS_CANCELED_) {
                throw new ExceptionEE($this->l(sprintf(
                    'Order with id %s was already notified',
                    $orderId
                )));
            }
            $this->module->getOrderMangement()->updateOrder(
                $orderId,
                $this->getStatus($responseArray['transaction-state'])
            );
            $logger->info(sprintf(
                'Order with id %s was notified',
                $orderId
            ));
        }
        if ($notification instanceof FailureResponse) {
            foreach ($notification->getStatusCollection() as $status) {
                $severity = ucfirst($status->getSeverity());
                $code = $status->getCode();
                $description = $status->getDescription();
                $logger->warning(sprintf(
                    '%s with code %s and message "%s" occurred.<br>',
                    $severity,
                    $code,
                    $description
                ));
            }
        }
    }

    private function getStatus($status)
    {
        switch ($status) {
            case "error ":
            case "failure":
                $statusResult=_PS_OS_ERROR_;
                break;
            default:
                $statusResult=_PS_OS_PAYMENT_;
                break;
        }
        return $statusResult;
    }
}
