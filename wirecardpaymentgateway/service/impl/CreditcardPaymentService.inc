<?php

use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
require_once _WPC_MODULE_DIR_ . '/service/PaymentService.inc';
require_once _WPC_MODULE_DIR_ . '/service/traits/PaymentServiceTrait.inc';
/**
 * @new-payment
 * Add the config class for the payment
 */
require_once _WPC_MODULE_DIR_ . '/models/CreditcardConfiguration.inc';
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/25/18
 * Time: 07:17 AM
 */

class CreditCardPaymentService implements PaymentService
{
    use PaymentServiceTrait;

    function prepareTransaction($params)
    {
        $transaction = new CreditCardTransaction();
        $transaction->setAmount($params->getAmount());
        $transaction->setTokenId(Tools::getValue('tokenId'));
        $transaction->setTermUrl($params->getRedirectUrls());
        if (Configuration::get($this->module->buildParamName($this->paymentMethod, 'basket_send'))) {
            $transaction->setBasket($params->getBasket());
        }
        return $transaction;
    }

    function getConfiguration()
    {
        $paymentConfig = new CreditCardConfiguration();
        return $paymentConfig->getConfiguration();
    }
}