<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 11:06 PM
 */


class Cart
{
    public $id;
    public $secure_key;
    private $amount_total;
    public function __construct()
    {
        $this->id = rand();
        $this->amount_total = 0;
    }
    public function addProduct($quantity, $amount)
    {
        $this->amount_total+=$quantity*$amount;
    }

    public function getOrderTotal($true)
    {
        return $this->amount_total;
    }
}
