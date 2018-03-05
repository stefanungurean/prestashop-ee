<?php
/**
 * Created by IntelliJ IDEA.
 * User: eduard.stroia
 * Date: 05.03.2018
 * Time: 11:09
 */

class ExceptionEETest extends \PHPUnit_Framework_TestCase
{
    public function testInitiate()
    {
        $exceptionEE=  new ExceptionEE();
        $this->assertInstanceOf("ExceptionEE", $exceptionEE);
    }
}