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

require_once dirname(__FILE__) .'/ConfigurationSettings.php';

class TabData
{
    //repetitive data specific labels
    const LABEL_WIRECARD_SERVER_URL = 'Wirecard server url';
    const LABEL_HTTP_PASS = 'Http password';
    const LABEL_HTTP_USER = 'Http user';
    const LABEL_SECRET = 'Secret';
    const LABEL_MAID = 'Maid';
    const LABEL_ENABLE = 'Enable';


    const INPUT_NAME_WIRECARD_SERVER_URL = 'wirecard_server_url';
    const INPUT_NAME_HTTP_PASS = 'http_password';
    const INPUT_NAME_HTTP_USER = 'http_user';
    const INPUT_NAME_SECRET = 'secret';
    const INPUT_NAME_MAID = 'maid';

    const INPUT_NAME_WIRECARD_SERVER_URL_VALUE = 'https://api-test.wirecard.com';
    const INPUT_NAME_HTTP_PASS_VALUE = 'qD2wzQ_hrc!8';
    const INPUT_NAME_HTTP_USER_VALUE = '70000-APITEST-AP';

    private $module;

    /**
     * initiate store data
     *
     * @since 0.0.3
     *
     * @param $module
     *
     */
    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * return paypal payment method data
     *
     * @since 0.0.3
     *
     * @return array
     */
    private function paypal()
    {
        $methodName = __FUNCTION__;
        $MethodName = ucfirst($methodName);

        return array(
            ConfigurationSettings::TAB_TEXT => $MethodName,
            ConfigurationSettings::FIELDS_TEXT => array(
                array(
                    ConfigurationSettings::NAME_TEXT => ConfigurationSettings::INPUT_NAME_ENABLE_METHOD_TEXT,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l(self::LABEL_ENABLE),
                    ConfigurationSettings::VALIDATE_DEFAULT => '0',
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_ON_OFF,
                    ConfigurationSettings::CLASS_NAME => $MethodName,
                    ConfigurationSettings::LOGO_TEXT  => 'paypal.png',
                    ConfigurationSettings::CLASS_METHOD_TEXT => $MethodName
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => self::INPUT_NAME_WIRECARD_SERVER_URL,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l(self::LABEL_WIRECARD_SERVER_URL),
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_NAME_WIRECARD_SERVER_URL_VALUE,
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => self::INPUT_NAME_MAID,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l(self::LABEL_MAID),
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => '9abf05c1-c266-46ae-8eac-7f87ca97af28',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => self::INPUT_NAME_SECRET,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l(self::LABEL_SECRET),
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => self::INPUT_NAME_HTTP_USER ,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l(self::LABEL_HTTP_USER),
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_NAME_HTTP_USER_VALUE,
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => self::INPUT_NAME_HTTP_PASS ,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l(self::LABEL_HTTP_PASS),
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_NAME_HTTP_PASS_VALUE,
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => 'transaction_type',
                    ConfigurationSettings::LABEL_TEXT => $this->module->l('Transaction type'),
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::SELECT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => 'purchase',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    'options' => 'getTransactionTypes'
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => 'descriptor',
                    ConfigurationSettings::LABEL_TEXT => $this->module->l('Send descriptor'),
                    ConfigurationSettings::VALIDATE_DEFAULT => '1',
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_ON_OFF,
                    ConfigurationSettings::VALIDATE_REQUIRED => true
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => 'basket_send',
                    ConfigurationSettings::LABEL_TEXT => $this->module->l('Send basket data'),
                    ConfigurationSettings::VALIDATE_DEFAULT => '0',
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_ON_OFF,
                    ConfigurationSettings::VALIDATE_REQUIRED => true
                ),
                array(
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::LINK_BUTTON,
                    ConfigurationSettings::BUTTON_TEXT_TEXT => $this->module->l('Test paypal configuration'),
                    ConfigurationSettings::ID_TEXT => 'paypalConfig',
                    ConfigurationSettings::METHOD_NAME  => $methodName,
                    ConfigurationSettings::SEND_TEXT => $this->getCheckArray($methodName)
                )
            )
        );
    }

    /**
     * return sofort payment method data
     *
     * @since 0.0.3
     *
     * @return array
     */
    private function sofort()
    {
        $methodName = __FUNCTION__;
        $MethodName = ucfirst($methodName);

        return array(
            ConfigurationSettings::TAB_TEXT => $MethodName,
            ConfigurationSettings::FIELDS_TEXT => array(
                array(
                    ConfigurationSettings::NAME_TEXT => ConfigurationSettings::INPUT_NAME_ENABLE_METHOD_TEXT,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l(self::LABEL_ENABLE),
                    ConfigurationSettings::VALIDATE_DEFAULT => '0',
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_ON_OFF,
                    ConfigurationSettings::CLASS_NAME => $MethodName,
                    ConfigurationSettings::LOGO_TEXT  => 'sofortbanking.png',
                    ConfigurationSettings::CLASS_METHOD_TEXT => $MethodName
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => self::INPUT_NAME_WIRECARD_SERVER_URL,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l(self::LABEL_WIRECARD_SERVER_URL),
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_NAME_WIRECARD_SERVER_URL_VALUE,
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => self::INPUT_NAME_MAID,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l(self::LABEL_MAID),
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => 'c021a23a-49a5-4987-aa39-e8e858d29bad',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => self::INPUT_NAME_SECRET,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l(self::LABEL_SECRET),
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => 'dbc5a498-9a66-43b9-bf1d-a618dd39968',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => self::INPUT_NAME_HTTP_USER ,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l(self::LABEL_HTTP_USER),
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_NAME_HTTP_USER_VALUE,
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => self::INPUT_NAME_HTTP_PASS ,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l(self::LABEL_HTTP_PASS),
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_NAME_HTTP_PASS_VALUE,
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::LINK_BUTTON,
                    ConfigurationSettings::VALIDATE_REQUIRED => false,
                    ConfigurationSettings::BUTTON_TEXT_TEXT => $this->module->l('Test sofort configuration'),
                    ConfigurationSettings::ID_TEXT => 'sofortConfig',
                    ConfigurationSettings::METHOD_NAME => $methodName,
                    ConfigurationSettings::SEND_TEXT => $this->getCheckArray($methodName)
                )
            )
        );
    }

    /**
     * return iDEAL payment method data
     *
     * @since 0.0.3
     *
     * @return array
     */
    private function iDEAL()
    {
        $methodName = __FUNCTION__;
        $MethodName = ucfirst($methodName);

        return array(
            ConfigurationSettings::TAB_TEXT => $MethodName,
            ConfigurationSettings::FIELDS_TEXT => array(
                array(
                    ConfigurationSettings::NAME_TEXT => ConfigurationSettings::INPUT_NAME_ENABLE_METHOD_TEXT,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l(self::LABEL_ENABLE),
                    ConfigurationSettings::VALIDATE_DEFAULT => '0',
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_ON_OFF,
                    ConfigurationSettings::CLASS_NAME => $MethodName,
                    ConfigurationSettings::LOGO_TEXT  => 'ideal.png',
                    ConfigurationSettings::CLASS_METHOD_TEXT => $MethodName
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => self::INPUT_NAME_WIRECARD_SERVER_URL,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l(self::LABEL_WIRECARD_SERVER_URL),
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_NAME_WIRECARD_SERVER_URL_VALUE,
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),

                array(
                    ConfigurationSettings::NAME_TEXT => self::INPUT_NAME_MAID,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l(self::LABEL_MAID),
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => 'b4ca14c0-bb9a-434d-8ce3-65fbff2c2267',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => self::INPUT_NAME_SECRET,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l(self::LABEL_SECRET),
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => self::INPUT_NAME_HTTP_USER ,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l(self::LABEL_HTTP_USER),
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_NAME_HTTP_USER_VALUE,
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => self::INPUT_NAME_HTTP_PASS ,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l(self::LABEL_HTTP_PASS),
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_NAME_HTTP_PASS_VALUE,
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::LINK_BUTTON,
                    ConfigurationSettings::VALIDATE_REQUIRED => false,
                    ConfigurationSettings::BUTTON_TEXT_TEXT => $this->module->l('Test iDEAL configuration'),
                    ConfigurationSettings::ID_TEXT => 'iDEALConfig',
                    ConfigurationSettings::METHOD_NAME => $methodName,
                    ConfigurationSettings::SEND_TEXT => $this->getCheckArray($methodName)
                )
            )
        );
    }

    /**
     * return check payment method data
     *
     * @since 0.0.3
     *
     * @param $methodName
     *
     * @return array
     */
    private function getCheckArray($methodName)
    {
        return array(
            ConfigurationSettings::buildParamName($methodName, self::INPUT_NAME_WIRECARD_SERVER_URL),
            ConfigurationSettings::buildParamName($methodName, self::INPUT_NAME_HTTP_USER),
            ConfigurationSettings::buildParamName($methodName, self::INPUT_NAME_HTTP_PASS)
        );
    }

    /**
     * return configuration data
     *
     * @since 0.0.3
     *
     * @return array
     */
    public function getConfig()
    {
        $configurationArray = array();
        $configurationArray['paypal'] = $this->paypal();
        $configurationArray['sofort'] = $this->sofort();
        $configurationArray['iDEAL'] = $this->iDEAL();

        return $configurationArray;
    }

    /**
     * get transaction types
     *
     * @since 0.0.3
     *
     * @return array
     */
    public function getTransactionTypes()
    {
        return array(
            array('key' => 'authorization', 'value' => $this->module->l('Authorization')),
            array('key' => 'purchase', 'value' => $this->module->l('Purchase'))
        );
    }
}
