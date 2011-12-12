<?php
class MenuButton extends LinkButton
{
    protected $items = array();
    private static $counter;
    private $id;

    public function __construct($label,$icon=null)
    {
        parent::__construct($label,"#",$icon);
        MenuButton::$counter++;
        $this->id = "menu-button-".MenuButton::$counter;
    }

    public function addMenuItem($item,$link = null,$onclick=null)
    {
        $this->items[] = array("label"=>$item,"link"=>$link,"onclick"=>$onclick);
    }

    protected function _render()
    {
        $this->linkAttributes = "onclick=\"$('#$this->id').toggle('fast')\"";
        $menu = "<ul class='toolbar-menu' id='$this->id'>";
        foreach ($this->items as $item)
        {
            $menu .= "<li><a class=' toolbar-toolitem' href='$item[link]' onclick=\"$item[onclick]\">$item[label]</a></li>";
        }
        $menu .= "</ul>";
        return parent::_render().$menu;;
    }
}
?>