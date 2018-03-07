<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 10:45 PM
 */


class OrderHistory
{
    public $id_order;

    public function changeIdOrderState($status, $orderNumber)
    {
        $order= new Order($orderNumber);
        $order->update($orderNumber, $status);
    }
    public function addWithemail($sendEmail = false)
    {
        return $sendEmail;
    }
}
