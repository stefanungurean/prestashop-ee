<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 10:58 PM
 */

class Configuration
{
    private static $db=array();
    public static function get($field)
    {
        $field = strtoupper($field);
        if (isset(self::$db[$field])) {
            return self::$db[$field];
        }
        return "";
    }
    public static function updateValue($field, $value)
    {
        $field=strtoupper($field);
        self::$db[$field] = $value;
        return true;
    }

    public static function getDb()
    {
        return self::$db;
    }
}
