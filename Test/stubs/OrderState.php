<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 10:45 PM
 */


class OrderState
{
    public $id;
    public $id_order;
    public function add()
    {
        $this->id= rand();
    }

    public function changeIdOrderState($status, $order)
    {
        $order= new Order($order);
        $order->update($order, $status);
    }
    public function addWithemail($sendEmail = false)
    {
        return $sendEmail;
    }
}
