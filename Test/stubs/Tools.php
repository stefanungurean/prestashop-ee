<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 10:45 PM
 */


class Tools
{
    public static function strtoupper($test)
    {
        return $test;
    }
    public static function strtolower($test)
    {
        return $test;
    }
    public static function strlen($str, $encoding = 'UTF-8')
    {
        if (is_array($str)) {
            return false;
        }
        $str = html_entity_decode($str, ENT_COMPAT, 'UTF-8');
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, $encoding);
        }
        return strlen($str);
    }
    public static function substr($str, $start, $length = false, $encoding = 'utf-8')
    {
        if (is_array($str)) {
            return false;
        }
        if (function_exists('mb_substr')) {
            return mb_substr($str, (int)$start, ($length === false ? Tools::strlen($str) : (int)$length), $encoding);
        }
        return substr($str, $start, ($length === false ? Tools::strlen($str) : (int)$length));
    }
    public static function isSubmit()
    {
        return true;
    }
    public static function getValue()
    {
        return true;
    }
    public static function getAdminTokenLite()
    {
        return true;
    }
    public static function displayError($msg)
    {
        return $msg;
    }
    public static function redirect($link)
    {
        $logger = new Logger();
        $logger->log(1, "URL:".$link);
    }

    public static function getRemoteAddr()
    {
        return "127.0.0.1";
    }
    public static function ucfirst($str)
    {
        return Tools::strtoupper(Tools::substr($str, 0, 1)).Tools::substr($str, 1);
    }
}
