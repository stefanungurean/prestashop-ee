<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 11:13 PM
 */

class CurrencyCore
{
    public $iso_code;
    public $db=array(1=>array('iso_code'=>"EUR"));
    public function __construct($id)
    {
        if ($this->db[$id]) {
            foreach ($this->db[$id] as $key => $value) {
                $this->{$key}=$value;
            }
        }
    }
}
