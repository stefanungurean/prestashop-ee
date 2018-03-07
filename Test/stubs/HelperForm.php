<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 10:45 PM
 */


class HelperForm
{
    public $id_order;

    public function generateForm($array)
    {
        return serialize($array);
    }
}
