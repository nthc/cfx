<?php
/**
 * Widgets are small pieces of applications which are installed on the
 * home page.
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 * @ingroup Controllers
 */
abstract class Widget
{
	/**
	 * The label to display on top of the widget
	 * @var string
	 */
    public $label;
    
    /**
     * A weight to factor to determine where to place the widget on the
     * desktop
     * @var string
     */
    public $order = 0;
    
    private static $db = false;
    
    /**
     * Generates the HTML code.
     */
    public abstract function render();
    
    protected static function db()
    {
        if(self::$db === false)
        {
            self::$db = sqlite_open("app/temp/datadb.sqlite");
        }
        return self::$db;
    }
    
    public function getPermissions()
    {
        return array(
            array("label"=>"Can view",    "name"=>$this->name."_can_view"),
        );
    }
    
    public static function wrap($object)
    {
        $rendered = $object->render();
        if($rendered !== false) 
        {
            return "<div class='widget'><h2>{$object->label}</h2><div class='widget-content'>" . $rendered . "</div></div>";
        }
        else
        {
            return false;
        }
    }
}
