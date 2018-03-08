<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 11:06 PM
 */


class Address
{
    public $firstname;
    public $lastname;
    public $city;
    public $address1;
    public $address2;
    public $postcode;
    public $id_country;
    public $db = array(
        1=>array('firstname'=>"edi",
            'lastname'=>"edi",
            'city'=>"111",
            'address1'=>"dsdss",
            'address2'=>"dsds",
            'postcode'=>"80000",
            'id_country'=>"1"
        )
    );
    public function __construct($id)
    {
        if ($this->db[$id]) {
            foreach ($this->db[$id] as $key => $value) {
                $this->{$key}=$value;
            }
        }
    }
}
