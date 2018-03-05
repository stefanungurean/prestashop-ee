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

    public function testBuildParamName()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        new ConfigurationSettings($module);
        $this->assertEquals(
            "WDEE_PAYPAL_ENABLE_METHOD",
            ConfigurationSettings::buildParamName("paypal", "enable_method")
        );
    }

    public function testGetConfigValue()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        new ConfigurationSettings($module);
        Configuration::updateValue(ConfigurationSettings::buildParamName("paypal", "enable_method"), 1);
        $this->assertEquals("1", ConfigurationSettings::getConfigValue("paypal", "enable_method"));
    }

    public function testGetPaymentType()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $configurationSettings = new ConfigurationSettings($module);
        $this->assertInstanceOf("WEEPaymentGatewayPaymentPaypal", $configurationSettings->getPaymentType('Paypal'));
        $this->assertNull($configurationSettings->getPaymentType('Paypal1'));
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

    public function testSetDefaults()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $configurationSettings = new ConfigurationSettings($module);
        Configuration::updateValue(ConfigurationSettings::buildParamName("paypal", "enable_method"), 1);
        $this->assertEquals("1", ConfigurationSettings::getConfigValue("paypal", "enable_method"));
        $configurationSettings::setDefaults();
        $this->assertEquals("0", ConfigurationSettings::getConfigValue("paypal", "enable_method"));
    }
}
