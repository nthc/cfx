<?php
/**
 * A class for showing the menu at the side of the application.
 * 
 * @author james
 *
 */
class SystemSideMenuController extends Controller
{
    /**
     * @var string
     */
    protected $menu;
    
    /**
     * @var integer
     */
    private $id;
    
    /**
     * @see lib/controllers/Controller#getContents()
     */
    protected function getContents()
    {
        //return $this->doMenu($this->menu);
    }
    
    /**
     * Performs recursive calls to the side_menu::generate() to generate an HTML
     * menu string which can be output to the user.
     * @param $menu
     * @return unknown_type
     */
    public function generate($menu)
    {
        return $this->doMenu(unserialize($menu[0]));
    }
    
    /**
     * Called recursively by the side_menu::generate() method for purposes of
     * generating a menu on the side.
     * 
     * @param $menu
     * @param $level
     * @return unknown_type
     */
    private function doMenu($menu,$level=0)
    {
        $this->id++;
        if(!is_array($menu)) return;
        $ret = "<ul class='menu "
             . ($level==0?"root-menu":"sub-menu")
             . " menu-level-$level'  id='menu-{$this->id}'>";
        foreach($menu as $item)
        {
            $nuId = $this->id+1;
            $path = count( $item["children"]) > 0 ? "javascript:" : Application::getLink($item["path"]);
            $extra = count($item["children"]) > 0 ? 
                     "onclick='expand(\"menu-$nuId\")' style='font-weight:bold'"
                     :"";
            $ret = $ret . "<li><a href='$path' $extra>{$item["title"]}</a>";
            if(count($item["children"]>0))
            {
                $ret =$ret . $this->doMenu($item["children"],$level+1);
            }
            $ret = $ret . "</li>";
        }
        $ret = $ret . "</ul>";
        return $ret;
    }
}

