<?php

define('_TEST_DIR_', dirname(__FILE__));
define('_PS_MODULE_DIR_', dirname(__FILE__)."/../wirecardpaymentgateway/");
define('_PS_OS_CANCELED_', 1);
define('_PS_OS_ERROR_', 2);

require_once _TEST_DIR_ . '/../wirecardpaymentgateway/vendor/autoload.php';

//stub objects
require _TEST_DIR_ . '/stubs/Validate.php';
require _TEST_DIR_ . '/stubs/Customer.php';
require _TEST_DIR_ . '/stubs/Address.php';

require _TEST_DIR_ . '/stubs/Carrier.php';
require _TEST_DIR_ . '/stubs/Country.php';
require _TEST_DIR_ . '/stubs/ModuleFrontController.php';
require _TEST_DIR_ . '/stubs/HelperForm.php';
require _TEST_DIR_ . '/stubs/Controller.php';
require _TEST_DIR_ . '/stubs/OrderController.php';
require _TEST_DIR_ . '/stubs/Cookie.php';
require _TEST_DIR_ . '/stubs/PaymentOption.php';
require _TEST_DIR_ . '/stubs/Link.php';
require _TEST_DIR_ . '/stubs/Smarty.php';
require _TEST_DIR_ . '/stubs/Context.php';
require _TEST_DIR_ . '/stubs/Tools.php';
require _TEST_DIR_ . '/stubs/Configuration.php';
require _TEST_DIR_ . '/stubs/Media.php';
require _TEST_DIR_ . '/stubs/Module.php';
require _TEST_DIR_ . '/stubs/Order.php';
require _TEST_DIR_ . '/stubs/PaymentModule.php';
require _TEST_DIR_ . '/stubs/CurrencyCore.php';
require _TEST_DIR_ . '/stubs/Language.php';
require _TEST_DIR_ . '/stubs/Cart.php';
require _TEST_DIR_ . '/stubs/OrderState.php';
require _TEST_DIR_ . '/stubs/OrderHistory.php';

require _TEST_DIR_ . '/stubs/PrestaShopLogger.php';
require _TEST_DIR_ . '/../wirecardpaymentgateway/wirecardpaymentgateway.php';
