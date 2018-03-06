<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */
require_once(_WPC_MODULE_DIR_.'/service/impl/ResponseHandlerServiceImpl.php');

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\TransactionService;

class WirecardPaymentGatewaySuccessModuleFrontController extends ModuleFrontController
{


    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $responseHandler = new ResponseHandlerServiceImpl();
        //var_dump($this->module);
        //$baseUrl = Configuration::get($this->module->buildParamName($this->paymentMethod, 'wirecard_server_url'));
       // var_dump($baseUrl);
        $config = new Config('https://api-test.wirecard.com', '70000-APITEST-AP' , 'qD2wzQ_hrc!8', 'EUR' );
        $config->setPublicKey(file_get_contents(_WPC_MODULE_DIR_ . '/certificates/api-test.wirecard.com.crt'));


        $service = new TransactionService($config);

        var_dump(get_class_methods($service));
        die();
        // la get da eroare ca n-are payment method;
        // compar post cu get;

        if(empty($_POST)){
            $response = $service->handleResponse($_GET);
        }else{
            $response = $service->handleResponse($_POST);
        }

        $responseHandler->handleResponse($response, $this->context, $this->module );

    }


}
