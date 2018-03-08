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
        $module->install();
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
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));

        $module->initiatePayment("Paypal");
        $this->assertEquals('Module is not active', $module->getContext()->cookie->eeMessage);

        $module->install();
        $cart = new Cart();
        $cart->exists = true;
        $module->getContext()->cart = $cart;
        $module->initiatePayment("Paypal");
        $this->assertEquals(
            'Cart cannot be loaded or an order has already been placed using this cart',
            $module->getContext()->cookie->eeMessage
        );

        $cart->exists = false;
        $module->getContext()->cart = $cart;
        $module->initiatePayment("Paypal");
        $this->assertEquals('Unable to load basket', $module->getContext()->cookie->eeMessage);

        $module->getContext()->cookie->id_cart = $cart->id;
        $module->initiatePayment("Paypal1");
        $this->assertEquals('This payment method is not available', $module->getContext()->cookie->eeMessage);

        $module->initiatePayment("Paypal");
        $this->assertEquals('Payment method not enabled', $module->getContext()->cookie->eeMessage);

        $cart->id_currency = 1;
        $module->getContext()->cart = $cart;
        Configuration::updateValue(ConfigurationSettings::buildParamName("paypal", "enable_method"), 1);
        Configuration::updateValue(ConfigurationSettings::buildParamName("paypal", "wirecard_server_url"), 1);
        $module->initiatePayment("Paypal");
        $this->assertEquals('The merchant configuration is incorrect', $module->getContext()->cookie->eeMessage);

        Configuration::updateValue(
            ConfigurationSettings::buildParamName(
                "paypal",
                "wirecard_server_url"
            ),
            'https://api-test.wirecard.com'
        );
        $cart->addProduct(200, 10);
        $module->getContext()->cart = $cart;
        $module->initiatePayment("Paypal");
        $this->assertEquals('Products out of stock', $module->getContext()->cookie->eeMessage);

        $cart = new Cart();
        $cart->secure_key = rand();
        $cart->id_currency = 1;
        $cart->id_customer = 1;
        $cart->id_lang = 1;
        $cart->id_carrier = 1;
        $cart->id_address_delivery = 1;
        $cart->addProduct(2, 10);
        $cart->addProduct(1, 15);
        $module->getContext()->cart = $cart;
        $module->getContext()->cookie->id_cart = $cart->id;
        $module->initiatePayment("Paypal");
        $log = new PrestaShopLogger();
        $log = $log->getLast();
        $this->assertEquals('The resource was successfully created.', $module->getContext()->cookie->eeMessage);
        $this->assertContains('URL:', $log->message);
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
