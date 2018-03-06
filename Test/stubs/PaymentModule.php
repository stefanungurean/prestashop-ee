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
    protected $active = true;
    protected $context;

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
}
