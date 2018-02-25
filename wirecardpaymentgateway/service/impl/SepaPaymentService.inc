<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/25/18
 * Time: 07:18 AM
 */

require_once __DIR__ . '/../PaymentService.inc';
require_once __DIR__ . '/../PaymentServiceTrait.inc';

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