<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 11:06 PM
 */


class Customer
{
    public $firstname;
    public $lastname;
    public $email;
    public $id_gender;
    public $birthday;

    public $db=array(1=>array('firstname'=>"edi",
        'lastname'=>"edi",
        'email'=>"test@test.com",
        'id_gender'=>"1",
        'birthday'=>"0000-00-00"));
    public function __construct($id)
    {
        if ($this->db[$id]) {
            foreach ($this->db[$id] as $key => $value) {
                $this->{$key}=$value;
            }
        }
    }
}
