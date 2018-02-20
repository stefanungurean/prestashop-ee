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
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Wirecard\PaymentSdk\Response\SuccessResponse;


class WirecardPaymentGatewayNotifyModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $config = new Config();
        $service = new TransactionService($config);

       $log = new Logger('Wirecard notifications');
       $log->pushHandler(new StreamHandler(__DIR__ . '/../../logs/notify.log', Logger::INFO));


        $notification = $service->handleNotification(file_get_contents('php://input'));

        $orderId=$notification->getData()["order-number"];

        if ($notification instanceof SuccessResponse) {
            $log->info(sprintf(
                'Transaction with id %s was successful and validation status is %s.',
                $notification->getTransactionId()

            //     $notification->isValidSignature() ? 'true' : 'false'
            ));


            $this->updateStatus($orderId, _PS_OS_PAYMENT_);

        } elseif ($notification instanceof FailureResponse) {
            $this->updateStatus($orderId, _PS_OS_ERROR_);

           // $history->changeIdOrderState((_PS_OS_ERROR_), $history->id_order, true);
            die('work in progress');
            $log->info(sprintf(
                'Transaction with id %s was failure and validation status is %s.',
                $notification->getTransactionId()
            //     $notification->isValidSignature() ? 'true' : 'false'
            ));

            foreach ($notification->getStatusCollection() as $status) {
                /**
                 * @var $status \Wirecard\PaymentSdk\Entity\Status
                  */
                $severity = ucfirst($status->getSeverity());
                $code = $status->getCode();
                $description = $status->getDescription();
                $log->warning(sprintf('%s with code %s and message "%s" occurred.<br>', $severity, $code, $description));
          }

     }

        exit();
    }
    private function updateStatus($orderNumber, $status) {
        $history = new OrderHistory();
        $history->id_order = (int)$orderNumber;
        $history->changeIdOrderState(($status), $history->id_order, true);
        $history->add();

    }

}