<?php
/**
 * Created by IntelliJ IDEA.
 * User: eduard.stroia
 * Date: 05.03.2018
 * Time: 11:09
 */

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testInitiate()
    {
        $logger=  new Logger();
        $this->assertInstanceOf("Logger", $logger);
    }

    public function testLog()
    {
        $logger = new Logger();
        $logger->log(1, "message");
        $log= new PrestaShopLogger(Db::getInstance()->Insert_ID());
        $this->assertEquals(1, $log->severity);
    }

    public function testEmergency()
    {
        $logger = new Logger();
        $logger->emergency("message");
        $log= new PrestaShopLogger(Db::getInstance()->Insert_ID());
        $this->assertEquals(4, $log->severity);
    }

    public function testAlert()
    {
        $logger = new Logger();
        $logger->alert("message");
        $log= new PrestaShopLogger(Db::getInstance()->Insert_ID());
        $this->assertEquals(4, $log->severity);
    }

    public function testCritical()
    {
        $logger = new Logger();
        $logger->critical("message");
        $log= new PrestaShopLogger(Db::getInstance()->Insert_ID());
        $this->assertEquals(4, $log->severity);
    }

    public function testError()
    {
        $logger = new Logger();
        $logger->error("message");
        $log= new PrestaShopLogger(Db::getInstance()->Insert_ID());
        $this->assertEquals(3, $log->severity);
    }

    public function testWarning()
    {
        $logger = new Logger();
        $logger->warning("message");
        $log= new PrestaShopLogger(Db::getInstance()->Insert_ID());
        $this->assertEquals(2, $log->severity);
    }

    public function testNotice()
    {
        $logger = new Logger();
        $logger->notice("message");
        $log= new PrestaShopLogger(Db::getInstance()->Insert_ID());
        $this->assertEquals(1, $log->severity);
    }

    public function testInfo()
    {
        $logger = new Logger();
        $logger->info("message");
        $log= new PrestaShopLogger(Db::getInstance()->Insert_ID());
        $this->assertEquals(1, $log->severity);
    }

    public function testDebug()
    {
        $logger = new Logger();
        $logger->debug("message");
        $log= new PrestaShopLogger(Db::getInstance()->Insert_ID());
        $this->assertEquals(1, $log->severity);
    }
}
