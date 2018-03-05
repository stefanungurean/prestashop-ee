<?php
/**
 * Created by IntelliJ IDEA.
 * User: eduard.stroia
 * Date: 05.03.2018
 * Time: 11:09
 */

class ConfigurationSettingsTest extends \PHPUnit_Framework_TestCase
{
    public function testInitiate()
    {
        $configurationSettings =  new ConfigurationSettings();
        $this->assertInstanceOf("ConfigurationSettings", $configurationSettings);
    }
}
