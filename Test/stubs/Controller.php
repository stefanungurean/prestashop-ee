<?php
/**
 * Created by IntelliJ IDEA.
 * User: iustin.morosan
 * Date: 02/24/18
 * Time: 10:29 PM
 */

class Controller
{
    public $name=array();
    public $php_self;
    public $errors=array();

    public function __construct()
    {
    }

    /**
     * @param mixed $php_self
     */
    public function setPhpSelf($php_self)
    {
        $this->php_self = $php_self;
    }

    public function registerStylesheet($name, $files, $parameters)
    {
        $this->name[$name]=array($files, $parameters);
    }

    public function getStylesheet($name)
    {
        if (isset($this->name[$name])) {
            return $this->name[$name];
        }
        return array();
    }
    public function getLanguages()
    {
    }
    public function clearStylesheet()
    {
        $this->name=array();
    }
}
