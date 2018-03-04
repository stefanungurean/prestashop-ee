<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/25/18
 * Time: 07:20 AM
 * 
 */

require _WPC_MODULE_DIR_ . '/vendor/autoload.php';
require_once _WPC_MODULE_DIR_ . '/service/PaymentForm.inc';
require_once _WPC_MODULE_DIR_ . '/models/SepaConfiguration.php';
use Wirecard\PaymentSdk\TransactionService;


class SepaForm implements PaymentForm
{
    private $context;
    private $module;
    private $config;
    private $name;

    function __construct() {
        $this->context = Context::getContext();
        $this->module = Module::getInstanceByName("wirecardpaymentgateway");
        $this->name = $this->module->name;
    }
    
    private function configuration() {
        $configuration = new SepaConfiguration();
        $this->config = $configuration->getConfiguration();
    }

    public function generateForm() {
        $this->configuration();
        $transactionService = new TransactionService($this->config);
//        $req = $transactionService->getDataForCreditCardUi();       
                $this->context->smarty->assign([
            'action' => $this->context->link->getModuleLink($this->name, 'payment', array(), true),
//            'requestData' => json_decode($req),
        ]);
        return $this->context->smarty->fetch('module:wirecardpaymentgateway/views/templates/front/sepa-payment.tpl');
    }
}