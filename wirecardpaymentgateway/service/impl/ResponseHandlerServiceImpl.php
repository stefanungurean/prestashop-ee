<?php
/**
 * Created by IntelliJ IDEA.
 * User: manuel.rinaldi
 * Date: 3/4/2018
 * Time: 5:20 PM
 */

require _WPC_MODULE_DIR_ . '/service/ResponseHandlerService.php';
require _WPC_MODULE_DIR_ . '/service/traits/ResponseHandlerServiceTrait.php';

class ResponseHandlerServiceImpl implements ResponseHandlerService
{
    use ResponseHandlerServiceTrait;

    public function __constructor() {
        $this->logger = new Logger();
    }


    function handleResponse($response, $context, $module)
    {
        $this->responseWirecardHandler($response, $context, $module);
    }

    function notifyResponse($response, $context, $module)
    {
        // TODO: Implement notifyResponse() method.
    }
}