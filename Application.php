<?php

/**
 * Main class for managing the page. The whole application runs through this class.
 * It contains mainly a list of static methods which are called through the
 * entire lifetime of a request to the application. In certain respects you
 * can consider this class as the main controller since it is the main point of
 * call during class loading.
 * 
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 */
class Application
{
    const TYPE_MODULE = "type_module";
    const TYPE_MODEL = "type_model";

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
    
    public static $cli = false;
    
    public static $cliOutput = "";
    
    public static $config;
    
    public static $templateEngine;
    
    /**
     * A method to add a stylesheet to the list of stylesheets
     *
     * @param string $href A path to the stylesheet
     * @param string $media The media of the stylesheet. Defaults to all.
     */
    public static function addStylesheet($href, $pathPrefix = false, $media="all")
    {
        Application::$stylesheets[] = array("href"=>($pathPrefix === false ? "app/themes/" . Application::$config['theme'] . "/" : $pathPrefix) . $href,"media"=>$media);
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
            $_GET["q"]= "dashboard";
        }
        $path = explode("/",$_GET["q"]);
        Application::$template = "main.tpl";

        require SOFTWARE_HOME . "app/bootstrap.php";
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

            foreach(array_keys(Application::$menus) as $key)
            {
                $t->assign($key, Menu::getContents($key));
            }
            
            $t->assign('stylesheets',Application::$stylesheets);
            $t->assign('styles',$t->fetch('stylesheets.tpl'));
            $t->assign('javascripts',Application::$javascripts);
            $t->assign('scripts',$t->fetch('javascripts.tpl'));
            $t->assign('title', Application::$title);
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
    public static function redirect($url)
    {
        if(isset($_GET["redirect"]))
        {
            header("Location: {$_GET["redirect"]}");
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
}
