<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 11:10 PM
 */

class Module
{

    public $name;

    public static function getInstanceByName() { return new Module(); }

    public static function buildParamName() {}
}