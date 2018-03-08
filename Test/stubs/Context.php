<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 11:06 PM
 */


class Context
{
    public static $context;
    public $link;
    public $smarty;
    public $controller;
    public $language;
    public $cookie;
    public $cart;

    public function __construct()
    {
        $this->link = new Link();
        $this->smarty = new Smarty();
        $this->controller = new OrderController();
        $this->language = new Language();
        $this->cookie = new Cookie();
        $this->cart = new Cart();
    }

    public static function getContext()
    {
        return self::$context;
    }
    public function setController($name)
    {
        $this->controller->setPhpSelf($name);
    }
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return mixed
     */
    public static function setCookie()
    {
        self::$cookie = new Cookie();
    }

    /**
     * @return mixed
     */
    public static function getCookie()
    {
        return self::$cookie;
    }
}
