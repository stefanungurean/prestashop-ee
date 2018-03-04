<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/20/18
 * Time: 12:10 AM
 */

include(dirname(__FILE__) . '/../../vendor/autoload.php');
/**
 * @new-payment
 * Add the config class for the payment
 */
require_once _WPC_MODULE_DIR_ . '/service/PaymentForm.inc';
require_once _WPC_MODULE_DIR_ . '/models/CreditcardConfiguration.inc';
use Wirecard\PaymentSdk\TransactionService;


class CreditCardForm implements PaymentForm
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

        $configuration = new CreditCardConfiguration();
        $this->config = $configuration->getConfiguration();
    }

    public function generateForm() {
        $this->configuration();
        $transactionService = new TransactionService($this->config);
        $req = $transactionService->getDataForCreditCardUi();

        $this->context->smarty->assign([
            'action' => $this->context->link->getModuleLink($this->name, 'payment', array(), true),
            'requestData' => json_decode($req),
        ]);
        return $this->context->smarty->fetch('module:wirecardpaymentgateway/views/templates/front/credit-card.tpl');
    }

}