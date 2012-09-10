<?php
/**
 * Controllers and all their classes
 * @defgroup Controllers
 */

/**
 * The Controller class represents the base class for all controllers that are
 * built for the ntentan framework. Controllers are used to direct the flow of
 * your application. They are stored in modules and they contain methods which
 * are called from the url. Parameters to the methods are also passed through the
 * URL. If no method is specified, the Controller:getContents() method is called.
 * The methods called by the controllers are expected to generate HTML output
 * which should be directly displayed to the screen.
 *
 * All the controllers you build must extend this class end implement
 *
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 * @ingroup Controllers
 *
 */
class Controller
{
    /**
     * Check if this controller is supposed to be shown in any menus that are
     * created. This property is usually false for modules which are built for
     * internal use within the application.
     * @var boolean
     */
    protected $_showInMenu = false;

    /**
     * A descriptive label for this controler.
     * @var string
     */
    public $label;

    /**
     * A piece of text which briefly describes this controller
     * @var string
     */
    public $description;

    /**
     * A variable which contains the contents of a given controller after a
     * particular method has been called. This is what external controllers
     * usually use.
     * @var string
     */
    public $content;

    /**
     * This constant represents controllers that are loaded from modules
     * @var string
     */
    const TYPE_MODULE = "module";
    
    /**
     * The constant represents controllers that are loaded from raw classes.
     * @var string
     */
    const TYPE_CLASS = "class";

    /**
     * This constant represents controllers that are loaded from models.
     * @var string
     */
    const TYPE_MODEL = "model";

    /**
     * This constant represents controllers that are loaded from report.xml
     * files
     * @var string
     */
    const TYPE_REPORT = "report";

    /**
     * A copy of the path that was used to load this controller in an array
     * form.
     * @var Array
     */
    public $path;

    /**
     * A short machine readable name for this label.
     * @var string
     */
    public $name;
    
    /**
     * Tells whether the model has been redirected or not.
     * @var boolean
     */
    public $redirected;
    
    /**
     * A path to redirect controllers to. 
     * @warning This value is automatically set when
     *      redirections are done. They should never be modified unless the
     *      developpers realy know what they are doing.
     * @var string
     */
    public $redirectPath;
    
    /**
     * The new package path to use for redirected packages.
     * @var string
     */
    public $redirectedPackage;
    
    public $mainRedirectedPackage;
    
    public $redirectedPackageName;

    /**
     * A utility method to load a controller. This method loads the controller
     * and fetches the contents of the controller into the Controller::$contents
     * variable if the get_contents parameter is set to true on call. If a controller
     * doesn't exist in the module path, a ModelController is loaded to help
     * manipulate the contents of the model. If no model exists in that location,
     * it is asumed to be a package and a package controller is loaded.
     *
     * @param $path         The path for the model to be loaded.
     * @param $get_contents A flag which determines whether the contents of the
     *                        controller should be displayed.
     * @return Controller
     */
    public static function load($path,$get_contents=true)
    {
        global $redirectedPackage;
        global $packageSchema;
        
        $controller_path = "";
        $controller_name = "";
        $redirected = false;
        $redirect_path = "";
        $package_name = "";
        $package_main = "";

        //Go through the whole path and build the folder location of the system
        for($i = 0; $i<count($path); $i++)
        {
            $p = $path[$i];
            $baseClassName = $package_name . Application::camelize("$controller_path/$p", "/");
            
            if(file_exists(SOFTWARE_HOME . "app/modules/$controller_path/$p/{$baseClassName}Controller.php"))
            {
                $controller_class_name = $baseClassName . "Controller";
                $controller_name = $p;
                $controller_path .= "/$p";
                $controller_type = Controller::TYPE_MODULE;
                add_include_path("app/modules/$controller_path/");
                break;
            }
            else if(file_exists(SOFTWARE_HOME . "app/modules/$controller_path/$p/$p.php"))
            {
                $controller_name = $p;
                $controller_path .= "/$p";
                $controller_type = Controller::TYPE_MODULE;
                break;
            }
            else if(file_exists(SOFTWARE_HOME . "app/modules/$controller_path/$p/{$baseClassName}Model.php"))
            {
                $controller_name = $p;
                $controller_path .= "/$p";
                $controller_type = Controller::TYPE_MODEL;
                break;
            }
            else if(file_exists(SOFTWARE_HOME . "app/modules/$controller_path/$p/model.xml"))
            {
                $controller_name = $p;
                $controller_path .= "/$p";
                $controller_type = Controller::TYPE_MODEL;
                break;
            }
            else if(file_exists(SOFTWARE_HOME . "app/modules/$controller_path/$p/report.xml"))
            {
                $controller_name = $p;
                $controller_path .= "/$p";
                $controller_type = Controller::TYPE_REPORT;
                break;
            }
            else if(file_exists(SOFTWARE_HOME . "app/modules/$controller_path/$p/package_redirect.php"))
            {
                include(SOFTWARE_HOME . "app/modules/$controller_path/$p/package_redirect.php");
                $redirected = true;
                $previousControllerPath = $controller_path . "/$p"; 
                $controller_path = "";
                $redirectedPackage = $package_path;
                $packageSchema = $package_schema;
            }
            else if($redirected === true && file_exists(SOFTWARE_HOME . "$redirect_path/$controller_path/$p/report.xml"))
            {
                $controller_name = $p;
                $controller_path .= "/$p";
                $controller_type = Controller::TYPE_REPORT;
                break;
            }
            else if($redirected === true && file_exists(SOFTWARE_HOME . "$redirect_path/$controller_path/$p/{$baseClassName}Controller.php"))
            {
                $controller_class_name = $baseClassName . "Controller";
                $controller_name = $p;
                $controller_path .= "/$p";
                $controller_type = Controller::TYPE_MODULE;
                $package_main .= $p;
                add_include_path("$redirect_path/$controller_path/");
                break;
            }
            else
            {
                $controller_path .= "/$p";
                if($redirected) $package_main .= "$p."; 
            }
        }

        // Check the type of controller and load it.
        switch($controller_type)
        {
            case Controller::TYPE_MODULE:
                // Load a module controller which would be a subclass of this
                // class
                if($controller_class_name=="")
                {
                    require_once SOFTWARE_HOME . "app/modules$controller_path/$controller_name.php";
                    $controller = new $controller_name();
                }
                else
                {
                    $controller_name = $controller_class_name;
                    $controller = new $controller_class_name();
                    $controller->redirected = $redirected;
                    $controller->redirectPath = $redirect_path;
                    $controller->redirectedPackage = $package_path;
                    $controller->mainRedirectedPackage = $package_main;
                    $controller->redirectedPackageName = $package_name;
                }
                break;

            case Controller::TYPE_MODEL;
                // Load the ModelController wrapper around an existing model class.
                $model = substr(str_replace("/",".",$controller_path),1);
                $controller_name = "ModelController";
                $controller = new ModelController($model, $package_path);
                break;
                
            case Controller::TYPE_REPORT:
                $controller = new XmlDefinedReportController($redirect_path . $controller_path."/report.xml", $redirected);
                $controller_name = "XmlDefinedReportController";
                break;

            default:
                // Load a package controller for this folder
                if(is_dir("app/modules$controller_path"))
                {
                    $controller = new PackageController($path);
                    $controller_name = "PackageController";
                    $get_contents = true;
                    $force_output = true;
                }
                else if($redirected === true && is_dir(SOFTWARE_HOME . "$redirect_path/$controller_path"))
                {
                    $controller = new PackageController($path);
                    $controller_name = "PackageController";
                    $get_contents = true;
                    $force_output = true;
                }
                else
                {
                    $controller = new ErrorController();
                    $controller_name = "ErrorController";
                }
        }

        // If the get contents flag has been set return all the contents of this
        // controller.
        $controller->path = $previousControllerPath . $controller_path;
        
        if($get_contents)
        {
            if($i == count($path)-1 || $force_output)
            {
                $ret = $controller->getContents();
            }
            else
            {
                if(method_exists($controller,$path[$i+1]))
                {
                    $controller_class = new ReflectionClass($controller_name);
                    $method = $controller_class->GetMethod($path[$i+1]);
                    $ret = $method->invoke($controller,array_slice($path,$i+2));
                }
                else
                {
                    $ret = "<h2>Error</h2> Method does not exist. [" . $path[$i+1] . "]";
                }
            }
            
            
            if(is_array($ret))
            {
                $t = new TemplateEngine();
                $t->assign('controller_path', $controller_path);
                $t->assign($ret["data"]);
                $controller->content = $t->fetch(isset($ret["template"])?$ret["template"]:$path[$i+1].".tpl");
            }
            else if(is_string($ret))
            {
                $controller->content = $ret;
            }
        }
        
        return $controller;
    }

    /**
     * The getContents method is the default point of call for any controller.
     * Every controller should override this method. The default method just
     * returns the string "No Content"
     *
     * @return string
     */
    protected function getContents()
    {
        return "<h1>Ooops! No Content</h1><p>Create a <b><code>" . $this->getClassName() . ".getContents()</code></b> method to provide default content for this controller.";
    }

    /**
     * Getter for the Controller::_showInMenu method
     * @return boolean
     */
    public function showInMenu($value = '')
    {
        if($value === '') 
        {
            return $this->_showInMenu;
        }
        else
        {
            $this->_showInMenu = $value;
            return $this;
        }
    }
    
    /**
     * An empty implementation of the getPermissions method
     */
    public function getPermissions()
    {

    }

    /**
     * Returns an array description to be used for rendering the smarty template.
     * 
     * @deprecated
     * @param string   $template
     * @param array    $data
     */
    public function getTemplateDescription($template,$data)
    {
        return array("template"=>"file:/".getcwd()."/app/modules/$template","data"=>$data);
    }
    
    /**
     * Returns an array description to be used for rendering the smarty template.
     * 
     * @param string $template
     * @param array $data
     */
    public function template($template, $data)
    {
        return array(
           "template"=>"file:/" . getcwd() . "/app/modules/{$this->path}/{$template}.tpl", 
           "data"=>$data
        );
    }
    
    public function arbitraryTemplate($arbitraryTemplate, $data)
    {
        return array(
            'template' => "file:/".SOFTWARE_HOME."/$arbitraryTemplate",
            'data' => $data        
        );
    }
    
    /**
     * A utility method which draws an attribute table.
     * @param unknown_type $attributes
     */
    public function getAttributeTable($attributes)
    {
        $ret = "<table width='100%'>";
        foreach($attributes as $key => $value)
        {
            $ret .= "<tr><td><b>".ucwords(str_replace("_", " ", $key))."</b></td><td>{$value}</td></tr>";
        }
        $ret .= "</table>";
        return $ret;
    }
    
    /**
     * Returns the name of the class.
     * @return
     */
    public function getClassName()
    {
        $objectInfo = new ReflectionObject($this);
        return $objectInfo->getName();
    }
    
}
