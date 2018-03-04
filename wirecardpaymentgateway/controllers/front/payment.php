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

/**
 * @new-payment
 * Add here the new service class for a new payment
 */

class WirecardPaymentGatewayPaymentModuleFrontController extends ModuleFrontController
{

    private $paymentMethod;
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $message = '';

        if(empty($_POST['payment-type'])) {
            $message = $this->l('Payment method not selected');
        }
        $this->paymentMethod = $_POST['payment-type'];
        if (!Configuration::get($this->module->buildParamName($this->paymentMethod, 'enable_method'))) {
            $message = $this->l('Payment method not available');
            $this->context->cookie->eeMessage = $message;
            $params = array(
                'submitReorder' => true,
                'id_order' => (int)$this->module->currentOrder
            );
            Tools::redirect($this->context->link->getPageLink('order', true, $this->context->cart->id_lang, $params));
        }

        require_once _WPC_MODULE_DIR_ . '/service/impl/' . $this->paymentMethod . 'PaymentService.inc';
        $className = ucfirst($this->paymentMethod) . 'PaymentService';
        $paymentService = new $className;

        $paymentService->initiatePayment($this->context, $this->module);


    }





}
