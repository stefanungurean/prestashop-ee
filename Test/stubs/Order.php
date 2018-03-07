
<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 11:06 PM
 */


class Order
{

    public $payment;
    public $current_state;
    public static $db=array();
    public function __construct($order = null)
    {
        if ($order != null) {
            $orderData = self::get($order);
            $this->current_state = $orderData->current_state;
            $this->payment = $orderData->payment;
        }
    }

    public function add($status, $payment)
    {
        $this->payment=$payment;
        $this->current_state=$status;
        self::$db[]= $this;
        return count(self::$db)-1;
    }


    public function update($order, $status)
    {
        $this->current_state = $status;
        self::$db[$order] = $this ;
    }

    public static function get($order = null)
    {
        if (isset(self::$db[$order])) {
            return self::$db[$order];
        }
        return null;
    }
}
