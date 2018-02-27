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

    public function renderForm()
    {
        $radio_type = 'switch';

        $radio_options = array(
            array(
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->module->l('Enabled')
            ),
            array(
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->module->l('Disabled')
            )
        );

        $input_fields = array();
        $tabs = array();

        foreach (self::$config as $groupKey => $group) {
            $tabs[$groupKey] = $this->module->l($group['tab']);
            foreach ($group['fields'] as $f) {
                $configGroup = isset($f['group']) ? $f['group'] : $groupKey;
                if (isset($f['class'])) {
                    $configGroup = 'pt';
                }

                $elem = array(
                    'name' => self::buildParamName($configGroup, $f['name']),
                    'label' => isset($f['label'])?$this->module->l($f['label']):"",
                    'tab' => $groupKey,
                    'type' => $f['type'],
                    'required' => isset($f['required']) && $f['required']
                );

                if (isset($f['cssclass'])) {
                    $elem['class'] = $f['cssclass'];
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
                        $elem['class'] = 't';
                        $elem['is_bool'] = true;
                        $elem['values'] = $radio_options;
                        break;
                    case 'text':
                        if (!isset($elem['class'])) {
                            $elem['class'] = 'fixed-width-xl';
                        }

                        if (isset($f['maxchar'])) {
                            $elem['maxlength'] = $elem['maxchar'] = $f['maxchar'];
                        }
                        break;
                    case 'select':
                        if (isset($f['multiple'])) {
                            $elem['multiple'] = $f['multiple'];
                        }

                        if (isset($f['size'])) {
                            $elem['size'] = $f['size'];
                        }

                        if (isset($f['options'])) {
                            $optfunc = $f['options'];
                            $options = array();
                            if (is_array($optfunc)) {
                                $options = $optfunc;
                            }

                            if (method_exists($this, $optfunc)) {
                                $options = $this->$optfunc();
                            }

                            $elem['options'] = array(
                                'query' => $options,
                                'id' => 'key',
                                'name' => 'value'
                            );
                        }
                        break;
                    default:
                        break;
                }

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
        return $this->module->HelperRender($fields_form_settings, $this->getConfigFieldsValues());
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
    static function buildParamName($group, $name)
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
            $val = Configuration::get($parameter['param_name']);
            if (isset($parameter['multiple']) && $parameter['multiple']) {
                if (!is_array($val)) {
                    $val = Tools::strlen($val) ? Tools::jsonDecode($val) : array();
                }

                $x = array();
                foreach ($val as $v) {
                    $x[$v] = $v;
                }
                $pname = $parameter['param_name'] . '[]';
                $values[$pname] = $x;
            } else {
                $values[$parameter['param_name']] = $val;
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
            foreach ($group['fields'] as $f) {
                $configGroup = isset($f['group']) ? $f['group'] : $groupKey;

                if (isset($f['class'])) {
                    $configGroup = 'pt';
                }

                $f['param_name'] = $this->buildParamName(
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
        if (Tools::isSubmit('btnSubmit')) {
            foreach ($this->getAllConfigurationParameters() as $parameter) {
                $val = Tools::getValue($parameter['param_name']);

                if (isset($parameter['sanitize'])) {
                    switch ($parameter['sanitize']) {
                        case 'trim':
                            $val = trim($val);
                            break;
                    }
                }

                if (isset($parameter['required']) && $parameter['required'] && !Tools::strlen($val)) {
                    $this->postErrors[] = $parameter['label'] . ' ' . $this->module->l('is required.');
                }

                if (!isset($parameter['validator'])) {
                    continue;
                }

                switch ($parameter['validator']) {
                    case 'numeric':
                        if (Tools::strlen($val) && !is_numeric($val)) {
                            $this->postErrors[] = $parameter['label'] . ' ' . $this->module->l(' must be a number.');
                        }
                        break;
                }
            }
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
        if (Tools::isSubmit('btnSubmit')) {
            foreach ($this->getAllConfigurationParameters() as $parameter) {
                $val = Tools::getValue($parameter['param_name']);

                if (isset($parameter['sanitize'])) {
                    switch ($parameter['sanitize']) {
                        case 'trim':
                            $val = trim($val);
                            break;
                    }
                }

                if (is_array($val)) {
                    $val = Tools::jsonEncode($val);
                }
                Configuration::updateValue($parameter['param_name'], $val);
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
    static function setDefaults()
    {
        foreach (self::$config as $groupKey => $group) {
            foreach ($group['fields'] as $f) {
                if (array_key_exists('default', $f)) {
                    $configGroup = isset($f['group']) ? $f['group'] : $groupKey;

                    if (isset($f['class'])) {
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
            }
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
            foreach ($group['fields'] as $f) {
                $classNameIndex=self::CLASS_NAME;
                if (array_key_exists($classNameIndex, $f)) {
                    if ($paymentType !== null && (!isset($f[$classNameIndex])||$f[$classNameIndex] != $paymentType)) {
                        continue;
                    }
                    $className = 'WirecardPaymentGatewayPayment' . $f[$classNameIndex];
                    $f['group'] = 'pt';
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
    static function getConfigValue($group, $field)
    {
        if ($group == 'basicdata') {
            $mode = Configuration::get(
                self::buildParamName(
                    'basicdata',
                    'configmode'
                )
            );
        }

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

        if (Tools::isSubmit('btnSubmit')) {
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