<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 10:40 PM
 */

namespace PrestaShop\PrestaShop\Adapter\StockManager;


class PaymentModule
{

    protected $active = true;
    protected $context;

    function __construct() {
        $this->context = new \Context();
    }

    protected static function l($text){}

}