<?php

/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/25/18
 * Time: 07:18 AM
 */
require_once _WPC_MODULE_DIR_ . '/service/PaymentService.inc';
require_once _WPC_MODULE_DIR_ . '/service/PaymentServiceTrait.inc';
require_once _WPC_MODULE_DIR_ . '/models/SepaConfiguration.php';

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Mandate;
use Wirecard\PaymentSdk\TransactionService;
use Wirecard\PaymentSdk\Entity\Amount;


class SepaPaymentService implements PaymentService {


    use PaymentServiceTrait;

    protected $paymentMethod;

    function prepareTransaction($params)
    {
        // TODO: Implement prepareTransaction() method.
        $transaction = new Wirecard\PaymentSdk\Transaction\SepaTransaction();
        $mandate = new Mandate(Configuration::get($this->module->buildParamName($this->paymentMethod, 'maid')));
        $accountHolder = new AccountHolder();
        $accountHolder->setLastName(Tools::getValue('account-holder-lastname'));
        $accountHolder->setFirstName(Tools::getValue('account-holder-firstname'));
        $transaction->setBic(Tools::getValue('bic'));
        $transaction->setIban(Tools::getValue('account'));
        $transaction->setMandate($mandate);
        $transaction->setAccountHolder($accountHolder);
        $transaction->setAmount($params->getAmount());
        if (Configuration::get($this->module->buildParamName($this->paymentMethod, 'basket_send')))
            {
            $transaction->setBasket($params->getBasket());
            }
        return $transaction;
    }

    function getConfiguration()
    {
        // TODO: Implement getConfiguration() method.
        $paymentConfig = new SepaConfiguration();
        return $paymentConfig->getConfiguration();
    }


}
