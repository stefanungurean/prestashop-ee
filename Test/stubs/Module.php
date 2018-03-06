<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 11:10 PM
 */

class Module extends Smarty
{
    private static $modules = array('wirecardpaymentgateway'=>"WirecardPaymentGateway");

    public $name;

    public static function getInstanceByName($module)
    {
        if (isset(self::$modules[$module])) {
            $className = self::$modules[$module];
            return new $className();
        }
        return null;
    }
}
