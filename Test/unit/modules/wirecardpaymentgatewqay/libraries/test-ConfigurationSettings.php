<?php
/**
 * Created by IntelliJ IDEA.
 * User: eduard.stroia
 * Date: 05.03.2018
 * Time: 11:09
 */

class ConfigurationSettingsTest extends \PHPUnit_Framework_TestCase
{
    public function testInitiate()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $configurationSettings =  new ConfigurationSettings($module);
        $this->assertInstanceOf("ConfigurationSettings", $configurationSettings);
    }

    public function testRenderForm()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $configurationSettings= new ConfigurationSettings($module);

        $this->assertNotEquals("", $configurationSettings->renderForm());
    }

    public function testBuildParamName()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        new ConfigurationSettings($module);
        $this->assertEquals(
            "WDEE_PAYPAL_ENABLE_METHOD",
            ConfigurationSettings::buildParamName("paypal", "enable_method")
        );
    }

    public function testPostValidation()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $configurationSettings=new ConfigurationSettings($module);
        $configurationSettings->postValidation();
        $module->postErrors;
        $this->assertNull($module->postErrors);
    }

    public function testPostProcess()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $configurationSettings=new ConfigurationSettings($module);
        $this->assertEquals('Settings updated', $configurationSettings->postProcess());
    }

    public function testSetDefaults()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $configurationSettings = new ConfigurationSettings($module);
        Configuration::updateValue(ConfigurationSettings::buildParamName("paypal", "enable_method"), 1);
        $this->assertEquals("1", ConfigurationSettings::getConfigValue("paypal", "enable_method"));
        $this->assertTrue($configurationSettings::setDefaults());
        $this->assertEquals("0", ConfigurationSettings::getConfigValue("paypal", "enable_method"));
    }

    public function testGetPaymentTypes()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $configurationSettings = new ConfigurationSettings($module);
        $this->assertContainsOnlyInstancesOf(
            'WEEPaymentGatewayPaymentPaypal',
            $configurationSettings->getPaymentTypes('Paypal')
        );
        $this->assertContainsOnlyInstancesOf('WEEPaymentGatewayPayment', $configurationSettings->getPaymentTypes());
        $this->assertArrayNotHasKey(
            ConfigurationSettings::TEXT_CLASS_NAME,
            $configurationSettings->getPaymentTypes('Paypal1')
        );
    }

    public function testGetPaymentType()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $configurationSettings = new ConfigurationSettings($module);
        $this->assertInstanceOf("WEEPaymentGatewayPaymentPaypal", $configurationSettings->getPaymentType('Paypal'));
        $this->assertNull($configurationSettings->getPaymentType('Paypal1'));
    }




    public function testGetConfigValue()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        new ConfigurationSettings($module);
        Configuration::updateValue(ConfigurationSettings::buildParamName("paypal", "enable_method"), 1);
        $this->assertEquals("1", ConfigurationSettings::getConfigValue("paypal", "enable_method"));
    }
}
