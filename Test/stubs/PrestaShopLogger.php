<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 11:06 PM
 */

class PrestaShopLogger
{
    public static $logs=array();
    public $serverity;
    public function add()
    {
        self::$logs[]=$this;
    }
    public function getLast()
    {
        return self::$logs[count(self::$logs)-1];
    }
}
