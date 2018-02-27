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
                    'name' => 'enable_method',
                    'label' => $this->module->l('Enable'),
                    'default' => '0',
                    'type' => ConfigurationSettings::INPUT_ON_OFF,
                    ConfigurationSettings::CLASS_NAME => $MethodName,
                    'logo' => 'paypal.png',
                    'labelMethod' => $this->module->l($MethodName),

                ),
                array(
                    'name' => self::WIRECARD_SERVER_URL,
                    'label' => $this->module->l('URL of Wirecard server'),
                    'type' => 'text',
                    'default' => 'https://api-test.wirecard.com',
                    'required' => true,
                    'sanitize' => 'trim'
                ),
                array(
                    'name' => 'maid',
                    'label' => $this->module->l('MAID'),
                    'type' => 'text',
                    'default' => '9abf05c1-c266-46ae-8eac-7f87ca97af28',
                    'required' => true,
                    'sanitize' => 'trim'
                ),
                array(
                    'name' => 'secret',
                    'label' => $this->module->l('Secret'),
                    'type' => 'text',
                    'default' => 'dbc5a498-9a66-43b9-bf1d-a618dd399684',
                    'required' => true,
                    'sanitize' => 'trim'
                ),
                array(
                    'name' => self::HTTP_USER,
                    'label' => $this->module->l('HTTP user'),
                    'type' => 'text',
                    'default' => '70000-APITEST-AP',
                    'required' => true,
                    'sanitize' => 'trim'
                ),
                array(
                    'name' => self::HTTP_PASS,
                    'label' => $this->module->l('HTTP Password'),
                    'type' => 'text',
                    'default' => 'qD2wzQ_hrc!8',
                    'required' => true,
                    'sanitize' => 'trim'
                ),
                array(
                    'name' => 'transaction_type',
                    'label' => $this->module->l('Transaction type'),
                    'type' => 'select',
                    'default' => 'purchase',
                    'required' => true,
                    'options' => 'getTransactionTypes'
                ),
                array(
                    'name' => 'descriptor',
                    'label' => $this->module->l('Send descriptor'),
                    'default' => '1',
                    'type' => ConfigurationSettings::INPUT_ON_OFF,
                    'required' => true
                ),
                array(
                    'name' => 'basket_send',
                    'label' => $this->module->l('Send basket data'),
                    'default' => '0',
                    'type' => ConfigurationSettings::INPUT_ON_OFF,
                    'required' => true
                ),
                array(
                    'type' => ConfigurationSettings::LINK_BUTTON,
                    'required' => false,
                    'buttonText' => $this->module->l('Test paypal configuration'),
                    'id' => 'paypalConfig',
                    ConfigurationSettings::METHOD_NAME  => $methodName,
                    'name' => $methodName,
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
                    'label' => $this->module->l('Enable'),
                    'default' => '0',
                    'type' => ConfigurationSettings::INPUT_ON_OFF,
                    ConfigurationSettings::CLASS_NAME => $MethodName,
                    'logo' => 'sofortbanking.png',
                    'labelMethod' => $this->module->l($MethodName),

                ),
                array(
                    'name' => self::WIRECARD_SERVER_URL,
                    'label' => $this->module->l('URL of Wirecard server'),
                    'type' => 'text',
                    'default' => 'https://api-test.wirecard.com',
                    'required' => true,
                    'sanitize' => 'trim'
                ),
                array(
                    'name' => 'maid',
                    'label' => $this->module->l('MAID'),
                    'type' => 'text',
                    'default' => 'c021a23a-49a5-4987-aa39-e8e858d29bad',
                    'required' => true,
                    'sanitize' => 'trim'
                ),
                array(
                    'name' => 'secret',
                    'label' => $this->module->l('Secret'),
                    'type' => 'text',
                    'default' => 'dbc5a498-9a66-43b9-bf1d-a618dd39968',
                    'required' => true,
                    'sanitize' => 'trim'
                ),
                array(
                    'name' => self::HTTP_USER,
                    'label' => $this->module->l('HTTP user'),
                    'type' => 'text',
                    'default' => '70000-APITEST-AP',
                    'required' => true,
                    'sanitize' => 'trim'
                ),
                array(
                    'name' => self::HTTP_PASS,
                    'label' => $this->module->l('HTTP Password'),
                    'type' => 'text',
                    'default' => 'qD2wzQ_hrc!8',
                    'required' => true,
                    'sanitize' => 'trim'
                ),
                array(
                    'type' => ConfigurationSettings::LINK_BUTTON,
                    'required' => false,
                    'buttonText' => $this->module->l('Test sofort configuration'),
                    'id' => 'sofortConfig',
                    ConfigurationSettings::METHOD_NAME => $methodName,
                    'name' => $methodName,
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
