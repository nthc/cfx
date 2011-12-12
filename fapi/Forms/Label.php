<?php
/**
 * A Label element.
 * @ingroup Forms
 */
class Label extends Element
{
    public function __construct($label)
    {
        $this->setLabel($label);
    }

    public function render()
    {
        return "<label class='fapi-label {$this->getCSSClasses()}' {$this->getAttributes()} >".$this->getLabel()."</label>";
    }

    public function getData($storable=false)
    {
        return array();
    }

    public function setData($data)
    {

    }

    public function validate()
    {
        //Always return true cos labels can't be validated.
        return true;
    }
}
?>
