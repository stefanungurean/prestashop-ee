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

use Wirecard\PaymentSdk\Transaction\PayPalTransaction;

class WEEPaymentGatewayPaymentPaypal extends WEEPaymentGatewayPayment
{
    protected $paymentMethod = 'Paypal';

    /**
     * get default paypal transaction data
     *
     * @since 0.0.3
     *
     * @return PayPalTransaction
     */
    protected function getTransaction()
    {
        $orderDetail = $this->module->getDisplayName();
        $descriptor = '';
        if (Configuration::get(ConfigurationSettings::getConfigValue($this->paymentMethod, TabData::INPUT_NAME_DESCRIPTOR))) {
            $descriptor = Configuration::get('PS_SHOP_NAME') . $this->orderNumber;
        }

        $transaction = new PayPalTransaction();
        if (Configuration::get(ConfigurationSettings::getConfigValue($this->paymentMethod, TabData::INPUT_NAME_BASKET_SEND))) {
            $transaction->setBasket($this->getBasket($this->cart));
        }
        $transaction->setOrderDetail($orderDetail);
        $transaction->setEntryMode('ecommerce');
        $transaction->setDescriptor($descriptor);

        return $transaction;
    }

    /**
     * get default paypal transaction name
     *
     * @since 0.0.3
     *
     * @return string
     */
    protected function getTransactionName()
    {
        return PayPalTransaction::NAME;
    }
}