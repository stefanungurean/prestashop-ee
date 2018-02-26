<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 2 (GPLv2) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 *
 * @author    WirecardCEE
 * @copyright WirecardCEE
 * @license   GPLv2
 */

use \Wirecard\PaymentSdk\Transaction\SofortTransaction;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Redirect;

class WirecardPaymentGatewayPaymentSofort extends WirecardPaymentGatewayPayment
{
    protected $paymentMethod = 'Sofort';

    function getTransaction($cart,$orderNumber){
        $currency = new CurrencyCore($cart->id_currency);
        $currencyIsoCode = $currency->iso_code;
        //$orderNumber = $this->module->currentOrder;
        $orderDetail = $this->module->getDisplayName();
        $descriptor = '';
        if (Configuration::get($this->module->getConfigValue($this->paymentMethod, 'descriptor'))) {
            $descriptor = Configuration::get('PS_SHOP_NAME') . $orderNumber;
        }

        $amount = new Amount($cart->getOrderTotal(true), $currencyIsoCode);
        $params = array(
            'id_cart' => (int)$cart->id,
            'id_module' => (int)$this->module->id,
            'key' => $cart->secure_key,
            'order' => $orderNumber
        );
        $redirectUrls = new Redirect(
            $this->module->getContext()->link->getModuleLink($this->module->getName(), 'success', $params, true),
            $this->module->getContext()->link->getModuleLink($this->module->getName(), 'cancel', $params, true)
        );

        $notificationUrl = $this->module->getContext()->link->getModuleLink(
            $this->module->getName(),
            'notify',
            $params,
            true
        );
        $transaction = new SofortTransaction();
        $transaction->setRedirect($redirectUrls);
        $transaction->setNotificationUrl($notificationUrl);
        $transaction->setAmount($amount);
        $transaction->setDescriptor('test');

        $customOrderNumber = new CustomField('customOrderNumber', $orderNumber);
        $customFields = new CustomFieldCollection();
        $customFields->add($customOrderNumber);
        $transaction->setCustomFields($customFields);

        return $transaction;
    }
    function getTransactionName(){
        return SofortTransaction::NAME;
    }
}
