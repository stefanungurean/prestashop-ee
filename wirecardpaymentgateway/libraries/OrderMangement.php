<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 * @author Wirecard AG
 * @copyright Wirecard AG
 * @license GPLv3
 */

class OrderMangement extends Module
{
    const WDEE_OS_AWAITING = 'WDEE_OS_AWAITING';
    const WDEE_OS_FRAUD = 'WDEE_OS_FRAUD';

    private $module;

    /**
     * initiate the order management
     *
     * @since 0.0.3
     *
     * @param $module
     *
     */
    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     *  add order
     *
     * @since 0.0.3
     *
     * @param $cart
     * @param $paymentMethod
     *
     * @return string
     */
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

    /**
     *  change order status
     *
     * @since 0.0.3
     *
     * @param $orderNumber
     * @param $orderStatus
     * @param $sendEmail
     *
     */
    public function updateOrder($orderNumber, $orderStatus, $sendEmail = false)
    {
        self::$_INSTANCE[$this->module->name] = $this->module->changeModuleNameByOrder($orderNumber);

        $history = new OrderHistory();
        $history->id_order = (int)$orderNumber;
        $history->changeIdOrderState(($orderStatus), $orderNumber, true);
        if ($sendEmail) {
            $history->addWithemail();
        }
    }

    /**
     *  add order status to prestashop
     *
     * @since 0.0.3
     */
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
            $orderState->module_name = $this->module->name;
            $orderState->add();

            Configuration::updateValue(
                OrderMangement::WDEE_OS_FRAUD,
                (int)($orderState->id)
            );
        }
    }
}
