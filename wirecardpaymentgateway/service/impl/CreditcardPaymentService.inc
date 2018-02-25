<?php

use Wirecard\PaymentSdk\Transaction\CreditCardTransaction;
require_once __DIR__ . '/../PaymentService.inc';
require_once __DIR__ . '/../PaymentServiceTrait.inc';
/**
 * @new-payment
 * Add the config class for the payment
 */
require_once __DIR__ . '/../../models/CreditcardConfiguration.inc';
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/25/18
 * Time: 07:17 AM
 */

class CreditCardPaymentService implements PaymentService
{
    use PaymentServiceTrait;

    protected $paymentMethod;

    function prepareTransaction($params)
    {
        $transaction = new CreditCardTransaction();
        $transaction->setAmount($params->getAmount());
        $transaction->setTokenId($params->getTokenId());
        $transaction->setTermUrl($params->getRedirectUrls());
        if (Configuration::get($this->module->buildParamName($this->paymentMethod, 'basket_send'))) {
            $transaction->setBasket($params->getBasket());
        }
        return $transaction;
    }

    function getConfiguration($maid, $key)
    {
        $paymentConfig = new CreditCardConfiguration();
        return $paymentConfig->getConfiguration();
    }
}