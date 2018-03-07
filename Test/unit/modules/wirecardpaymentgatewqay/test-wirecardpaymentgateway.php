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

    public function testGetContent()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $content = $module->getContent();
        $this->assertNotEquals('', $content);
    }

    public function testHookActionFrontControllerSetMedia()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $module->getContext()->setController('order');
        $module->hookActionFrontControllerSetMedia();
        $this->assertNotEquals(
            array(),
            $module->getContext()->getController()->getStylesheet('module-' . $module->name . '-style')
        );
        $module->getContext()->getController()->clearStylesheet();
        $module->getContext()->setController('order1');
        $module->hookActionFrontControllerSetMedia();
        $this->assertEquals(
            array(),
            $module->getContext()->getController()->getStylesheet('module-' . $module->name . '-style')
        );
    }
    public function testhookDisplayHeader()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        Context::$context=new Context();
        Context::$context->cookie->eeMessage="Hello";
        $module->hookDisplayHeader();

        $this->assertNotEquals(array(), Context::getContext()->controller->errors);
        Context::getContext()->controller->errors=array();
        Context::$context->cookie->eeMessage=false;
        $module->hookDisplayHeader();
        $this->assertEquals(array(), Context::getContext()->controller->errors);
    }
    public function testInitiatePayment()
    {
        $this->assertTrue(true);
    }

    public function testHelperRender()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $render = $module->helperRender(array("test"=>"testdata"), array("test1"=>"testdata1"));
        $this->assertNotEquals('', $render);
    }

    public function testChangeModuleNameByOrder()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $cart = new Cart();
        $cart->secure_key =rand();
        $cart->addProduct(2, 10);
        $cart->addProduct(1, 15);

        $orderManagement = new OrderMangement($module);
        $currentOrder = $orderManagement->addOrder($cart, "Paypal");
        Configuration::updateValue(ConfigurationSettings::buildParamName("paypal", "enable_method"), 1);
        $module->changeModuleNameByOrder($currentOrder);
        $this->assertEquals('Paypal', $module->displayName);
    }
}
