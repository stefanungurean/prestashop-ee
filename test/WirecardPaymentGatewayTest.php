<?php
/**
 * Default test case.
 */

define('_PS_MODULE_DIR_', '');
define('_PS_VERSION_', '1.7.1');

class WirecardPaymentGatewayTest extends \PHPUnit_Framework_TestCase
{


    /**
     * A single example test.
     */
    public function testHookPaymentOptions()
    {
        $wirecardModule = new WirecardPaymentGateway();
        $paymentsConfig = $wirecardModule->getTotalPaymentMethods();
        $payments = $wirecardModule->hookPaymentOptions(null);

        // Replace this with some actual testing code.
        $this->assertEquals($paymentsConfig, count($payments));
    }
}
