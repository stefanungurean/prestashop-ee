<?php
/**
 * Created by IntelliJ IDEA.
 * User: eduard.stroia
 * Date: 05.03.2018
 * Time: 11:09
 */

class TabDataTest extends \PHPUnit_Framework_TestCase
{
    public function testInitiate()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $tabData = new TabData($module);
        $this->assertInstanceOf("TabData", $tabData);
    }

    public function testTabs()
    {
        $module = Module::getInstanceByName(Tools::strtolower('wirecardpaymentgateway'));
        $TabData = new TabData($module);
        $config = $TabData->getConfig();
        foreach ($TabData->getTabs() as $tab) {
            $this->assertArrayHasKey($tab, $config);
        }
    }
}
