<?php
/**
 * A Controller inteded to automatically show links to other controllers that
 * are found within a directory which doesn't have either a controller or a
 * model. This controller is used for generating the on screen menu.which appears
 * whenever a package is clicked on the menu.
 *
 * @ingroup Controllers
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 */
class PackageController extends Controller
{
    private $displayPath;
    
    public function __construct($path)
    {
        $this->displayPath = $path;
        $this->_showInMenu = true;
    }
    
    /**
     * 
     * Enter description here ...
     * @param unknown_type $menu
     */
    private function getChildren($menu)
    {
        if(!is_array($menu["children"])) return array();
        foreach($menu["children"] as $child)
        {
            if(count($child["children"]) == 0)
            {
                $return .= "<a href='{$child["path"]}' class='permission-button'>{$child["title"]}</a>";
            }
            else
            {
                $return .= "<div style='clear:both'></div><div class='sub-permissions'><a href='{$child["path"]}'><h3>{$child["title"]}</h3></a>";
                $return .= $this->getChildren($child)."<div style='clear:both'></div></div>";
            }
        }
        return $return;
    }

    /**
     * (non-PHPdoc)
     * @see lib/controllers/Controller::getContents()
     */
    public function getContents()
    {
        Application::addStylesheet("css/permissions.css");
        $menu = unserialize(file_get_contents("app/cache/menus/menu_{$_SESSION["role_id"]}.object"));
        $menu = $menu["/{$_GET["q"]}"];
        $return = "<h2>{$menu["title"]}</h2>";
        
        $return .= $this->getChildren($menu)."<div style='clear:both'></div>";

        return "<div id='permissions'>$return</div>";
    }
}

