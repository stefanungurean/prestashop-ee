<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 10:40 PM
 */

class PaymentModule extends Module
{
    public $id;
    public $active = false;
    protected $context;
    public $currentOrder;
    public $_path;
    public $identifier;

    public function __construct()
    {
        $this->id = rand();
        $this->context = new Context();
    }

    public static function l($text)
    {

        return $text;
    }
    public function install()
    {
        $this->active = true;
        return true;
    }
    public function uninstall()
    {
        return true;
    }
    public function registerHook()
    {
        return true;
    }
    public function validateOrder(
        $id_cart,
        $id_order_state,
        $amount_paid,
        $payment_method = 'Unknown',
        $message = null,
        $extra_vars = array(),
        $currency_special = null,
        $dont_touch_amount = false,
        $secure_key = false,
        Shop $shop = null
    ) {
        $order = new Order();
        $this->currentOrder = $order->add($id_order_state, $payment_method);
    }
    public function displayConfirmation($msg)
    {
        return $msg;
    }
}
