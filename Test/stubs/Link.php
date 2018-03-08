<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 11:06 PM
 */

class Link
{
    public function getModuleLink($module, $controller, $params = array(), $flag = array())
    {
        return 'http://localhost/prestashop/'.$module.'/'.$controller."/";
    }
    public function getAdminLink()
    {
    }
    public function getPageLink()
    {
    }
}
