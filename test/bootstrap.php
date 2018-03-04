<?php

define('_TEST_DIR_'. dirname(__FILE__));
require_once _PS_MODULE_DIR_ . '/../wirecardpaymentgateway/vendor/autoload.php';

//stub objects
require _TEST_DIR_ . '/stubs/ModuleFrontController.php';
require _TEST_DIR_ . '/stubs/PaymentOption.php';
require _TEST_DIR_ . '/stubs/Link.php';
require _TEST_DIR_ . '/stubs/Smarty.php';
require _TEST_DIR_ . '/stubs/Context.php';
require _TEST_DIR_ . '/stubs/PaymentModule.php';
require _TEST_DIR_ . '/stubs/Tools.php';
require _TEST_DIR_ . '/stubs/Configuration.php';
require _TEST_DIR_ . '/stubs/Media.php';
require _TEST_DIR_ . '/stubs/Module.php';
require _TEST_DIR_ . '/stubs/CurrencyCore.php';
require _PS_MODULE_DIR_ . '/wirecardpaymentgateway.php';
