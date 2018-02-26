<?php
/**
 * Created by IntelliJ IDEA.
 * User: eduard.stroia
 * Date: 26.02.2018
 * Time: 19:02
 */
class OrderMangement
{
    const WDEE_OS_AWAITING = 'WDEE_OS_AWAITING';
    const WDEE_OS_FRAUD = 'WDEE_OS_FRAUD';

    private $module;
    public function __construct($module)
    {
        $this->module=$module;
    }

    public function addOrder($cart, $paymentMethod)
    {
        $this->module->validateOrder(
            $cart->id,
            Configuration::get(self::WDEE_OS_AWAITING),
            $cart->getOrderTotal(true),
            $paymentMethod,
            null,
            array(),
            null,
            false,
            $cart->secure_key
        );
        return $this->module->currentOrder;
    }

    public function updateOrder($orderNumber, $orderStatus)
    {
        $history = new OrderHistory();
        $history->id_order = (int)$orderNumber;
        $history->changeIdOrderState(($orderStatus), $orderNumber, true);
    }
}