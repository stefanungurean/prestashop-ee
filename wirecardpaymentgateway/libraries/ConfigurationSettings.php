<?php
/**
 * Created by IntelliJ IDEA.
 * User: eduard.stroia
 * Date: 26.02.2018
 * Time: 19:02
 */

require_once __DIR__.'/OrderMangement.php';

class ConfigurationSettings
{
    private $module;
    static public $config;
    const CLASS_NAME="className";
    const METHOD_NAME="method";

    //inputs names
    const LINK_BUTTON='linkbutton';
    const INPUT_ON_OFF='onoff';
    const SUBMIT_BUTTON='btnSubmit';

    //validation names
    const REQUIRED='required';
    const SANITIZE='sanitize';
    const VALIDATE_MAX_CHAR = 'maxchar';

    //labels names
    const VALUE_TEXT='value';
    const LABEL_TEXT='label';
    const PARAM_TEXT='param_name';
    const FIELDS_TEXT='fields';
    const MULTIPLE_TEXT='multiple';
    const CLASS_TEXT='class';
    const OPTION_TEXT='options';
    const GROUP_TEXT='group';

    public function __construct($module, $config)
    {
        self::$config=$config;
        $this->module=$module;
        ini_set(
            'include_path',
            ini_get('include_path')
            . PATH_SEPARATOR . realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR.'..' .DIRECTORY_SEPARATOR. 'vendor'
            . PATH_SEPARATOR . realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR.'..' .DIRECTORY_SEPARATOR. 'models'
        );
        require_once 'wirecardee_autoload.php';
    }

    public function processInput($f, $groupKey)
    {
        $configGroup = $groupKey;
        $label = "";

        if (isset($f[self::GROUP_TEXT])) {
            $configGroup=$f[self::GROUP_TEXT];
        }
        if (isset($f[self::LABEL_TEXT])) {
            $label= $this->module->l($f[self::LABEL_TEXT]);
        }

        if (isset($f[self::CLASS_TEXT])) {
            $configGroup = 'pt';
        }

        $elem = array(
            'name' => self::buildParamName($configGroup, $f['name']),
            self::LABEL_TEXT => $label,
            'tab' => $groupKey,
            'type' => $f['type'],
            self::REQUIRED => isset($f[self::REQUIRED ]) && $f[self::REQUIRED ]
        );

        if (isset($f['cssclass'])) {
            $elem[self::CLASS_TEXT] = $f['cssclass'];
        }

        $elem = $this->processInputDoc($f, $elem);

        if (isset($f['docref'])) {
            $desc='';
            if (isset($elem['desc'])) {
                $desc= $elem['desc']. ' ';
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

    public function processInputDoc($f, $elem)
    {
        if (isset($f['doc'])) {
            if (is_array($f['doc'])) {
                $elem['desc'] = '';
                foreach ($f['doc'] as $d) {
                    if (Tools::strlen($elem['desc'])) {
                        $elem['desc'] .= '<br/>';
                    }

                    $elem['desc'] .= $this->module->l($d);
                }
            } else {
                $elem['desc'] = $this->module->l($f['doc']);
            }
        }
        return $elem;
    }

    public function processInputType($f, $elem)
    {
        $radio_type = 'switch';

        $radio_options = array(
            array(
                'id' => 'active_on',
                self::VALUE_TEXT => 1,
                self::LABEL_TEXT => $this->module->l('Enabled')
            ),
            array(
                'id' => 'active_off',
                self::VALUE_TEXT => 0,
                self::LABEL_TEXT => $this->module->l('Disabled')
            )
        );
        switch ($f['type']) {
            case self::LINK_BUTTON:
                $elem['buttonText'] = $f['buttonText'];
                $elem['id'] = $f['id'];
                $elem[self::METHOD_NAME ] = $f[self::METHOD_NAME ];
                $elem['send'] = $f['send'];
                break;
            case self::INPUT_ON_OFF:
                $elem['type'] = $radio_type;
                $elem[self::CLASS_TEXT] = 't';
                $elem['is_bool'] = true;
                $elem['values'] = $radio_options;
                break;
            case 'text':
                if (!isset($elem[self::CLASS_TEXT])) {
                    $elem[self::CLASS_TEXT] = 'fixed-width-xl';
                }
                if (isset($f[self::VALIDATE_MAX_CHAR])) {
                    $elem['maxlength'] = $elem[self::VALIDATE_MAX_CHAR] = $f[self::VALIDATE_MAX_CHAR];
                }
                break;
            case 'select':
                $elem=$this->processInputTypeSelect($f, $elem);
                break;
            default:
                break;
        }
        return $elem;
    }

    public function processInputTypeSelect($f, $elem)
    {
        if (isset($f[self::MULTIPLE_TEXT])) {
            $elem[self::MULTIPLE_TEXT] = $f[self::MULTIPLE_TEXT];
        }

        if (isset($f['size'])) {
            $elem['size'] = $f['size'];
        }

        if (isset($f[self::OPTION_TEXT])) {
            $optfunc = $f[self::OPTION_TEXT];
            $options = array();
            if (is_array($optfunc)) {
                $options = $optfunc;
            }

            if (method_exists($this->module, $optfunc)) {
                $options = $this->module->$optfunc();
            }

            $elem[self::OPTION_TEXT] = array(
                'query' => $options,
                'id' => 'key',
                'name' => self::VALUE_TEXT
            );
        }
        return $elem;
    }

    public function renderForm()
    {
        $input_fields = array();
        $tabs = array();

        foreach (self::$config as $groupKey => $group) {
            $tabs[$groupKey] = $this->module->l($group['tab']);
            foreach ($group[self::FIELDS_TEXT] as $f) {
                $elem=$this->processInput($f, $groupKey);
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
     * @since 0.0.2
     *
     * @return array
     */
    public function getConfigFieldsValues()
    {
        $values = array();
        foreach ($this->getAllConfigurationParameters() as $parameter) {
            $val = Configuration::get($parameter[self::PARAM_TEXT]);
            if (isset($parameter[self::MULTIPLE_TEXT]) && $parameter[self::MULTIPLE_TEXT]) {
                if (!is_array($val)) {
                    $val = Tools::strlen($val) ? Tools::jsonDecode($val) : array();
                }

                $x = array();
                foreach ($val as $v) {
                    $x[$v] = $v;
                }
                $pname = $parameter[self::PARAM_TEXT] . '[]';
                $values[$pname] = $x;
            } else {
                $values[$parameter[self::PARAM_TEXT]] = $val;
            }
        }

        return $values;
    }

    /**
     * return alls configuration parameters
     *
     * @since 0.0.2
     *
     * @return array
     */
    public function getAllConfigurationParameters()
    {
        $params = array();
        foreach (self::$config as $groupKey => $group) {
            foreach ($group[self::FIELDS_TEXT] as $f) {
                $configGroup = isset($f[self::GROUP_TEXT]) ? $f[self::GROUP_TEXT] : $groupKey;

                if (isset($f[self::CLASS_TEXT])) {
                    $configGroup = 'pt';
                }

                $f[self::PARAM_TEXT] = $this->buildParamName(
                    $configGroup,
                    $f['name']
                );
                $params[] = $f;
            }
        }

        return $params;
    }

    /**
     * validate post parameters
     *
     * @since 0.0.2
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
    public function validateValue($parameter)
    {
        $val = Tools::getValue($parameter[self::PARAM_TEXT]);

        if (isset($parameter[self::SANITIZE])&& $parameter[self::SANITIZE] == "trim") {
                $val = trim($val);
        }

        if (isset($parameter[self::REQUIRED ]) && $parameter[self::REQUIRED ] && !Tools::strlen($val)) {
            $this->postErrors[] = $parameter[self::LABEL_TEXT] . ' ' . $this->module->l('is required.');
        }

        if (!isset($parameter['validator'])) {
            return false;
        }

        if ($parameter['validator']=='numeric' && Tools::strlen($val) && !is_numeric($val)) {
                $this->postErrors[] = $parameter[self::LABEL_TEXT] . ' ' . $this->module->l(' must be a number.');
        }
    }

    /**
     * process form post
     *
     * @since 0.0.2
     *
     */
    public function postProcess()
    {
        if (Tools::isSubmit(self::SUBMIT_BUTTON)) {
            foreach ($this->getAllConfigurationParameters() as $parameter) {
                $val = Tools::getValue($parameter[self::PARAM_TEXT]);

                if (isset($parameter[self::SANITIZE])&& $parameter[self::SANITIZE] == "trim") {
                    $val = trim($val);
                }

                if (is_array($val)) {
                    $val = Tools::jsonEncode($val);
                }
                Configuration::updateValue($parameter[self::PARAM_TEXT], $val);
            }
        }
        return $this->module->displayConfirmation($this->module->l('Settings updated'));
    }

    public static function setDefaults()
    {
        foreach (self::$config as $groupKey => $group) {
            foreach ($group[self::FIELDS_TEXT] as $f) {
                if (array_key_exists('default', $f) && !self::setDefaultValue($f, $groupKey)) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function setDefaultValue($f, $groupKey)
    {
        $configGroup = isset($f[self::GROUP_TEXT]) ? $f[self::GROUP_TEXT] : $groupKey;

        if (isset($f[self::CLASS_TEXT])) {
            $configGroup = 'pt';
        }
        $p = self::buildParamName($configGroup, $f['name']);
        $defVal = $f['default'];
        if (is_array($defVal)) {
            $defVal = Tools::jsonEncode($defVal);
        }

        if (!Configuration::updateValue($p, $defVal)) {
            return false;
        }
        return true;
    }

    /**
     * return paymenttype objects
     *
     * @param null $paymentType
     *
     * @return array
     */
    public function getPaymentTypes($paymentType = null)
    {
        $types = array();
        foreach (self::$config as $group) {
            foreach ($group[self::FIELDS_TEXT] as $f) {
                $classNameIndex=self::CLASS_NAME;
                if (array_key_exists($classNameIndex, $f)) {
                    if ($paymentType !== null && (!isset($f[$classNameIndex])||$f[$classNameIndex] != $paymentType)) {
                        continue;
                    }
                    $className = 'WirecardPaymentGatewayPayment' . $f[$classNameIndex];
                    $f[self::GROUP_TEXT] = 'pt';
                    $pt = new $className($this->module, $f);

                    $types[] = $pt;
                }
            }
        }


        return $types;
    }

    /**
     * @param $paymentType
     *
     * @return WirecardCheckoutSeamlessPayment |null
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
     * get config value, take presets into account
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
     * @return string
     * @throws Exception
     * @throws SmartyException
     */
    public function getContent()
    {
        $this->html = '<h2>' . $this->displayName . '</h2>';

        if (Tools::isSubmit(self::SUBMIT_BUTTON)) {
            $this->postValidation();
            if (!count($this->postErrors)) {
                $this->postProcess();
            } else {
                foreach ($this->postErrors as $err) {
                    $this->html .= $this->displayError(html_entity_decode($err));
                }
            }
        }

        $this->context->smarty->assign(
            array(
                'module_dir' => $this->_path,
                'ajax_configtest_url' => $this->context->module->link->getModuleLink('wirecardpaymentgateway', 'ajax')
            )
        );

        $this->html .= $this->context->smarty->fetch(
            dirname(__FILE__) . '/views/templates/admin/configuration.tpl'
        );
        $this->html .= $this->renderForm();

        return $this->html;
    }
}
