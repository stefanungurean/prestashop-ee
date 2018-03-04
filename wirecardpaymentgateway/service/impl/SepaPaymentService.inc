<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/25/18
 * Time: 07:18 AM
 */

require_once _WPC_MODULE_DIR_ . '/service/PaymentService.inc';
require_once _WPC_MODULE_DIR_ . '/service/PaymentServiceTrait.inc';

class SepaPaymentService implements PaymentService
{
    use PaymentServiceTrait;

    function prepareTransaction($params)
    {
        // TODO: Implement prepareTransaction() method.
    }

    function getConfiguration($maid, $key)
    {
        // TODO: Implement getConfiguration() method.
    }
}