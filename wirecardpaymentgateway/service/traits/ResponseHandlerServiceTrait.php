<?php
/**
 * Created by IntelliJ IDEA.
 * User: manuel.rinaldi
 * Date: 3/4/2018
 * Time: 5:09 PM
 */

require _WPC_MODULE_DIR_ . '/libraries/Logger.php';
require _WPC_MODULE_DIR_.'/vendor/autoload.php';
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Response\FailureResponse;

trait ResponseHandlerServiceTrait
{

    protected $logger;

    public function handleResponse($response, $context, $module) {
        $this->responseHandler($response, $context, $module);
    }

    public function notifyResponse($response, $context, $module ){
        $this->responseHandler($response, $context, $module);
    }


    private function responseHandler($response, $context, $module ) {
        if($response->isValidSignature()) {
            if($response instanceof SuccessResponse) {
                $this->successfulResponse($response, $context, $module );
            }else if($response instanceof FailureResponse) {
                $this->failResponse($response, $context, $module);
            }
        }else {
            $this->cancelOrder($response->customFields->get('orderId'));
        }
    }

    /**
     * @param $response
     * @param $context
     * @param $module
     */
    public function successfulResponse($response, $context, $module)
    {
        $this->logResponseStatuses($response);
        $responseArray = $response->getDate();
        $orderId = $response->customFields->get("order_id");
        if($responseArray["statuses"] != null &&
            $responseArray["statuses"]["status"] != null &&
            $responseArray["statuses"]["status"]["@attributes"] != null &&
            $responseArray["statuses"]["status"]["@attributes"]["code"] == "201.0000") {
            $this->updateStatus($orderId, Configuration::get('WDEE_OS_PENDING'));
        }

        $customer = $context->customer;

        Tools::redirectLink(__PS_BASE_URI__ . 'index.php?controller=order-confirmation&id_cart=' . $response->customFields->get("cart_id") .'&id_module='. $module->id .'&id_order=' . $orderId . '&key=' . $customer->secure_key);

    }

    public function cancelOrder($orderId)
    {
        if(empty($orderId)) {
            $orderId = Tools::getValue("id_order");
        }
        $this->updateStatus($orderId, _PS_OS_CANCELED_);


        $customer = $this->context->customer;

        Tools::redirectLink(__PS_BASE_URI__ . 'index.php?controller=order-detail&id_module='. $this->module->id .'&id_order=' . $orderId);
        //update status order
    }


    public function failResponse($response, $context, $module)
    {
        $orderId = $response->customFields->get("order_id");
        $this->updateStatus($orderId,_PS_OS_ERROR_);
        $errors = $this->logResponseStatuses($response);
        return $errors;
    }

    public function notifySuccessfulResponse()
    {
        // TODO: Implement notifySuccessfulResponse() method.
    }

    public function notifyErrorResponse()
    {
        // TODO: Implement notifyErrorResponse() method.
    }

    private function logResponseStatuses($response) {
        $logger = new Logger();
        $errors = array();
        foreach ($response->getStatusCollection() as $status) {

            $severity = Tools::ucfirst($status->getSeverity());

            $code = $status->getCode();

            $description = $status->getDescription();
            $errors[] = $description;
            $logger->warning(sprintf(

                $this->l('%s with code %s and message "%s" occurred'),

                $severity,

                $code,

                $description

            ));

        }
        return $errors;
    }

    private function updateStatus($orderNumber, $status) {
        $history = new OrderHistory();
        $history->id_order = (int)$orderNumber;
        $history->changeIdOrderState(($status), $history->id_order, true);
    }

}