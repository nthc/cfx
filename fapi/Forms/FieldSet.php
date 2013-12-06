<?php
include_once "Container.php";

/**
 * A fieldset container.
 * 
 * @author ekow
 *
 */
class Fieldset extends Container
{
    private $collapsible = false;

    public function __construct($label="",$description="")
    {
        parent::__construct();
        $this->setLabel($label);
        $this->setDescription($description);
    }

    public function setCollapsible($collapsible)
    {
        $this->collapsible = $collapsible;
        $this->addCSSClass("collapsible");
        return $this;
    }

    public function render()
    {
        $ret = "<fieldset class='fapi-fieldset ".$this->getCSSClasses()."' {$this->getAttributes()}>";
        $ret .= "<legend id='{$this->id}_leg' style='cursor:pointer' ".($this->collapsible?"onclick='fapiFieldsetCollapse(this.id)'":"")." >".$this->getLabel()."</legend>";
        if($this->collapsible) $ret .= "<div id='{$this->id}_leg_collapse' style='display:none'>";
        $ret .= "<div class='fapi-description'>".$this->getDescription()."</div>";
        $ret .= $this->renderElements();
        if($this->collapsible) $ret .= "</div>";
        $ret .= "</fieldset>";
        return $ret;
    }
}
