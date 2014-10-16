<?php
/*
 * WYF Framework
 * Copyright (c) 2011 James Ekow Abaka Ainooson
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * A simple wrapper class for smarty. This class provides the boilerplate code
 * for initialising the template engine for use in the WYF framework.
 */
class TemplateEngine extends Smarty
{
    /**
     * Sets up the smarty engine
     */
    function __construct()
    {
        parent::__construct();
        $this->template_dir = SOFTWARE_HOME . 'app/themes/' . Application::$config['theme'] . '/templates';
        $this->compile_dir = SOFTWARE_HOME . 'app/cache/template_compile';
        $this->config_dir = SOFTWARE_HOME . 'config/template/';
        $this->cache_dir = SOFTWARE_HOME . 'app/cache/template';
        $this->caching  = false;
        $this->assign('host',$_SERVER["HTTP_HOST"]);
    }
    
    /**
     * A static function for invoking the smarty template engine given the 
     * template and the data variables.
     * 
     * @param string $template
     * @param string $data
     */
    public static function render($template, $data)
    {
        $t = new TemplateEngine();
        $t->assign($data);
        return $t->fetch("file:/" . getcwd() . "/$template");
    }
    
    public static function renderString($template, $data)
    {
        $file = "app/temp/" . uniqid() . ".tpl";
        file_put_contents($file, $template);
        $rendered = self::render($file, $data);
        unlink($file);
        return $rendered; 
   }
}
