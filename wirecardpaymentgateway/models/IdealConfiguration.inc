<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/25/18
 * Time: 09:25 PM
 */

use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;

require_once 'TransactionConfig.inc';
require_once 'TransactionConfigTrait.inc';

class IdealConfiguration implements TransactionConfig
{
    use TransactionConfigTrait;

    function getConfiguration()
    {
        $this->paymentMethod = 'ideal';
        $this->getCommonConfiguration();
        $paymentConfig = new PaymentMethodConfig(IdealTransaction::NAME, $this->maid, $this->key);
        $this->config->add($paymentConfig);
        return $this->config;
    }
}