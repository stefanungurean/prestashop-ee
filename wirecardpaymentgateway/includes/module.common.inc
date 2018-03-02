<?php

require_once 'module.config.inc';
function manageResponse($response, $context, $module) {
    $xmlResponse = new SimpleXMLElement($response->getRawData());
    $responseArray = json_decode(json_encode($xmlResponse), 1);
    paymentMethodResponse($responseArray, $response->getCustomFields(), $context, $module);
}

function paymentMethodResponse($response, $customFields, $context, $module) {
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