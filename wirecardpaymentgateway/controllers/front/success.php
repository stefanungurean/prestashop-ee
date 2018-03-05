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
 * @author Wirecard AG
 * @copyright Wirecard AG
 * @license GPLv3
 */

require_once dirname(__FILE__) . '/../../vendor/autoload.php';
require_once dirname(__FILE__) . '/../../libraries/Logger.php';
require_once dirname(__FILE__) . '/../../libraries/ExceptionEE.php';

use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
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
        try {
            if (!$this->module->active) {
                throw new ExceptionEE($this->l('Module is not activ'));
            }

            $orderNumber =  Tools::getValue('order');
            $order = new Order($orderNumber);
            if ($orderNumber == null || empty($order)) {
                throw new ExceptionEE(sprintf(
                    $this->l('Order %s do not exist'),
                    $orderNumber
                ));
            }

            $paymentType = $this->module->getConfig()->getPaymentType($order->payment);
            if ($paymentType === null) {
                throw new ExceptionEE($this->l('This payment method is not available.'));
            }
            if (!$paymentType->isAvailable()) {
                throw new ExceptionEE($this->l('Payment method not enabled'));
            }
            if (!$paymentType->configuration()) {
                throw new ExceptionEE($this->l('The merchant configuration is incorrect'));
            }
            if (!$paymentType->getResponseData()) {
                throw new ExceptionEE($this->l('The order has been cancelled'));
            }

            $paymentType->setCertificate(dirname(__FILE__) . '/../../certificates/api-test.wirecard.com.crt');
            $service = new TransactionService($paymentType->getConnection(), $logger);
            $response = $service->handleResponse($paymentType->getResponseData());
            $this->processResponse($response);
        } catch (Exception $e) {
            $message=$e->getMessage();
        }

        if ($message != "") {
            $logger->error($message);
        }
        $this->context->smarty->assign(array(
            'message' => $message
        ));

        $this->setTemplate('module:wirecardpaymentgateway/views/templates/front/confirmation123.tpl');
    }

    /**
     * process response from skd
     *
     * @since 0.0.3
     *
     * @param $response
     *
     */
    private function processResponse($response)
    {
        $logger = new Logger();

        if ($response instanceof SuccessResponse) {
            $orderId = $response->getCustomFields()->get('customOrderNumber');
            if ($orderId != Tools::getValue('order')) {
                throw new ExceptionEE($this->l('The data has been modified by 3rd Party2222'));
            }

            $order = new Order($orderId);
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

        if ($response instanceof FailureResponse) {
            foreach ($response->getStatusCollection() as $status) {
                $severity = Tools::ucfirst($status->getSeverity());
                $code = $status->getCode();
                $description = $status->getDescription();
                $logger->warning(sprintf(
                    $this->l('%s with code %s and message "%s" occurred'),
                    $severity,
                    $code,
                    $description
                ));
                throw new ExceptionEE($description);
            }
        }
    }
}
