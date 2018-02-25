<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/25/18
 * Time: 09:25 PM
 */

require_once 'TransactionConfig.inc';
require_once 'TransactionConfigTrait.inc';

class PaypalConfiguration implements TransactionConfig
{
    use TransactionConfigTrait;

    function getConfiguration()
    {
        $this->getCommonConfiguration();
        $paymentConfig = PaymentMethodConfig(PayPalTransaction::NAME, $this->maid, $this->key);
        $this->config->add($paymentConfig);
    }
}