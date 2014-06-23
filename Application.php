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
 * Main class for managing the page. The whole application runs through this class.
 * It contains mainly a list of static methods which are called through the
 * entire lifetime of a request to the application. In certain respects you
 * can consider this class as the main controller since it is the main point of
 * call during class loading.
 * 
 * @package wyf.core
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 */
class Application
{
    const TYPE_MODULE = "type_module";
    const TYPE_MODEL = "type_model";
    
    public static $notes = array();

    /**
     * Initial or default template used for laying out the page.
     * @var string
     */
    public static $template;

    /**
     * The title of the page for this given request
     * @var string
     */
    public static $title;

    /**
     * The name of the application
     * @var string
     */
    public static $site_name;

    /**
     * An array containing all the stylesheets which would be used for styling
     * the current layout.
     * @var array
     */
    private static $stylesheets = array();

    /**
     * An array containing all the javascripts which would be loaded for the
     * current request.
     * @var array
     */
    private static $javascripts = array();

    /**
     * A prefix to all the pages of the application. This property can be set if
     * the application is running through a sub directory of the web server's
     * root directory.
     * @var string
     */
    public static $prefix;

    /**
     * An array which lists all the menus currentlu being used in the
     * application.
     * @var array
     */
    public static $menus = array();

    /**
     * The path of the packages
     * @var unknown_type
     */
    public static  $packagesPath;
    
    /**
     * Returns true when applicaiton is in CLI mode and false otherwise.
     * @var type 
     */
    public static $cli = false;
    
    /**
     * 
     * @var type 
     */
    public static $cliOutput = "";
    
    public static $config;
    
    public static $templateEngine;
    
    public static $defaultRoute = "dashboard";
    
    private static $sideMenuHidden = false;
    
    /**
     * A method to add a stylesheet to the list of stylesheets
     *
     * @param string $href A path to the stylesheet
     * @param string $media The media of the stylesheet. Defaults to all.
     */
    public static function addStylesheet($href, $pathPrefix = false, $media="all")
    {
        Application::$stylesheets[] = self::prepareStylesheetEntry($href, $pathPrefix, $media);
    }
    
    public static function preAddStylesheet($href, $pathPrefix = false, $media="all")
    {
        array_unshift(Application::$stylesheets, self::prepareStylesheetEntry($href, $pathPrefix, $media));
    }
    
    private static function prepareStylesheetEntry($href, $pathPrefix, $media)
    {
        return array(
            "href"=>($pathPrefix === false ? "app/themes/" . Application::$config['theme'] . "/" : $pathPrefix) . $href,"media"=>$media
        );
    }

    /**
     * This method returns a link. It is useful because it prefixes all the
     * links with the appropriate prefixes.
     *
     * @param string $path
     * @return string
     */
    public static function getLink($path)
    {
        return Application::$prefix.$path;
    }

    /**
     * A method to add a javascript to the list of javascripts.
     *
     * @param string $href A path to the javascript.
     */
    public static function addJavascript($href)
    {
        Application::$javascripts[] = $href;
    }

    /**
     * Sets the title of the page. This method appends the title set to
     * the name of the site.
     *
     * @param string $title
     */
    public static function setTitle($title="")
    {
        if($title=="")
        {
            Application::$title = Application::$site_name;
        }
        else
        {
            Application::$title = $title . " | ". Application::$site_name;
        }
    }

    /**
     * Outputs the application. This calls all the template files and outputs the
     * final application in HTML.
     */
    public static function render()
    {
        $t = Application::$templateEngine;
        if($_GET["q"]=="")
        {
            $_GET["q"]= Application::$defaultRoute;
        }
        $path = explode("/",$_GET["q"]);
        Application::$template = "main.tpl";

        $t->assign('prefix',Application::$prefix);

        Application::setTitle();
        $module = Controller::load($path);
        
        if(Application::$cli)
        {
            ob_start();
        }
        
        if(Application::$template == "")
        {
            print $module->content;
        }
        else
        {
            $t->assign('content',$module->content);
            $t->assign('module_name', $module->label);
            $t->assign('module_description',$module->description);
            $t->assign('side_menu_hidden', self::$sideMenuHidden);

            foreach(array_keys(Application::$menus) as $key)
            {
                $t->assign($key, Menu::getContents($key));
            }
            
            $t->assign('stylesheets',Application::$stylesheets);
            $t->assign('styles',$t->fetch('stylesheets.tpl'));
            $t->assign('javascripts',Application::$javascripts);
            $t->assign('scripts',$t->fetch('javascripts.tpl'));
            $t->assign('title', Application::$title);
            $t->assign('session', $_SESSION);
            $t->assign('info', 
                array_merge((is_array($_SESSION['notes']) ? $_SESSION['notes'] : array()), self::$notes)
            );
            $t->display(Application::$template);
        }
                
        if(Application::$cli)
        {
            if(Application::$cliOutput=="")
            {
                print ob_get_clean();
            }
            else
            {
                file_put_contents(Application::$cliOutput, ob_get_clean());
            }
        }
    }
    
    /**
     * Emits a header to redirect the page to a new location. This method 
     * checks to find out if the redirect parameter has been set in the url. If
     * this parameter is set, it takes precedence over the parameter passed to
     * the url.
     * 
     * @param string $url The url to redirect to.
     */
    public static function redirect($url, $notification = false)
    {
        if(isset($_GET["redirect"]))
        {
            header("Location: {$_GET["redirect"]}" . ($notification === false ? '' : "?notification={$notification}"));
        }
        else
        {
            header("Location: $url");
        }
    }
    
    /**
     * A utility method which generates camelized names for classes. It is called
     * throughout the application to convert URLs and model paths. 
     * 
     * @param string $string
     * @param string $delimiter
     * @param string $baseDelimiter
     */
    public static function camelize($string, $delimiter=".", $baseDelimiter = "")
    {
        if($baseDelimiter == "") $baseDelimiter = $delimiter;
        $parts = explode($delimiter, $string);
        $ret = "";
        foreach($parts as $part)
        {
            $ret .= $delimiter == $baseDelimiter ? ucfirst(Application::camelize($part, "_", $baseDelimiter)) : ucfirst($part);
        }
        return $ret;
    }

    public static function labelize($name)
    {
        return ucwords(str_replace("_", " ", $name));
    }
    
    public static function hideSideMenu()
    {
        self::$sideMenuHidden = true;
    }
    
    public static function showSideMenu()
    {
        self::$sideMenuHidden = false;
    }
}
