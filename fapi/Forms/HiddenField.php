<?php
include_once "Field.php";

/**
 * Implementation of a regular hidden field. This field is used to hold
 * form information that is not supposed to be visible to the user.
 * @ingroup Forms
 */
class HiddenField extends Field
{
    public function __construct($name="", $value="")
    {
        parent::__construct($name, $value);
    }
    
    public function setValue($value)
    {
        if($value === false)
        {
            return parent::setValue('0');
        }
        else if($value === true)
        {
            return parent::setValue('1');
        }
        else
        {
            return parent::setValue($value);
        }
    }

    public function render()
    {
        return "<input type='hidden'  name='".$this->getName()."' value='".$this->getValue()."' {$this->getAttributes()} />";
    }

    public function getType()
    {
        return __CLASS__;
    }
}
