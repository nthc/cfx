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
 * A class which provides utilities for the running application. This class 
 * provides the final stage of the applications execution process. It is
 * responsible for loading the controllers and their associated views. While
 * controller methods are running, they can also use this class to alter some 
 * aspects of the view (like setting the title of the web page or adding a 
 * directive to load a javascript).
 * 
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 */
class Application
{
    /**
     * Specifies that the current controller running was loaded from a 
     * Controller class.
     * @var string
     */
    const TYPE_MODULE = "type_module";
    
    /**
     * Specifies that the current controller running was loaded from a 
     * Model class.
     * @var string
     */
    const TYPE_MODEL = "type_model";
    
    /**
     * The notes that are currently displayed on the top of the rendered page.
     * @var Array
     */
    private static $notes = array();

    /**
     * Initial or default template used for laying out the pages. The template
     * used can be chaged by altering the value of this variable during
     * the execution of the controller.
     * 
     * @var string
     */
    public static $template;

    /**
     * The title of the page for this given request. Although the title of the page
     * can be changed during the execution of your controller action by altering
     * the value of this property, you should use the Application::setTitle() method
     * for this purpose. 
     * 
     * @var string
     * @see Application::setTitle
     */
    private static $title;

    /**
     * The name of the application. The value of this variable is normally used
     * during the rendering of the title of the application into the title 
     * tag of the HTML output. The default value from this variable is always
     * read from the config.php file.
     * 
     * @var string
     */
    private static $siteName;

    /**
     * An array containing all the stylesheets which would be used for styling
     * the current layout. Stylesheets can be added through the 
     * Application::addStylesheet method.
     * 
     * @var array
     * @see Application::addStylesheet.
     */
    private static $stylesheets = array();

    /**
     * An array containing all the javascripts which would be loaded for the
     * current request. Javascripts can be added through the 
     * Application::addJavascript method.
     * 
     * @var array
     */
    private static $javascripts = array();

    /**
     * A prefix to all the pages of the application. This property can be set if
     * the application is running through a sub directory of the web server's
     * root directory. This can be set through the config.php of the running
     * application.
     * 
     * @var string
     */
    public static $prefix;

    /**
     * Contains the current menu tree for the menu loaded into the side bar.
     * This variable is usually populated based on the roles currently attached
     * to the user.
     * 
     * @var array
     */
    public static $menus = array();

    /**
     * The filesystem path to the loaction of the application's modules. By 
     * default, modules are store in the app/modules directory.
     * 
     * @var string.
     */
    public static  $packagesPath;
    
    /**
     * Set to true when applicaiton is in CLI mode and false otherwise.
     * 
     * @var boolean
     */
    public static $cli = false;
    
    /**
     * Contains the output that is generated when the application is executed
     * throuh the command line interface.
     * 
     * @var string
     */
    public static $cliOutput = "";
    
    /**
     * The current configuration map. Modifying this value during runtime has
     * no effect.
     * @var array
     */
    public static $config;
    
    /**
     * An instance of the template engine which would be used to render
     * the controller at the very last stage of the execution. You can use this
     * instance to do any rendering you want during the execution of your
     * controller. Using this prevents the loading of multiple instances of
     * the template engine.
     * 
     * @var Smarty
     */
    public static $templateEngine;
    
    /**
     * The default controller route that should be loaded when the request is
     * empty. You can think of this as your default or index page.
     * @var string
     */
    public static $defaultRoute = "dashboard";
    
    /**
     * The flag which indicates whether the side menu is visible or not.
     * @var boolean
     */
    private static $sideMenuHidden = false;
    
    /**
     * Adds a stylesheet to the list of stylesheets. This method adds
     * the stylesheets at the bottom of the list.
     *
     * @param string $href A path to the stylesheet
     * @param string $media The media of the stylesheet. Defaults to all.
     * @param string $pathPrefix An optional prefix to add to the path.
     */
    public static function addStylesheet($href, $pathPrefix = false, $media="all")
    {
        Application::$stylesheets[] = self::prepareStylesheetEntry($href, $pathPrefix, $media);
    }
    
    /**
     * Adds a stylesheet to the list of stylesheets. This method adds the 
     * stylesheets to the top of the list.
     * 
     * @param string $href A path to the stylesheet
     * @param string $media The media of the stylesheet. Defaults to all.
     * @param string $pathPrefix An optional prefix to add to the path.
     */
    public static function preAddStylesheet($href, $pathPrefix = false, $media="all")
    {
        array_unshift(Application::$stylesheets, self::prepareStylesheetEntry($href, $pathPrefix, $media));
    }
    
    /**
     * Format the stylesheet entry and make it a little bit more appropriate
     * for rendering.
     * 
     * @param string $href A path to the stylesheet
     * @param string $media The media of the stylesheet. Defaults to all.
     * @param string $pathPrefix An optional prefix to add to the path.
     * @return Array. A structured array describing the array.
     */ 
    private static function prepareStylesheetEntry($href, $pathPrefix, $media)
    {
        return array(
            "href"=>($pathPrefix === false ? "app/themes/" . Application::$config['theme'] . "/" : $pathPrefix) . $href,"media"=>$media
        );
    }

    /**
     * This method returns a link to a resource within your WYF app. This method
     * adds any prefixes the application requires.
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
            Application::$title = Application::$siteName;
        }
        else
        {
            Application::$title = $title . " | ". Application::$siteName;
        }
    }

    /**
     * Outputs the application. This method is the final stage in the application
     * lifecyle which calls all the template files and outputs the
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

    /**
     * A utility method which converts a string to lowecase while converting
     * all spaces to underscores.
     * 
     * @param string $name The string to be converted/
     * @return string A lowercase string with all spaces as underscores.
     */
    public static function labelize($name)
    {
        return ucwords(str_replace("_", " ", $name));
    }
    
    /**
     * Sets a flag in the template which prevents the rendering of the side menu.
     * Please note that this method would only work if the template being used
     * to render your app supports hiding side menus.
     */
    public static function hideSideMenu()
    {
        self::$sideMenuHidden = true;
    }
    
    /**
     * Unsets a flag in the template which prevents the rendering of the side menu.
     * Please note that this method would only work if the template being used
     * to render your app supports hiding side menus.
     */    
    public static function showSideMenu()
    {
        self::$sideMenuHidden = false;
    }
    
    /**
     * 
     */
    public static function setSiteName($siteName)
    {
        self::$siteName = $siteName;
    }
    
    public static function getWyfHome($path = '')
    {
        return substr(__DIR__, strlen(getcwd()) + 1) . "/$path";
    }    
}
