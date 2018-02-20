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
use \Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\TransactionService;

define("PAYPAL_PAYMENTH_METHOD", "paypal");
define("SEPA_PAYMENT_METHOD", "sepa");
define("CREDIT_CARD_METHOD", "creditcard");

class WirecardPaymentGatewaySuccessModuleFrontController extends ModuleFrontController
{


    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {

        $config = new Config();
        $service = new TransactionService($config);
        if($_POST) {
            $response = $service->handleResponse($_POST);
            if($response instanceof SuccessResponse) {
                $xmlResponse = new SimpleXMLElement($response->getRawData());
                $responseArray = json_decode(json_encode($xmlResponse), 1);
                switch ( $response->getPaymentMethod() ) {
                    case PAYPAL_PAYMENTH_METHOD:
                        $this->payPalResponse($responseArray, $response->getCustomFields());
                        break;
                    case SEPA_PAYMENT_METHOD:
                        $this->sepaResponse($responseArray , $response->getCustomFields());
                        break;
                    case CREDIT_CARD_METHOD:
                        $this->creditCardResponse($responseArray, $response->getCustomFields());
                        break;
                }
            }
        }
        Tools::redirect("order-confirmation");
    }

    private function payPalResponse($response, $customFields) {

        $orderId = $response["order-number"];
        if($response["statuses"] != null &&
            $response["statuses"]["status"] != null &&
            $response["statuses"]["status"]["@attributes"] != null &&
            $response["statuses"]["status"]["@attributes"]["code"] == "201.0000") {
            $this->updateStatus($orderId, Configuration::get('WDEE_OS_PENDING'));
        }

        $customer = $this->context->customer;

        Tools::redirectLink(__PS_BASE_URI__ . 'index.php?controller=order-confirmation&id_cart=' . $customFields->get("cart_id") .'&id_module='. $this->module->id .'&id_order=' . $orderId . '&key=' . $customer->secure_key);
            //update status order
            //create payment for order

    }

    private function sepaResponse($response, $customFields) {
//update status order
        //create payment for order
    }

    private function creditCardResponse($response, $customFields) {
//update status order
        //create payment for order
    }

    private function updateStatus($orderNumber, $status) {

        $history = new OrderHistory();
        $history->id_order = (int)$orderNumber;
        $history->changeIdOrderState(($status), $history->id_order, true);
        $history->add();

    }
}
