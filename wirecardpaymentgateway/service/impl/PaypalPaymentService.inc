<?php

use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
require_once __DIR__ . '/../PaymentService.inc';
require_once __DIR__ . '/../PaymentServiceTrait.inc';
require_once __DIR__ . '/../../models/PaypalConfiguration.inc';
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/25/18
 * Time: 07:17 AM
 */

class PaypalPaymentService implements PaymentService
{
    use PaymentServiceTrait;


    function prepareTransaction($params)
    {
        // The PayPal transaction holds all transaction relevant data for the payment process.
        $transaction = new PayPalTransaction();
        $transaction->setNotificationUrl($params->getNotificationUrl());
        $transaction->setRedirect($params->getRedirectUrls());
        $transaction->setAmount($params->getAmount());
        if (Configuration::get($this->module->buildParamName($this->paymentMethod, 'basket_send'))) {
            $transaction->setBasket($params->getBasket());
        }
        $transaction->setOrderNumber($params->getOrderNumber());
        $transaction->setOrderDetail($params->getOrderDetail());
        $transaction->setDescriptor($params->getDescriptor());
        $transaction->setEntryMode('ecommerce');
        return $transaction;
    }

    function getConfiguration($maid, $key)
    {
        $paymentConfig = new PaypalConfiguration();
        return $paymentConfig->getConfiguration();
    }
}