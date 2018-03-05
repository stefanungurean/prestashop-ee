<?php
/**
 * Created by IntelliJ IDEA.
 * User: manuel.rinaldi
 * Date: 3/4/2018
 * Time: 5:01 PM
 */

interface ResponseHandlerService
{
    function handleResponse($response, $context, $module);
    function notifyResponse($response, $context, $module);
    function cancelOrder($orderId);

}