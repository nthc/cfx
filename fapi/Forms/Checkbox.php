<?php
include_once "Field.php";

/**
 * A regular checkbox with a label. This class renders a checkbox into the form.
 * @ingroup Forms
 */
class Checkbox extends Field
{
    /**
     * The value that this field should contain if this checkbox is checked. By 
     * default this is a value of '1' but it could be any other value. In cases
     * where the checkbox is not checked, the value stored in the 
     * Checkbox::$uncheckedValue would be sent to any form callbacks.
     * 
     * @var string
     * @see Checkbox::setCheckedValue
     */
    protected $checkedValue;
    
    /**
     * The value that this field should contain in cases where this checkbox
     * has not been checked. By default the value of this field is '0'. In
     * cases where the checkbox is checked, the value stored in the 
     * Checkbox::$checkedValue would be sent to any form callbacks.
     * 
     * @var string
     * @see Checkbox::setUncheckedValue
     */
    protected $uncheckedValue;

    /**
     * Constructor for the checkbox.
     *
     * @param $label The label of the checkbox.
     * @param $name The name of the checkbox used for the name='' attribute of the HTML output
     * @param $description A description of the field.
     * @param $value A value to assign to this checkbox in cases where the checkbox is selected
     * @param $uncheckedValue A value to assign to this checkbox in cases where the checkbox is not selected
     */
    public function __construct($label="", $name="", $description="", $value="", $uncheckedValue="0")
    {
        Element::__construct($label, $description);
        parent::__construct($name);
        $this->setCheckedValue($value);
        $this->setUncheckedValue($uncheckedValue);
    }

    /**
     * Sets the value that should be assigned as the checked value for
     * this check box. The checked value is the value returned to the HTML form
     * when this form has been checked during form submission. In cases where
     * the checkbox has not been checked, the un
     *
     * 
     * @param $checkedValue The value to be assigned.
     * @return Checkbox
     */
    public function setCheckedValue($checkedValue)
    {
        $this->checkedValue = $checkedValue;
        return $this;
    }
    
    /**
     * Sets the value that should be assigned as the unchecked value for this
     * check box. The unchecked balue is the value returned to the HTML form when
     * this form is not checked durint form submission. Note that the unchecked
     * value can only be retrieved through the forms API since browsers do not
     * submit values for unchecked forms during form submission.
     * 
     * @param unknown_type $uncheckedValue
     */
    public function setUncheckedValue($uncheckedValue)
    {
        $this->uncheckedValue = $uncheckedValue;
        return $this;
    }

    /**
     * Gets and returns the checkedValue for the check box.
     * @return string
     */
    public function getCheckedValue()
    {
        return $this->checkedValue;
    }

    /**
     * @see Element::render()
     * @return string
     */
    public function render()
    {
        $ret = "";
        $ret .= '<input class="fapi-checkbox" type="checkbox" name="'.$this->getName().'" id="'.$this->getId().'" value="'.$this->getCheckedValue().'" '.
              (($this->getValue()==$this->getCheckedValue())?"checked='checked'":"").' '.$this->getAttributes().' />';

        /*$ret .= '<span class="fapi-label">'.$this->getLabel()."</span>";*/
        return $ret;
    }

    public function getData($storable = false)
    {
        if(isset($_POST[$this->getName()]))
        {
            return parent::getData();
        }
        else
        {
            return array($this->getName(false) => $this->uncheckedValue);
        }
    }

    public function getDisplayValue()
    {
        return $this->getValue()==$this->checkedValue?"Yes":"No";
    }
    
    public function setWithDisplayValue($value) 
    {
        $this->setValue($value == 'Yes' ? $this->checkedValue : null);
    }

    public function getRequired()
    {
        return false;
    }
    
    public function setValue($value)
    {
        if($value == 't') $value = '1';
        parent::setValue($value);
        return $this;
    }
}
