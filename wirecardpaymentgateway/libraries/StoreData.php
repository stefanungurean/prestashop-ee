<?php
/**
 * Created by IntelliJ IDEA.
 * User: eduard.stroia
 * Date: 26.02.2018
 * Time: 19:02
 */

require_once __DIR__.'/ConfigurationSettings.php';

class StoreData
{
    const WIRECARD_SERVER_URL='wirecard_server_url';
    const HTTP_PASS='http_password';
    const HTTP_USER='http_user';

    private $module;
    public function __construct($module)
    {
        $this->module=$module;
    }

    protected function paypal()
    {
        $methodName=__FUNCTION__;
        $MethodName=ucfirst($methodName);
        return array(
            'tab' => $this->module->l($MethodName),
            'fields' => array(
                array(
                    ConfigurationSettings::NAME_TEXT => 'enable_method',
                    ConfigurationSettings::LABEL_TEXT => $this->module->l('Enable'),
                    ConfigurationSettings::VALIDATE_DEFAULT => '0',
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_ON_OFF,
                    ConfigurationSettings::CLASS_NAME => $MethodName,
                    'logo' => 'paypal.png',
                    'labelMethod' => $this->module->l($MethodName)
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => self::WIRECARD_SERVER_URL,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l('URL of Wirecard server'),
                    ConfigurationSettings::TYPE_TEXT => 'text',
                    ConfigurationSettings::VALIDATE_DEFAULT => 'https://api-test.wirecard.com',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => 'trim'
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => 'maid',
                    ConfigurationSettings::LABEL_TEXT => $this->module->l('MAID'),
                    ConfigurationSettings::TYPE_TEXT => 'text',
                    ConfigurationSettings::VALIDATE_DEFAULT => '9abf05c1-c266-46ae-8eac-7f87ca97af28',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => 'trim'
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => 'secret',
                    ConfigurationSettings::LABEL_TEXT => $this->module->l('Secret'),
                    ConfigurationSettings::TYPE_TEXT => 'text',
                    ConfigurationSettings::VALIDATE_DEFAULT => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => 'trim'
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => self::HTTP_USER,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l('HTTP user'),
                    ConfigurationSettings::TYPE_TEXT => 'text',
                    ConfigurationSettings::VALIDATE_DEFAULT => '70000-APITEST-AP',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => 'trim'
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => self::HTTP_PASS,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l('HTTP Password'),
                    ConfigurationSettings::TYPE_TEXT => 'text',
                    ConfigurationSettings::VALIDATE_DEFAULT => 'qD2wzQ_hrc!8',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => 'trim'
                ),
                array(
                    ConfigurationSettings::NAME_TEXT => 'transaction_type',
                    ConfigurationSettings::LABEL_TEXT => $this->module->l('Transaction type'),
                    ConfigurationSettings::TYPE_TEXT => 'select',
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
                    'buttonText' => $this->module->l('Test paypal configuration'),
                    'id' => 'paypalConfig',
                    ConfigurationSettings::METHOD_NAME  => $methodName,
                    'send' => $this->getCheckArray($methodName)
                )
            )
        );
    }

    protected function sofort()
    {
        $methodName=__FUNCTION__;
        $MethodName=ucfirst($methodName);
        return array(
            'tab' => $this->module->l($MethodName),
            'fields' => array(
                array(
                    'name' => 'enable_method',
                    ConfigurationSettings::LABEL_TEXT => $this->module->l('Enable'),
                    ConfigurationSettings::VALIDATE_DEFAULT => '0',
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::INPUT_ON_OFF,
                    ConfigurationSettings::CLASS_NAME => $MethodName,
                    'logo' => 'sofortbanking.png',
                    'labelMethod' => $this->module->l($MethodName)
                ),
                array(
                    'name' => self::WIRECARD_SERVER_URL,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l('URL of Wirecard server'),
                    ConfigurationSettings::TYPE_TEXT => 'text',
                    ConfigurationSettings::VALIDATE_DEFAULT => 'https://api-test.wirecard.com',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => 'trim'
                ),
                array(
                    'name' => 'maid',
                    ConfigurationSettings::LABEL_TEXT => $this->module->l('MAID'),
                    ConfigurationSettings::TYPE_TEXT => 'text',
                    ConfigurationSettings::VALIDATE_DEFAULT => 'c021a23a-49a5-4987-aa39-e8e858d29bad',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => 'trim'
                ),
                array(
                    'name' => 'secret',
                    ConfigurationSettings::LABEL_TEXT => $this->module->l('Secret'),
                    ConfigurationSettings::TYPE_TEXT => 'text',
                    ConfigurationSettings::VALIDATE_DEFAULT => 'dbc5a498-9a66-43b9-bf1d-a618dd39968',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => 'trim'
                ),
                array(
                    'name' => self::HTTP_USER,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l('HTTP user'),
                    ConfigurationSettings::TYPE_TEXT => 'text',
                    ConfigurationSettings::VALIDATE_DEFAULT => '70000-APITEST-AP',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => 'trim'
                ),
                array(
                    'name' => self::HTTP_PASS,
                    ConfigurationSettings::LABEL_TEXT => $this->module->l('HTTP Password'),
                    ConfigurationSettings::TYPE_TEXT => 'text',
                    ConfigurationSettings::VALIDATE_DEFAULT => 'qD2wzQ_hrc!8',
                    ConfigurationSettings::VALIDATE_REQUIRED => true,
                    ConfigurationSettings::VALIDATE_SANITIZE => 'trim'
                ),
                array(
                    ConfigurationSettings::TYPE_TEXT => ConfigurationSettings::LINK_BUTTON,
                    ConfigurationSettings::VALIDATE_REQUIRED => false,
                    'buttonText' => $this->module->l('Test sofort configuration'),
                    'id' => 'sofortConfig',
                    ConfigurationSettings::METHOD_NAME => $methodName,
                    'send' => $this->getCheckArray($methodName)
                )
            )
        );
    }

    public function getCheckArray($methodName)
    {
        return array(
            ConfigurationSettings::buildParamName($methodName, self::WIRECARD_SERVER_URL),
            ConfigurationSettings::buildParamName($methodName, self::HTTP_USER),
            ConfigurationSettings::buildParamName($methodName, self::HTTP_PASS)
        );
    }

    public function config()
    {
        $configurationArray=array();
        $configurationArray['paypal']=$this->paypal();
        $configurationArray['sofort']=$this->sofort();
        return $configurationArray;
    }
}
