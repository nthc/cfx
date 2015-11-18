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
        $tag = strtolower($this->label);
        return "<div class='icon i$tag'><a id='$tag-tool-link' href='{$this->link}' $this->linkAttributes >{$this->label}</a></div>";
    }

    public function getCssClasses()
    {
        return array(
            "toolbar-linkbutton-".strtolower($this->label),
            "toolbar-toolitem-button"
        );
    }
}
