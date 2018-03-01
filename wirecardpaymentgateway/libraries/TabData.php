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
    //input labels
    const INPUT_LABEL_ENABLE_METHOD = 'Enable';
    const INPUT_LABEL_WIRECARD_SERVER_URL = 'Wirecard server url';
    const INPUT_LABEL_HTTP_PASS = 'Http password';
    const INPUT_LABEL_HTTP_USER = 'Http user';
    const INPUT_LABEL_SECRET = 'Secret';
    const INPUT_LABEL_MAID = 'Maid';
    const INPUT_LABEL_TRANSACTION_TYPE = 'Transaction type';
    const INPUT_LABEL_DESCRIPTOR = 'Send descriptor';

    //input names
    const INPUT_NAME_ENABLE_METHOD = 'enable_method';
    const INPUT_NAME_WIRECARD_SERVER_URL = 'wirecard_server_url';
    const INPUT_NAME_HTTP_PASS = 'http_password';
    const INPUT_NAME_HTTP_USER = 'http_user';
    const INPUT_NAME_SECRET = 'secret';
    const INPUT_NAME_MAID = 'maid';
    const INPUT_NAME_TRANSACTION_TYPE = 'transaction_type';
    const INPUT_NAME_DESCRIPTOR = 'descriptor';
    const INPUT_NAME_BASKET_SEND = 'basket_send';

    //input default values
    const INPUT_VALUE_ENABLE_METHOD = '0';
    const INPUT_VALUE_WIRECARD_SERVER_URL = 'https://api-test.wirecard.com';
    const INPUT_VALUE_HTTP_PASS = 'qD2wzQ_hrc!8';
    const INPUT_VALUE_HTTP_USER = '70000-APITEST-AP';
    const INPUT_VALUE_TRANSACTION_TYPE = 'purchase';
    const INPUT_VALUE_DESCRIPTOR = '1';
    const INPUT_VALUE_BASKET_SEND = '0';

    const INPUT_TRANSACTION_TYPE_FUNCTION = 'getTransactionTypes';

    private $tabs = array('paypal','sofort','iDEAL');

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
            ConfigurationSettings::TEXT_TAB => $MethodName,
            ConfigurationSettings::TEXT_FIELDS => array(
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_ENABLE_METHOD,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_ENABLE_METHOD),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_ON_OFF,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_VALUE_ENABLE_METHOD,
                    ConfigurationSettings::TEXT_CLASS_NAME => $MethodName,
                    ConfigurationSettings::TEXT_LOGO  => 'paypal.png',
                    ConfigurationSettings::TEXT_METHOD_LABEL => $MethodName
                ),
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_WIRECARD_SERVER_URL,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_WIRECARD_SERVER_URL),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_VALUE_WIRECARD_SERVER_URL,
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_MAID,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_MAID),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => '9abf05c1-c266-46ae-8eac-7f87ca97af28',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_SECRET,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_SECRET),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_HTTP_USER,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_HTTP_USER),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_VALUE_HTTP_USER,
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_HTTP_PASS,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_HTTP_PASS),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_VALUE_HTTP_PASS,
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_TRANSACTION_TYPE,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_TRANSACTION_TYPE),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_SELECT,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_VALUE_TRANSACTION_TYPE,
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    'options' => self::INPUT_TRANSACTION_TYPE_FUNCTION
                ),
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_DESCRIPTOR,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_DESCRIPTOR),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_ON_OFF,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_VALUE_DESCRIPTOR,
                    ConfigurationSettings::VALIDATE_REQUIRED => true
                ),
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_BASKET_SEND,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_NAME_BASKET_SEND),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_ON_OFF,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_VALUE_BASKET_SEND,
                    ConfigurationSettings::VALIDATE_REQUIRED => true
                ),
                $this->buildCheckButon($methodName)
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
            ConfigurationSettings::TEXT_TAB => $MethodName,
            ConfigurationSettings::TEXT_FIELDS => array(
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_ENABLE_METHOD,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_ENABLE_METHOD),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_ON_OFF,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_VALUE_ENABLE_METHOD,
                    ConfigurationSettings::TEXT_CLASS_NAME => $MethodName,
                    ConfigurationSettings::TEXT_LOGO  => 'sofortbanking.png',
                    ConfigurationSettings::TEXT_METHOD_LABEL => $MethodName
                ),
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_WIRECARD_SERVER_URL,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_WIRECARD_SERVER_URL),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_VALUE_WIRECARD_SERVER_URL,
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_MAID,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_MAID),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => 'c021a23a-49a5-4987-aa39-e8e858d29bad',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_SECRET,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_SECRET),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => 'dbc5a498-9a66-43b9-bf1d-a618dd39968',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_HTTP_USER,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_HTTP_USER),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_VALUE_HTTP_USER,
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_HTTP_PASS,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_HTTP_PASS),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_VALUE_HTTP_PASS,
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                $this->buildCheckButon($methodName)
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
            ConfigurationSettings::TEXT_TAB => $MethodName,
            ConfigurationSettings::TEXT_FIELDS => array(
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_ENABLE_METHOD,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_ENABLE_METHOD),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_ON_OFF,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_VALUE_ENABLE_METHOD,
                    ConfigurationSettings::TEXT_CLASS_NAME => $MethodName,
                    ConfigurationSettings::TEXT_LOGO  => 'ideal.png',
                    ConfigurationSettings::TEXT_METHOD_LABEL => $MethodName
                ),
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_WIRECARD_SERVER_URL,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_WIRECARD_SERVER_URL),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_VALUE_WIRECARD_SERVER_URL,
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_MAID,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_MAID),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => 'b4ca14c0-bb9a-434d-8ce3-65fbff2c2267',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_SECRET,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_SECRET),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_HTTP_USER,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_HTTP_USER),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_VALUE_HTTP_USER,
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                array(
                    ConfigurationSettings::TEXT_NAME => self::INPUT_NAME_HTTP_PASS,
                    ConfigurationSettings::TEXT_LABEL => $this->module->l(self::INPUT_LABEL_HTTP_PASS),
                    ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::INPUT_TEXT,
                    ConfigurationSettings::VALIDATE_DEFAULT => self::INPUT_VALUE_HTTP_PASS,
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => ConfigurationSettings::SANITIZE_TRIM
                ),
                $this->buildCheckButon($methodName)
            )
        );
    }

    /**
     * return check button array
     *
     * @since 0.0.3
     *
     * @param $methodName
     *
     * @return array
     */
    private function buildCheckButon($methodName)
    {
        return array(
            ConfigurationSettings::TEXT_TYPE => ConfigurationSettings::LINK_BUTTON,
            ConfigurationSettings::TEXT_BUTTON_TEXT => sprintf(
                $this->module->l(ConfigurationSettings::TEXT_LINK_BUTTON),
                $methodName
            ),
            ConfigurationSettings::TEXT_ID => $methodName.'Config',
            ConfigurationSettings::TEXT_METHOD => $methodName,
            ConfigurationSettings::TEXT_SEND => $this->getCheckArray($methodName)
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
        foreach ($this->tabs as $tab) {
            if (method_exists($this, $tab)) {
                $configurationArray[$tab] = $this->{$tab}();
            }
        }

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
