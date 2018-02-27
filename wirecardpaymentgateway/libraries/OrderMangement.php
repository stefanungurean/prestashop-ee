<?php
/**
 * Created by IntelliJ IDEA.
 * User: eduard.stroia
 * Date: 26.02.2018
 * Time: 19:02
 */
class OrderMangement
{
    const WDEE_OS_AWAITING = 'WDEE_OS_AWAITING';
    const WDEE_OS_FRAUD = 'WDEE_OS_FRAUD';

    private $module;
    public function __construct($module)
    {
        $this->module=$module;
    }

    public function addOrder($cart, $paymentMethod)
    {
        $this->module->validateOrder(
            $cart->id,
            Configuration::get(self::WDEE_OS_AWAITING),
            $cart->getOrderTotal(true),
            $paymentMethod,
            null,
            array(),
            null,
            false,
            $cart->secure_key
        );
        return $this->module->currentOrder;
    }

    public function updateOrder($orderNumber, $orderStatus)
    {
        $history = new OrderHistory();
        $history->id_order = (int)$orderNumber;
        $history->changeIdOrderState(($orderStatus), $orderNumber, true);
    }

    public function setStatus()
    {
        if (!Configuration::get(OrderMangement::WDEE_OS_AWAITING)) {
            $orderState = new OrderState();
            $orderState->name = array();
            foreach (Language::getLanguages() as $language) {
                $orderState->name[$language['id_lang']] = 'Checkout Wirecard Gateway payment awaiting';
            }
            $orderState->send_email = false;
            $orderState->color = 'lightblue';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = false;
            $orderState->invoice = false;
            $orderState->add();
            Configuration::updateValue(
                OrderMangement::WDEE_OS_AWAITING,
                (int)($orderState->id)
            );
        }

        if (!Configuration::get(OrderMangement::WDEE_OS_FRAUD)) {
            $orderState = new OrderState();
            $orderState->name = array();
            foreach (Language::getLanguages() as $language) {
                $orderState->name[$language['id_lang']] = 'Checkout Wirecard Gateway fraud detected';
            }
            $orderState->send_email = false;
            $orderState->color = '#8f0621';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = false;
            $orderState->invoice = false;
            $orderState->module_name =$this->module->name;
            $orderState->add();

            Configuration::updateValue(
                OrderMangement::WDEE_OS_FRAUD,
                (int)($orderState->id)
            );
        }
    }
}
