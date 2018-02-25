<?php

require_once 'module.config.inc';
function manageResponse($response, $context, $module) {
    $xmlResponse = new SimpleXMLElement($response->getRawData());
    $responseArray = json_decode(json_encode($xmlResponse), 1);
    switch ( $response->getPaymentMethod() ) {
        case PAYPAL_PAYMENTH_METHOD:
            payPalResponse($responseArray, $response->getCustomFields(), $context, $module);
            break;
        case SEPA_PAYMENT_METHOD:
            sepaResponse($responseArray , $response->getCustomFields(), $context, $module);
            break;
        case CREDIT_CARD_METHOD:
            creditCardResponse($responseArray, $response->getCustomFields(), $context, $module);
            break;
    }
}

function payPalResponse($response, $customFields, $context, $module) {

    $orderId = $customFields->get("order_id");
    if($response["statuses"] != null &&
        $response["statuses"]["status"] != null &&
        $response["statuses"]["status"]["@attributes"] != null &&
        $response["statuses"]["status"]["@attributes"]["code"] == "201.0000") {
        updateStatus($orderId, Configuration::get('WDEE_OS_PENDING'));
    }

    $customer = $context->customer;

    Tools::redirectLink(__PS_BASE_URI__ . 'index.php?controller=order-confirmation&id_cart=' . $customFields->get("cart_id") .'&id_module='. $this->module->id .'&id_order=' . $orderId . '&key=' . $customer->secure_key);
    //update status order
    //create payment for order

}

function sepaResponse($response, $customFields, $context, $module) {
//update status order
    //create payment for order
}

function creditCardResponse($response, $customFields, $context, $module) {
//update status order
    //create payment for order
    $orderId = $customFields->get("order_id");
    if($response["statuses"] != null &&
        $response["statuses"]["status"] != null &&
        $response["statuses"]["status"]["@attributes"] != null &&
        $response["statuses"]["status"]["@attributes"]["code"] == "201.0000") {
        updateStatus($orderId, Configuration::get('WDEE_OS_PENDING'));
    }

    $customer = $context->customer;

    Tools::redirectLink(__PS_BASE_URI__ . 'index.php?controller=order-confirmation&id_cart=' . $customFields->get("cart_id") .'&id_module='. $module->id .'&id_order=' . $orderId . '&key=' . $customer->secure_key);
    //update status order
    //create payment for order
}

function updateStatus($orderNumber, $status) {
    $history = new OrderHistory();
    $history->id_order = (int)$orderNumber;
    $history->changeIdOrderState(($status), $history->id_order, true);
}

?>