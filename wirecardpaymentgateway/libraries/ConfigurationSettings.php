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

require_once dirname(__FILE__) .'/OrderMangement.php';
require_once dirname(__FILE__) .'/TabData.php';

class ConfigurationSettings
{
    private $module;
    private static $config;
    private static $tabData;


    //inputs names
    const LINK_BUTTON = 'linkbutton';
    const INPUT_ON_OFF = 'onoff';
    const SUBMIT_BUTTON = 'btnSubmit';
    const INPUT_TEXT = 'text';
    const INPUT_SELECT = 'select';

    //validation names
    const VALIDATE_REQUIRED = 'required';
    const VALIDATE_SANITIZE = 'sanitize';
    const VALIDATE_MAX_CHAR = 'maxchar';
    const VALIDATE_DEFAULT = 'default';
    const SANITIZE_TRIM = 'trim';

    //labels names
    const TEXT_VALUE = 'value';
    const TEXT_LABEL = 'label';
    const TEXT_PARAM = 'param_name';
    const TEXT_FIELDS = 'fields';
    const TEXT_MULTIPLE = 'multiple';
    const TEXT_CLASS = 'class';
    const TEXT_OPTION = 'options';
    const TEXT_GROUP = 'group';
    const TEXT_NAME = 'name';
    const TEXT_TYPE = 'type';
    const TEXT_LOGO = 'logo';
    const TEXT_METHOD = "method";
    const TEXT_METHOD_LABEL = 'labelMethod';
    const TEXT_CLASS_NAME = "className";
    const TEXT_IS_FORM = "is_form";

    const TEXT_BUTTON_TEXT = 'buttonText';
    const TEXT_ID = 'id';
    const TEXT_SEND = 'send';
    const TEXT_LINK_BUTTON = 'Test %s configuration';

    const TEXT_TAB = 'tab';
    /**
     * initiate the configuration settings
     *
     * @since 0.0.3
     *
     * @param $module
     *
     */
    public function __construct($module)
    {
        self::$tabData = new TabData($module);
        self::$config =  self::$tabData->getConfig();
        $this->module = $module;

        ini_set(
            'include_path',
            ini_get('include_path')
            . PATH_SEPARATOR . realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR.'..' .DIRECTORY_SEPARATOR. 'vendor'
            . PATH_SEPARATOR . realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR.'..' .DIRECTORY_SEPARATOR. 'models'
        );
        require_once realpath(dirname(__FILE__)).
            DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'wirecardee_autoload.php';
    }

    /**
     * process form element
     *
     * @since 0.0.3
     *
     * @param $f
     * @param $groupKey
     *
     * @return array
     */
    private function processInput($f, $groupKey)
    {
        $configGroup = $groupKey;
        $label = "";

        if (isset($f[self::TEXT_GROUP])) {
            $configGroup = $f[self::TEXT_GROUP];
        }
        if (isset($f[self::TEXT_CLASS])) {
            $configGroup = 'pt';
        }

        if (isset($f[self::TEXT_LABEL])) {
            $label = $f[self::TEXT_LABEL];
        }

        $name = "";
        if (isset($f[self::TEXT_NAME])) {
            $name = self::buildParamName($configGroup, $f[self::TEXT_NAME]);
        }

        $elem = array(
            self::TEXT_NAME => $name,
            self::TEXT_LABEL => $label,
            self::TEXT_TAB => $groupKey,
            self::TEXT_TYPE => $f[self::TEXT_TYPE],
            self::VALIDATE_REQUIRED => isset($f[self::VALIDATE_REQUIRED ]) && $f[self::VALIDATE_REQUIRED ]
        );

        if (isset($f['cssclass'])) {
            $elem[self::TEXT_CLASS] = $f['cssclass'];
        }

        $elem = $this->processInputDoc($f, $elem);

        if (isset($f['docref'])) {
            $desc = '';
            if (isset($elem['desc'])) {
                $desc = $elem['desc']. ' ';
            }
            $elem['desc'] = $desc;
            $elem['desc'] .= sprintf(
                '<a target="_blank" href="%s">%s <i class="icon-external-link"></i></a>',
                $f['docref'],
                $this->module->l('More information')
            );
        }

        return $this->processInputType($f, $elem);
    }

    /**
     * process  element doc attribute
     *
     * @since 0.0.3
     *
     * @param $f
     * @param $elem
     *
     * @return array
     */
    private function processInputDoc($f, $elem)
    {
        if (isset($f['doc'])) {
            if (is_array($f['doc'])) {
                $elem['desc'] = '';
                foreach ($f['doc'] as $d) {
                    if (Tools::strlen($elem['desc'])) {
                        $elem['desc'] .= '<br/>';
                    }
                    $elem['desc'] .= $d;
                }
            } else {
                $elem['desc'] = $f['doc'];
            }
        }

        return $elem;
    }

    /**
     * process element by type
     *
     * @since 0.0.3
     *
     * @param $f
     * @param $elem
     *
     * @return array
     */
    private function processInputType($f, $elem)
    {
        $radio_type = 'switch';
        $radio_options = array(
            array(
                self::TEXT_ID => 'active_on',
                self::TEXT_VALUE => 1,
                self::TEXT_LABEL => $this->module->l('Enabled')
            ),
            array(
                self::TEXT_ID => 'active_off',
                self::TEXT_VALUE => 0,
                self::TEXT_LABEL => $this->module->l('Disabled')
            )
        );

        switch ($f[self::TEXT_TYPE]) {
            case self::LINK_BUTTON:
                $elem[self::TEXT_BUTTON_TEXT] = $f[self::TEXT_BUTTON_TEXT];
                $elem[self::TEXT_ID] = $f[self::TEXT_ID];
                $elem[self::TEXT_METHOD] = $f[self::TEXT_METHOD];
                $elem[self::TEXT_SEND] = $f[self::TEXT_SEND];
                break;
            case self::INPUT_ON_OFF:
                $elem[self::TEXT_TYPE] = $radio_type;
                $elem[self::TEXT_CLASS] = 't';
                $elem['is_bool'] = true;
                $elem['values'] = $radio_options;
                break;
            case self::INPUT_TEXT:
                if (!isset($elem[self::TEXT_CLASS])) {
                    $elem[self::TEXT_CLASS] = 'fixed-width-xl';
                }
                if (isset($f[self::VALIDATE_MAX_CHAR])) {
                    $elem['maxlength'] = $elem[self::VALIDATE_MAX_CHAR] = $f[self::VALIDATE_MAX_CHAR];
                }
                break;
            case self::INPUT_SELECT:
                $elem = $this->processInputTypeSelect($f, $elem);
                break;
            default:
                break;
        }

        return $elem;
    }

    /**
     * process drop down element
     *
     * @since 0.0.3
     *
     * @param $f
     * @param $elem
     *
     * @return array
     */
    private function processInputTypeSelect($f, $elem)
    {
        if (isset($f[self::TEXT_MULTIPLE])) {
            $elem[self::TEXT_MULTIPLE] = $f[self::TEXT_MULTIPLE];
        }
        if (isset($f['size'])) {
            $elem['size'] = $f['size'];
        }
        if (isset($f[self::TEXT_OPTION])) {
            $optfunc = $f[self::TEXT_OPTION];
            $options = array();
            if (is_array($optfunc)) {
                $options = $optfunc;
            }
            if (method_exists(self::$tabData, $optfunc)) {
                $options = self::$tabData->$optfunc();
            }

            $elem[self::TEXT_OPTION] = array(
                'query' => $options,
                self::TEXT_ID => 'key',
                self::TEXT_NAME => self::TEXT_VALUE
            );
        }

        return $elem;
    }

    /**
     * return rendered form
     *
     * @since 0.0.3
     *
     * @return string
     */
    public function renderForm()
    {
        $input_fields = array();
        $tabs = array();

        foreach (self::getConfig() as $groupKey => $group) {
            $tabs[$groupKey] = $group[self::TEXT_TAB];
            foreach ($group[self::TEXT_FIELDS] as $f) {
                $elem = $this->processInput($f, $groupKey);
                $input_fields[] = $elem;
            }
        }

        $fields_form_settings = array(
            'form' => array(
                'tabs' => $tabs,
                'legend' => array(
                    'title' => $this->module->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => $input_fields,
                'submit' => array(
                    'title' => $this->module->l('Save')
                )
            )
        );

        return $this->module->helperRender($fields_form_settings, $this->getConfigFieldsValues());
    }

    /**
     * build prestashop internal parameter name
     *
     * @since 0.0.2
     *
     * @param $group
     * @param $name
     *
     * @return string
     */
    public static function buildParamName($group, $name)
    {
        return sprintf(
            'WDEE_%s_%s',
            Tools::strtoupper($group),
            Tools::strtoupper($name)
        );
    }

    /**
     * return saved config parameter values
     *
     * @since 0.0.3
     *
     * @return array
     */
    private function getConfigFieldsValues()
    {
        $values = array();
        foreach ($this->getAllConfigurationParameters() as $parameter) {
            $val = Configuration::get($parameter[self::TEXT_PARAM]);
            if (isset($parameter[self::TEXT_MULTIPLE]) && $parameter[self::TEXT_MULTIPLE]) {
                if (!is_array($val)) {
                    $val = Tools::strlen($val) ? Tools::jsonDecode($val) : array();
                }

                $x = array();
                foreach ($val as $v) {
                    $x[$v] = $v;
                }
                $pname = $parameter[self::TEXT_PARAM] . '[]';
                $values[$pname] = $x;
            } else {
                $values[$parameter[self::TEXT_PARAM]] = $val;
            }
        }

        return $values;
    }

    /**
     * return all configuration parameters
     *
     * @since 0.0.3
     *
     * @return array
     */
    private function getAllConfigurationParameters()
    {
        $params = array();
        foreach (self::getConfig() as $groupKey => $group) {
            foreach ($group[self::TEXT_FIELDS] as $f) {
                $configGroup = isset($f[self::TEXT_GROUP]) ? $f[self::TEXT_GROUP] : $groupKey;

                if (isset($f[self::TEXT_CLASS])) {
                    $configGroup = 'pt';
                }

                if (isset($f[self::TEXT_NAME])) {
                    $f[self::TEXT_PARAM] = $this->buildParamName(
                        $configGroup,
                        $f[self::TEXT_NAME]
                    );
                    $params[] = $f;
                }
            }
        }

        return $params;
    }

    /**
     * validate post parameters
     *
     * @since 0.0.3
     *
     * @param $parameter
     *
     */
    public function postValidation()
    {
        if (Tools::isSubmit(SELF::SUBMIT_BUTTON)) {
            foreach ($this->getAllConfigurationParameters() as $parameter) {
                if (!$this->validateValue($parameter)) {
                    continue;
                }
            }
        }
    }

    /**
     * validate item value
     *
     * @since 0.0.3
     *
     * @param $parameter
     *
     * @return boolean
     */
    private function validateValue($parameter)
    {
        $val = Tools::getValue($parameter[self::TEXT_PARAM]);

        if (isset($parameter[self::VALIDATE_SANITIZE])&& $parameter[self::VALIDATE_SANITIZE] == SELF::SANITIZE_TRIM) {
            $val = trim($val);
        }

        if (isset($parameter[self::VALIDATE_REQUIRED ]) &&
            $parameter[self::VALIDATE_REQUIRED ] &&
            !Tools::strlen($val)) {
            $this->module->postErrors[] = $parameter[self::TEXT_VALUE] . ' ' . $this->module->l('is required');
        }

        if (!isset($parameter['validator'])) {
            return false;
        }

        if ($parameter['validator'] == 'numeric' && Tools::strlen($val) && !is_numeric($val)) {
            $this->module->postErrors[] = $parameter[self::TEXT_VALUE] . ' ' . $this->module->l(' must be a number');
        }

        return true;
    }

    /**
     * process form post
     *
     * @since 0.0.3
     *
     */
    public function postProcess()
    {
        if (Tools::isSubmit(self::SUBMIT_BUTTON)) {
            foreach ($this->getAllConfigurationParameters() as $parameter) {
                $val = Tools::getValue($parameter[self::TEXT_PARAM]);

                if (isset($parameter[self::VALIDATE_SANITIZE]) &&
                    $parameter[self::VALIDATE_SANITIZE] == SELF::SANITIZE_TRIM) {
                    $val = trim($val);
                }

                if (is_array($val)) {
                    $val = Tools::jsonEncode($val);
                }
                Configuration::updateValue($parameter[self::TEXT_PARAM], $val);
            }
        }

        return $this->module->displayConfirmation($this->module->l('Settings updated'));
    }

    /**
     * set item default values
     *
     * @since 0.0.3
     *     *
     * @return boolean
     */
    public static function setDefaults()
    {
        foreach (self::getConfig() as $groupKey => $group) {
            foreach ($group[self::TEXT_FIELDS] as $f) {
                if (array_key_exists(self::VALIDATE_DEFAULT, $f) && !self::setDefaultValue($f, $groupKey)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * set item default value
     *
     * @since 0.0.3
     *
     * @param $f
     * @param $groupKey
     *
     * @return boolean
     */
    private static function setDefaultValue($f, $groupKey)
    {
        $configGroup = isset($f[self::TEXT_GROUP]) ? $f[self::TEXT_GROUP] : $groupKey;
        if (isset($f[self::TEXT_CLASS])) {
            $configGroup = 'pt';
        }

        $p = self::buildParamName($configGroup, $f[self::TEXT_NAME]);
        $defVal = $f[self::VALIDATE_DEFAULT];
        if (is_array($defVal)) {
            $defVal = Tools::jsonEncode($defVal);
        }

        if (!Configuration::updateValue($p, $defVal)) {
            return false;
        }

        return true;
    }

    /**
     * return payment instances
     *
     * @since 0.0.3
     *
     * @param null $paymentType
     *
     * @return array WirecardPaymentGatewayPayment
     */
    public function getPaymentTypes($paymentType = null)
    {
        $types = array();
        foreach (self::getConfig() as $group) {
            foreach ($group[self::TEXT_FIELDS] as $f) {
                if (array_key_exists(self::TEXT_CLASS_NAME, $f)) {
                    if ($paymentType !== null &&
                        (!isset($f[self::TEXT_CLASS_NAME]) || $f[self::TEXT_CLASS_NAME] != $paymentType)) {
                        continue;
                    }
                    $className = 'WEEPaymentGatewayPayment' . $f[self::TEXT_CLASS_NAME];
                    $f[self::TEXT_GROUP] = 'pt';
                    $pt = new $className($this->module, $f);
                    $types[] = $pt;
                }
            }
        }

        return $types;
    }

    /**
     * get payment instance
     *
     * @since 0.0.3
     *
     * @param $paymentType
     *
     * @return WirecardPaymentGatewayPayment |null
     */
    public function getPaymentType($paymentType)
    {
        $found = $this->getPaymentTypes($paymentType);
        if (count($found) != 1) {
            return null;
        }

        return $found[0];
    }

    /**
     * get config value
     *
     * @since 0.0.3
     *
     * @param $group
     * @param $field
     *
     * @return string
     */
    public static function getConfigValue($group, $field)
    {
        return Configuration::get(self::buildParamName($group, $field));
    }

    /**
     * get current configuration
     *
     * @since 0.0.3
     *
     * @return string
     */
    private static function getConfig()
    {
        return self::$config;
    }
}