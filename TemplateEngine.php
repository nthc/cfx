<?php
include SOFTWARE_HOME . "lib/smarty/libs/Smarty.class.php";

/**
 * 
 */
class TemplateEngine extends Smarty
{
    function __construct()
    {
        parent::__construct();
        $this->template_dir = SOFTWARE_HOME . 'app/templates/';
        $this->compile_dir = SOFTWARE_HOME . 'app/cache/template_compile';
        $this->config_dir = SOFTWARE_HOME . 'config/template/';
        $this->cache_dir = SOFTWARE_HOME . 'app/cache/template';
        $this->caching = false;
        $this->assign('host',$_SERVER["HTTP_HOST"]);
    }
    
    public static function render($template, $data)
    {
        $t = new TemplateEngine();
        $t->assign($data);
        return $t->fetch("file:/" . getcwd() . "/$template");
    }
}
