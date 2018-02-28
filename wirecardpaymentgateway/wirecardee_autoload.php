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

spl_autoload_register('wirecardEEAutoload');

/**
 * include class page
 *
 * @since 0.0.3
 *
 * @param $class
 *
 */
function wirecardEEAutoload($class)
{
    $namespaces = array( 'Wirecard');
    $namespace = null;
    $modelNamespace = 'WirecardPaymentGateway';
    $paymentNamespace = 'WirecardPaymentGatewayPayment';

    foreach ($namespaces as $ns) {
        if (strncmp($ns, $class, Tools::strlen($ns)) !== 0) {
            continue;
        } else {
            $namespace = $ns;
            break;
        }
    }

    if ($namespace === null) {
        return;
    }

    if (strcmp($class, $modelNamespace) > 0) {
        $classWithUnderscore = 'Wirecard_PaymentGateway_';
        if ((strcmp($paymentNamespace, Tools::substr($class, Tools::strlen($paymentNamespace))) >= 0)
            && ((Tools::substr($class, Tools::strlen($paymentNamespace))) != '')
        ) {
            $classWithUnderscore .= 'Payment_' . Tools::substr($class, Tools::strlen($paymentNamespace));
        } else {
            $classWithUnderscore .= Tools::substr($class, Tools::strlen($modelNamespace));
        }
        $class = $classWithUnderscore;
    }

    $file = str_replace(array('\\', '_'), '/', $class) . '.php';

    require_once $file;
}
