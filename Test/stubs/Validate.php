<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 10:45 PM
 */


class Validate
{
    public static function isLoadedObject($cart)
    {
        return !$cart->exists;
    }
}