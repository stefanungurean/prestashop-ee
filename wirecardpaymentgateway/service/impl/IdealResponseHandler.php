<?php
/**
 * Created by IntelliJ IDEA.
 * User: manuel.rinaldi
 * Date: 3/7/2018
 * Time: 7:32 PM
 */

require_once _WPC_MODULE_DIR_ . '/service/traits/ResponseHandlerServiceTrait.php';

class IdealResponseHandler implements ResponseHandlerService
{

    use ResponseHandlerServiceTrait;

    function handleResponse($response, $context, $module)
    {
        $cart = Tools::getValue('id_cart');
        $order = Tools::getValue('id_order');
        $customer = $context->customer;
        $this->updateStatus($order, _PS_OS_PAYMENT_);
        $this->redirectToConfirm($cart, $module->id, $order, $customer);
    }

    function notifyResponse($response, $context, $module)
    {
        // TODO: Implement notifyResponse() method.
    }

    function cancelOrder($orderId)
    {
        // TODO: Implement cancelOrder() method.
    }
}