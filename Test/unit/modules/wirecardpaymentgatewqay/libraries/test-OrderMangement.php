<?php
/**
 * Created by IntelliJ IDEA.
 * User: eduard.stroia
 * Date: 05.03.2018
 * Time: 11:09
 */

class OrderMangementTest extends \PHPUnit_Framework_TestCase
{
    public function testInitiate()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $orderMangement = new OrderMangement($module);
        $this->assertInstanceOf("OrderMangement", $orderMangement);
    }

    public function testAddOrder()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        if ($module->id) {
            $module->uninstall();
        }
        $this->assertTrue($module->install());

        $cart = new Cart();
        $cart->secure_key =rand();
        $cart->addProduct(2, 10);
        $cart->addProduct(1, 15);

        $orderManagement = new OrderMangement($module);
        $currentOrder = $orderManagement->addOrder($cart, "Sepa");

        $this->assertNotEquals("", $currentOrder);
    }

    public function testUpdateOrder()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $module->install();
        $cart = new Cart();
        $cart->secure_key =rand();
        $cart->addProduct(2, 10);
        $cart->addProduct(1, 15);
        Configuration::updateValue(ConfigurationSettings::buildParamName("sepa", "enable_method"), 1);

        $orderManagement = new OrderMangement($module);
        $currentOrder = $orderManagement->addOrder($cart, "Sepa");
        $order= new Order($currentOrder);
        $this->assertNotEquals(_PS_OS_CANCELED_, $order->current_state);
        $orderManagement->updateOrder($currentOrder, _PS_OS_CANCELED_);
        $order= new Order($currentOrder);
        $this->assertEquals(_PS_OS_CANCELED_, $order->current_state);
    }

    public function testSetStatus()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $orderMangement=  new OrderMangement($module);
        $orderMangement->setStatus();
        $this->assertTrue(
            Configuration::get(OrderMangement::WDEE_OS_AWAITING)&&
            Configuration::get(OrderMangement::WDEE_OS_FRAUD)
        );
    }
}
