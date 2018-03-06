<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 11:06 PM
 */


class Context
{
    public $link;
    public $smarty;

    public function __construct()
    {
        $this->link = new Link();
        $this->smarty = new Smarty();
    }

    public static function getContext()
    {
        return new Context();
    }
}
