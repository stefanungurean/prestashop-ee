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
    const LINK_BUTTON='linkbutton';
    const INPUT_ON_OFF='onoff';
    const REQUIRED='required';
    const SANITIZE='sanitize';
    const SUBMIT_BUTTON='btnSubmit';
    const VALUE_TEXT='value';
    const LABEL_TEXT='label';
    const PARAM_LABEL='param_name';
    const FIELDS_LABEL='fields';
    const MULTIPLE_LABEL='multiple';
    const CLASS_LABEL='class';
    const OPTION_LABEL='options';

    const GROUP_LABEL='group';

    const VALIDATE_MAX_CHAR = 'maxchar';



    public function __construct($module)
    {
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

        $configGroup = isset($f[self::GROUP_LABEL]) ? $f[self::GROUP_LABEL] : $groupKey;
        if (isset($f[self::CLASS_LABEL])) {
            $configGroup = 'pt';
        }

        $elem = array(
            'name' => self::buildParamName($configGroup, $f['name']),
            self::LABEL_TEXT => isset($f[self::LABEL_TEXT])?$this->module->l($f[self::LABEL_TEXT]):"",
            'tab' => $groupKey,
            'type' => $f['type'],
            self::REQUIRED => isset($f[self::REQUIRED ]) && $f[self::REQUIRED ]
        );

        if (isset($f['cssclass'])) {
            $elem[self::CLASS_LABEL] = $f['cssclass'];
        }

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

        if (isset($f['docref'])) {
            $elem['desc'] = isset($elem['desc']) ? $elem['desc'] . ' ' : '';
            $elem['desc'] .= sprintf(
                '<a target="_blank" href="%s">%s <i class="icon-external-link"></i></a>',
                $f['docref'],
                $this->module->l('More information')
            );
        }

        switch ($f['type']) {
            case self::LINK_BUTTON:
                $elem['buttonText'] = $f['buttonText'];
                $elem['id'] = $f['id'];
                $elem[self::METHOD_NAME ] = $f[self::METHOD_NAME ];
                $elem['send'] = $f['send'];
                break;
            case self::INPUT_ON_OFF:
                $elem['type'] = $radio_type;
                $elem[self::CLASS_LABEL] = 't';
                $elem['is_bool'] = true;
                $elem['values'] = $radio_options;
                break;
            case 'text':
                if (!isset($elem[self::CLASS_LABEL])) {
                    $elem[self::CLASS_LABEL] = 'fixed-width-xl';
                }

                if (isset($f[self::VALIDATE_MAX_CHAR])) {
                    $elem['maxlength'] = $elem[self::VALIDATE_MAX_CHAR] = $f[self::VALIDATE_MAX_CHAR];
                }
                break;
            case 'select':
                if (isset($f[self::MULTIPLE_LABEL])) {
                    $elem[self::MULTIPLE_LABEL] = $f[self::MULTIPLE_LABEL];
                }

                if (isset($f['size'])) {
                    $elem['size'] = $f['size'];
                }

                if (isset($f[self::OPTION_LABEL])) {
                    $optfunc = $f[self::OPTION_LABEL];
                    $options = array();
                    if (is_array($optfunc)) {
                        $options = $optfunc;
                    }

                    if (method_exists($this->module, $optfunc)) {
                        $options = $this->module->$optfunc();
                    }

                    $elem[self::OPTION_LABEL] = array(
                        'query' => $options,
                        'id' => 'key',
                        'name' => self::VALUE_TEXT
                    );
                }
                break;
            default:
                break;
        }
        return $elem;
    }

    public function renderForm()
    {
        $input_fields = array();
        $tabs = array();

        foreach (self::$config as $groupKey => $group) {
            $tabs[$groupKey] = $this->module->l($group['tab']);
            foreach ($group[self::FIELDS_LABEL] as $f) {
                $elem=$this->processInput($f,$groupKey);
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
            $val = Configuration::get($parameter[self::PARAM_LABEL]);
            if (isset($parameter[self::MULTIPLE_LABEL]) && $parameter[self::MULTIPLE_LABEL]) {
                if (!is_array($val)) {
                    $val = Tools::strlen($val) ? Tools::jsonDecode($val) : array();
                }

                $x = array();
                foreach ($val as $v) {
                    $x[$v] = $v;
                }
                $pname = $parameter[self::PARAM_LABEL] . '[]';
                $values[$pname] = $x;
            } else {
                $values[$parameter[self::PARAM_LABEL]] = $val;
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
            foreach ($group[self::FIELDS_LABEL] as $f) {
                $configGroup = isset($f[self::GROUP_LABEL]) ? $f[self::GROUP_LABEL] : $groupKey;

                if (isset($f[self::CLASS_LABEL])) {
                    $configGroup = 'pt';
                }

                $f[self::PARAM_LABEL] = $this->buildParamName(
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
        $val = Tools::getValue($parameter[self::PARAM_LABEL]);

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
                $val = Tools::getValue($parameter[self::PARAM_LABEL]);

                if (isset($parameter[self::SANITIZE])&& $parameter[self::SANITIZE] == "trim") {
                    $val = trim($val);
                }

                if (is_array($val)) {
                    $val = Tools::jsonEncode($val);
                }
                Configuration::updateValue($parameter[self::PARAM_LABEL], $val);
            }
        }
        return $this->module->displayConfirmation($this->module->l('Settings updated'));
    }

    /**
     * set configuration value defaults
     *
     * @since 0.0.2
     *
     * @return bool
     */
    public static function setDefaults()
    {
        foreach (self::$config as $groupKey => $group) {
            foreach ($group[self::FIELDS_LABEL] as $f) {
                if (array_key_exists('default', $f) && !self::setDefaultValue($f, $groupKey)) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function setDefaultValue($f, $groupKey)
    {
        $configGroup = isset($f[self::GROUP_LABEL]) ? $f[self::GROUP_LABEL] : $groupKey;

        if (isset($f[self::CLASS_LABEL])) {
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
            foreach ($group[self::FIELDS_LABEL] as $f) {
                $classNameIndex=self::CLASS_NAME;
                if (array_key_exists($classNameIndex, $f)) {
                    if ($paymentType !== null && (!isset($f[$classNameIndex])||$f[$classNameIndex] != $paymentType)) {
                        continue;
                    }
                    $className = 'WirecardPaymentGatewayPayment' . $f[$classNameIndex];
                    $f[self::GROUP_LABEL] = 'pt';
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

    public function setStatus()
    {
        if (!Configuration::get(OrderMangement::WDEE_OS_AWAITING)) {

            /** @var OrderStateCore $orderState */
            $orderState = new OrderState();
            $orderState->name = array();
            foreach (Language::getLanguages() as $language) {
                $orderState->name[$language['id_lang']] = 'Checkout Wirecard Gateway payment awaiting';
            }
            $orderState->send_email = false;
            $orderState->color = 'lightblue';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = false;
            $orderState->invoice = false;
            $orderState->add();
            Configuration::updateValue(
                OrderMangement::WDEE_OS_AWAITING,
                (int)($orderState->id)
            );
        }

        if (!Configuration::get(OrderMangement::WDEE_OS_FRAUD)) {

            /** @var OrderStateCore $orderState */
            $orderState = new OrderState();
            $orderState->name = array();
            foreach (Language::getLanguages() as $language) {
                $orderState->name[$language['id_lang']] = 'Checkout Wirecard Gateway fraud detected';
            }
            $orderState->send_email = false;
            $orderState->color = '#8f0621';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = false;
            $orderState->invoice = false;
            $orderState->module_name =$this->module->name;
            $orderState->add();

            Configuration::updateValue(
                OrderMangement::WDEE_OS_FRAUD,
                (int)($orderState->id)
            );
        }
    }
}
