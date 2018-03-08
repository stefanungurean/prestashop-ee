<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 11:06 PM
 */


class Cart
{
    public $exists = false;
    public $id;
    public $secure_key;
    public $id_lang;
    private $amount_total;
    public $id_customer;
    public $id_carrier;
    public $id_address_delivery;
    public $id_currency;
    public $products = array();
    public function __construct()
    {
        $this->id = rand();
        $this->amount_total = 0;
    }
    public function addProduct($quantity, $amount)
    {
        $this->products[]=$quantity;
        $this->amount_total+=$quantity*$amount;
    }

    public function getOrderTotal($true)
    {
        return $this->amount_total;
    }
    public function orderExists()
    {
        return false;
    }
    public function checkQuantities()
    {
        foreach ($this->products as $product) {
            if ($product > 5) {
                return false;
            }
        }
        return true;
    }
    public function getProducts()
    {
        return array();
    }
    public function getTotalShippingCost()
    {
        return 0;
    }
}
