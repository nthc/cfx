<?php
include_once "Element.php";

/**
 * A standard radio button. Can be added to the radio button group.
 * @ingroup Forms
 */
class RadioButton extends Field
{
    protected $checked_value;

    /**
     * The constructor of the radio button.
     *
     * @param $label
     * @param $value
     * @param $description
     * @param $id
     */
    public function __construct($label="", $name="", $description="", $value="")
    {
        Element::__construct($label, $description, $id );
        $this->setName($name);
        $this->setCheckedValue($value);
    }

    public function getCheckedValue()
    {
        return $this->checked_value;
    }

    public function setCheckedValue($checked_value)
    {
        $this->checked_value = $checked_value;
    }

    public function render()
    {
        $ret .= "<input class='fapi-radiobutton ".$this->getCSSClasses()."' ".$this->getAttributes()." type='radio' name='".$this->getName()."' value='".$this->getCheckedValue()."' ".($this->getValue()==$this->getCheckedValue()?"checked='checked'":"")."/>";
        return $ret;
    }
}

