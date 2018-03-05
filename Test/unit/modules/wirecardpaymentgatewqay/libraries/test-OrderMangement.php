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
        if ($module->id)
            $module->uninstall();
        $module->install();

        $module->active = true;
        try {
            if (is_null($module->getContext()->cart)) {

                $module->getContext()->cart =
                    new Cart($module->getContext()->cookie->id_cart);
            }
            if (is_null($module->getContext()->cart->id_currency)) {
                $module->getContext()->cart->id_currency = $module->getContext()->cookie->id_currency;
            }
            if (is_null($module->getContext()->cart->id_currency)) {
                $module->getContext()->cart->secure_key = $module->getContext()->cookie->secure_key;
            }
            if (is_null($module->getContext()->cart->id_lang)) {
                $module->getContext()->cart->id_lang = $module->getContext()->cookie->id_lang;
            }
            if (is_null($module->getContext()->cart->id_carrier)) {
                $module->getContext()->cart->id_carrier = 2;
            }
            $module->getContext()->cart->updateQty(1, 5);
            if (is_null($module->getContext()->cart->id)) {
                $module->getContext()->cart->add();
            }


            $orderMangement = new OrderMangement($module);
            $currentOrder = $orderMangement->addOrder($module->getContext()->cart, "Sepa");
        }
        catch(Exception $e){
        }

        $this->assertNotFalse($currentOrder);
        //$this->assertNotEquals("");
    }

    public function testSetStatus()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $orderMangement=  new OrderMangement($module);
        $orderMangement->setStatus();
        $this->assertTrue(Configuration::get(OrderMangement::WDEE_OS_AWAITING)&&Configuration::get(OrderMangement::WDEE_OS_FRAUD));
    }

    public function testUpdateOrder()
    {
        $orderNumber=8;
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));

        $orderMangement=  new OrderMangement($module);
        $this->assertNotFalse($orderMangement->updateOrder($orderNumber,_PS_OS_CANCELED_));
        $order= new Order($orderNumber);
        $this->assertTrue($order->current_state == _PS_OS_CANCELED_);
    }



}