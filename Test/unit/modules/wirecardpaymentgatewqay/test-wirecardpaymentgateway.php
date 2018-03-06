<?php
/**
 * Created by IntelliJ IDEA.
 * User: eduard.stroia
 * Date: 05.03.2018
 * Time: 11:09
 */

class WirecardPaymentGatewayTest extends \PHPUnit_Framework_TestCase
{
    public function testInitiateModule()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $this->assertNotNull($module);
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway11'));
        $this->assertNull($module);
    }


    public function testInstall()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        if ($module->id) {
            $module->uninstall();
        }
        $this->assertTrue($module->install());
    }

    public function testUninstall()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        if (!$module->id) {
            $module->install();
        }
        $this->assertTrue($module->uninstall());
    }

    public function testGetName()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $this->assertEquals('wirecardpaymentgateway', $module->name);
    }

    public function testGetDisplayName()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $this->assertEquals('Wirecard payment proccesing gateway', $module->displayName);
    }

    public function testGetOrderMangement()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $OrderMangement=$module->getOrderMangement();
        $this->assertInstanceOf("OrderMangement", $OrderMangement);
    }

    public function testGetConfig()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $config=$module->getConfig();
        $this->assertInstanceOf("ConfigurationSettings", $config);
    }

    public function testGetContext()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $context=$module->getContext();
        $this->assertInstanceOf("Context", $context);
    }
    public function testHookPaymentOptions()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        Configuration::updateValue(ConfigurationSettings::buildParamName("paypal", "enable_method"), 1);
        $paymentOptions = $module->HookPaymentOptions();
        $this->assertNotEquals(array(), $paymentOptions);
        Configuration::updateValue(ConfigurationSettings::buildParamName("paypal", "enable_method"), 0);
        $paymentOptions = $module->HookPaymentOptions();
        $this->assertEquals(array(), $paymentOptions);
    }
}
