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
 */

require_once __DIR__.'/../../../vendor/autoload.php';

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Item;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Entity\Device;
use Wirecard\PaymentSdk\Entity\Amount;

class WirecardPaymentGatewayCart
{
    /** @var  WirecardPaymentGateway */
    public $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function getUrlParameters($cart, $orderNumber)
    {
        return array(
            'id_cart' => (int)$cart->id,
            'id_module' => (int)$this->module->id,
            'key' => $cart->secure_key,
            'order' => $orderNumber
        );
    }

    public function getRedirect($cart, $orderNumber)
    {
        $params=$this->getUrlParameters($cart, $orderNumber);
        return new Redirect(
            $this->module->getContext()->link->getModuleLink($this->module->getName(), 'success', $params, true),
            $this->module->getContext()->link->getModuleLink($this->module->getName(), 'cancel', $params, true)
        );
    }

    public function getNotification($cart, $orderNumber)
    {
        $params=$this->getUrlParameters($cart, $orderNumber);
        return $this->module->getContext()->link->getModuleLink(
            $this->module->getName(),
            'notify',
            $params,
            true
        );
    }

    public function getConsumerData($cart)
    {
        $customer = new Customer($cart->id_customer);

        $customerData = new AccountHolder();
        $customerData->setFirstName($customer->firstname);
        $customerData->setLastName($customer->lastname);
        $customerData->setEmail($customer->email);
        $customerData->setGender($customer->id_gender);
        if (Tools::strlen($customer->birthday) && $customer->birthday !== "0000-00-00") {
            $birthday = new DateTime($customer->birthday);
            $customerData->setDateOfBirth($birthday);
        }

        return $customerData;
    }

    public function getShippingData($cart)
    {
        $carrier = new Carrier($cart->id_carrier);
        $addressDelivery = new Address(intval($cart->id_address_delivery));

        $shippingData = new AccountHolder();
        $shippingData->setFirstName($addressDelivery->firstname);
        $shippingData->setLastName($addressDelivery->lastname);
        $shippingData->setAddress($this->getAddress($addressDelivery));
        $shippingData->setShippingMethod($carrier->getShippingMethod());

        return $shippingData;
    }

    public function getDevice($id_customer)
    {
        $Device=new Device();
        $Device->setFingerprint(md5($id_customer . "_" . microtime()));
        return $Device;
    }

    public function getBasket($cart)
    {
        $currency = new CurrencyCore($cart->id_currency);
        $currencyIsoCode = $currency->iso_code;
        $basket = new Basket();

        foreach ($cart->getProducts() as $product) {
            $price_wt=$product['price_wt'];
            $price=$product['price'];
            $tax = ($price_wt - $price) * 100 / $price;

            $productInfo = new Item(
                $product['name'],
                new Amount(
                    number_format(
                        $price_wt,
                        2,
                        '.',
                        ''
                    ),
                    $currencyIsoCode
                ),
                $product['cart_quantity']
            );
            $productInfo->setDescription(
                Tools::substr(
                    strip_tags($product['description_short']),
                    0,
                    127
                )
            );
            $productInfo->setTaxRate(
                number_format(
                    $tax,
                    2,
                    '.',
                    ''
                )
            );
            $basket->add($productInfo);
        }

        if ($cart->getTotalShippingCost() != 0) {
            $shipping = new Item(
                'Shipping',
                new Amount(
                    number_format(
                        $cart->getTotalShippingCost(),
                        2,
                        '.',
                        ''
                    ),
                    $currencyIsoCode
                ),
                '1'
            );

            $shipping->setDescription($this->module->l('Shipping'));
            $shipping->setTaxRate(
                number_format(
                    '0',
                    2,
                    '.',
                    ''
                )
            );
            $basket->add($shipping);
        }
        return $basket;
    }

    public function getConsumerIpAddress()
    {
        return Tools::getRemoteAddr();
    }

    public function getTotalAmount($cart)
    {
        $currency = new CurrencyCore($cart->id_currency);
        $currencyIsoCode = $currency->iso_code;
        return new Amount($cart->getOrderTotal(true), $currencyIsoCode);
    }

    public function setCustomField($CustomFieldArray)
    {
        $customFields = new CustomFieldCollection();
        if (!empty($CustomFieldArray)) {
            foreach ($CustomFieldArray as $key => $field) {
                $customOrderNumber = new CustomField($key, $field);
                $customFields->add($customOrderNumber);
            }
        }
        return $customFields;
    }

    protected function getAddress($source)
    {
        $country = new Country($source->id_country);

        $address = new \Wirecard\PaymentSdk\Entity\Address(
            $country->iso_code,
            $source->city,
            $source->address1
        );
        $address->setPostalCode($source->postcode);
        $address->setStreet2($source->address2);

        return $address;
    }
}
