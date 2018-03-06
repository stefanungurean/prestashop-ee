<?php

use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\IdealBic;


require_once _WPC_MODULE_DIR_ . '/service/PaymentService.inc';
require_once _WPC_MODULE_DIR_ . '/service/traits/PaymentServiceTrait.inc';


require_once _WPC_MODULE_DIR_ . '/models/IdealConfiguration.inc';


/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/25/18
 * Time: 07:17 AM
 */

class IdealPaymentService implements PaymentService
{
    use PaymentServiceTrait;


    function prepareTransaction($params)
    {
        // The Ideal transaction holds all transaction relevant data for the payment process.
        $transaction = new IdealTransaction();
        $transaction->setNotificationUrl($params->getNotificationUrl());
        $transaction->setRedirect($params->getRedirectUrls());
        $transaction->setAmount($params->getAmount());
        if (Configuration::get($this->module->buildParamName($this->paymentMethod, 'basket_send'))) {
            $transaction->setBasket($params->getBasket());
        }
        //for ideal - to be taken from shop
        $accountHolder = new AccountHolder();
        $accountHolder->setLastName('Doe');
        $accountHolder->setFirstName('John');
        $transaction->setBic(IdealBic::INGBNL2A);

        $transaction->setDescriptor('customer-statement');
        return $transaction;
    }

    function getConfiguration()
    {
        $paymentConfig = new IdealConfiguration();
        return $paymentConfig->getConfiguration();
    }
}