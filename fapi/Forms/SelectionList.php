<?php
/**
 * Renders a selection list 
 */
class SelectionList extends Field
{
    protected $options = array();
    protected $multiple;
    protected $hasGroups;
    protected $groupedOptions;

    public function __construct($label="", $name="", $description="")
    {
        Field::__construct($name);
        Element::__construct($label, $description);
    }

    //! Sets weather multiple selections could be made.
    public function setMultiple($multiple)
    {
        $this->name.="[]";
        $this->multiple = $multiple;
        return $this;
    }

    /**
     * Add an option to the selection list
     * 
     * @param type $label
     * @param type $value
     * @param type $group
     * @return \SelectionList 
     */
    public function addOption($label="", $value="", $group = '')
    {
        if($value==="") $value=$label;
            
        if($group != null)
        {
            $this->hasGroups = true;
            $this->groupedOptions[$group][$value] = $label ; //[] = new SelectionListItem($label, $value);
        }
        else
        {
            $this->options[$value] = $label; // new SelectionListItem($label, $value);
        }
        return $this;
    }
    
    public function flush()
    {
        $this->options = array();
        return $this;
    }

    public function render()
    {
        $validations = $this->getJsValidations();
        if($this->ajax && $validations != "[]")
        {
            $this->addAttribute("onblur","fapiValidate('".$this->getId()."',$validations)");
        }
        $this->addAttribute("id",$this->getId());
        if(count($this->jsOnChangeParams)>0) $this->addAttribute("onchange",$this->getId()."OnChangeFunction()");
        $ret = "<select {$this->getAttributes()} class='fapi-list ".$this->getCSSClasses()."' name='".$this->getName()."' ".($this->multiple?"multiple='multiple'":"").">";
        
        
        // Default option for null values
        $ret .= "<option value=''></option>";
        
        if($this->hasGroups)
        {
            foreach($this->groupedOptions as $group => $options)
            {
                $ret .= "<optgroup label='$group'>";
                foreach($options as $value => $label)
                {
                    $ret .= "<option value='$value' ".($this->getValue()==$value?"selected='selected'":"").">$label</option>";
                }
                $ret .= "</optgroup>";
                if(count($this->options))
                {
                    $ret .= "<optgroup label='Un Grouped'>";
                    foreach($this->options as $value => $label)
                    {
                        $ret .= "<option value='$value' ".((string)$this->getValue()===(string)$value?"selected='selected'":"").">$label</option>";
                    }
                    $ret .= "</optgroup>";
                }
            }
        }
        else
        {
            foreach($this->options as $value => $label)
            {
                $ret .= "<option value='$value' ".((string)$this->getValue()===(string)$value?"selected='selected'":"").">$label</option>";
            }
        }
        $ret .= "</select>";
        $ret .= $this->getJsOnChangeScript();
        return $ret;
    }

    public function getDisplayValue()
    {
        foreach($this->options as $value => $label)
        {
            if($value == $this->getValue())
            {
                return $label;
            }
        }
        return $this->value;
    }
    
    public function setWithDisplayValue($displayValue) 
    {
        $value  = $this->getValueWithLabel($displayValue);
        if($value !== false)
            $this->setValue($value);
        else
            $this->setValue (null);
    }
    
    public function getLabelWithValue($value)
    {
        return $this->options[$value];
    }
    
    public function getValueWithLabel($label)
    {
        return array_search($label, $this->options);
    }
    

    public function hasOptions()
    {
        return true;
    }

    // Extend the default set value field and allow for name resolution
    // using the id's.
    /*public function setValue($value)
    {
        return $this->resolve($value);
    }*/

    public function getOptions()
    {
        return $this->options;
    }
}

