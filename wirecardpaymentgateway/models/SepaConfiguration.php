<?php

use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\Entity\Amount;

require_once 'TransactionConfig.inc';
require_once 'TransactionConfigTrait.inc';

class SepaConfiguration implements TransactionConfig {

    use TransactionConfigTrait;

    function getConfiguration()
    {
        $this->paymentMethod = 'sepa';
        $this->getCommonConfiguration();
        
        $this->maid = Configuration::get($this->module->buildParamName($this->paymentMethod, 'maid'));
        $this->key = Configuration::get($this->module->buildParamName($this->paymentMethod, 'secret'));
        $Creditorid = Configuration::get($this->module->buildParamName($this->paymentMethod, 'Creditorid'));
        $paymentConfig = new Wirecard\PaymentSdk\Config\SepaConfig(
                $this->maid, $this->key
        );
        $paymentConfig->setCreditorId($Creditorid);
        $this->config->add($paymentConfig);
        return $this->config;
    }

}
