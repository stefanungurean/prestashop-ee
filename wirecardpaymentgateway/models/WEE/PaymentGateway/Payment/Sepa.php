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
 * @author Wirecard AG
 * @copyright Wirecard AG
 * @license GPLv3
 */

use Wirecard\PaymentSdk\Transaction\SepaTransaction;
use Wirecard\PaymentSdk\Config\SepaConfig;
use Wirecard\PaymentSdk\Entity\Mandate;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;

class WEEPaymentGatewayPaymentSepa extends WEEPaymentGatewayPayment
{
    protected $paymentMethod = 'Sepa';

    /**
     * get default sofort transaction data
     *
     * @since 0.0.3
     *
     * @return SofortTransaction
     */
    protected function getTransaction()
    {
        $mandate = new Mandate('12345678');

        $transaction = new SepaTransaction();
        $transaction->setIban('DE42512308000000060004');
        $transaction->setBic('WIREDEMMXXX');
        $transaction->setMandate($mandate);

        return $transaction;
    }

    /**
     * get default sofort transaction name
     *
     * @since 0.0.3
     *
     * @return string
     */

    public function configMethod()
    {
        $MAID = ConfigurationSettings::getConfigValue($this->paymentMethod, 'maid');
        $key = ConfigurationSettings::getConfigValue($this->paymentMethod, 'secret');
        $creditorid = 'DE98ZZZ09999999999';
        $configsepa= new SepaConfig($MAID, $key);
        $configsepa->setCreditorId($creditorid);

        return $configsepa;
    }

    protected function processResponseSuccess($response)
    {
        if (!($response instanceof FormInteractionResponse)) {
            return false;
        } ?>
        <form id="sepa" method="<?= $response->getMethod(); ?>" action="<?= $response->getUrl(); ?>">
            <?php foreach ($response->getFormFields() as $key => $value) { ?>
                <input type="hidden" name="<?= $key ?>" value="<?= $value ?>">
            <?php } ?>
       </form>
        <script>
            document.getElementById("sepa").submit();
        </script>
        <?php
        exit;
    }

    public function setFormData()
    {
        $this->module->getContext()->smarty->assign(
            array(
                'bic' => Configuration::get(
                    ConfigurationSettings::buildParamName(
                        $this->paymentMethod,
                        TabData::INPUT_NAME_ENABLE_BIC
                    )
                )
            )
        );
    }
}
