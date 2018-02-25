<?php

use Wirecard\PaymentSdk\Config\Config;

/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/25/18
 * Time: 09:21 PM
 */

trait TransactionConfigTrait
{
    protected $maid;
    protected $key;
    protected $config;
    protected $context;
    protected $module;
    protected $paymentMethod;

    protected function getCommonConfiguration() {
        $this->context = Context::getContext();
        $this->module = Module::getInstanceByName("wirecardpaymentgateway");
        $currency = new CurrencyCore($this->context->cart->id_currency);
        $currencyIsoCode = $currency->iso_code;
        $baseUrl = Configuration::get($this->module->buildParamName($this->paymentMethod, 'wirecard_server_url'));
        $httpUser = Configuration::get($this->module->buildParamName($this->paymentMethod, 'http_user'));
        $httpPass = Configuration::get($this->module->buildParamName($this->paymentMethod, 'http_password'));
        $this->maid = Configuration::get($this->module->buildParamName($this->paymentMethod, 'maid'));
        $this->key = Configuration::get($this->module->buildParamName($this->paymentMethod, 'secret')) ;

        $this->config = new Config($baseUrl, $httpUser, $httpPass, $currencyIsoCode);

    }

}