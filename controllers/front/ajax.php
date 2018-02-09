<?php
require __DIR__.'/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\TransactionService;

class WirecardPaymentGatewayAjaxModuleFrontController extends ModuleFrontController
{

    private $config;
    /**
     * @see FrontController::postProcess()
     */

    public function postProcess()
    {
        switch (Tools::getValue('action')) {

            case 'TestConfig':

                $method = Tools::getValue('method');
                $status = 'ok';
                $message = $this->l('The merchant configuration was successfuly tested.');

                $baseUrl = Configuration::get($this->module->buildParamName($method, 'wirecard_server_url'));
                $httpUser = Configuration::get($this->module->buildParamName($method, 'http_user'));
                $httpPass = Configuration::get($this->module->buildParamName($method, 'http_password'));

                $config = new Config($baseUrl, $httpUser, $httpPass, "RON");
                $transactionService = new TransactionService($config, $this->logger);

                if (!$transactionService->checkCredentials()) {
                    $status = 'error';
                    $message = $this->l('The merchant configuration is incorrect');
                }

                die(Tools::jsonEncode(
                    array(
                        'status' => htmlspecialchars($status),
                        'message' => htmlspecialchars($message)
                    )
                ));
                break;
        }
    }
}
