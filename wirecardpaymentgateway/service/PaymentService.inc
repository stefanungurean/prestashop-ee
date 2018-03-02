<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/25/18
 * Time: 07:15 AM
 */

interface PaymentService
{
    function initiatePayment($context, $module);
    function prepareTransaction($params);
    function getConfiguration();

    /**
     * Termina di estrarre payment nei vari metodi e lascia il comune nel trait
     */
}