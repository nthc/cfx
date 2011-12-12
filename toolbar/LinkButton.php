<?php
class LinkButton extends ToolbarButtonItem
{
    protected $label;
    protected $link;
    public $linkAttributes;

    public function __construct($label,$link,$icon=null)
    {
        $this->label = $label;
        $this->link = $link;
        $this->icon = $icon;
    }

    protected function _render()
    {
        return "<div class='icon i".  strtolower($this->label)."'><a href='{$this->link}' $this->linkAttributes >{$this->label}</a></div>";
    }

    public function getCssClasses()
    {
        return array(
            "toolbar-linkbutton-".strtolower($this->label),
            "toolbar-toolitem-button"
        );
    }
}
