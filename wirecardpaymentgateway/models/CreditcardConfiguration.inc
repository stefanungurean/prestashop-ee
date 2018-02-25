<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/25/18
 * Time: 09:23 PM
 */

use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\Entity\Amount;

require_once 'TransactionConfig.inc';
require_once 'TransactionConfigTrait.inc';

class CreditCardConfiguration implements TransactionConfig
{
    use TransactionConfigTrait;

    function getConfiguration()
    {
        $this->paymentMethod = 'creditcard';
        $this->getCommonConfiguration();
        $threeDMaid = Configuration::get($this->module->buildParamName($this->paymentMethod, '3dmaid'));
        $threeDKey = Configuration::get($this->module->buildParamName($this->paymentMethod, '3dssecret'));
        $sslMax = Configuration::get($this->module->buildParamName($this->paymentMethod, 'SSLmax'));
        $threeDMinLimit = Configuration::get($this->module->buildParamName($this->paymentMethod, '3dmin'));
        $paymentConfig = new CreditCardConfig(
            $this->maid,
            $this->key
        );

        $paymentConfig->addSslMaxLimit(new Amount($sslMax, 'EUR'));
        $paymentConfig->addThreeDMinLimit(new Amount($threeDMinLimit, 'EUR'));

        $paymentConfig->setThreeDCredentials(
            $threeDMaid,
            $threeDKey
        );
        $this->config->add($paymentConfig);
        return $this->config;
    }
}