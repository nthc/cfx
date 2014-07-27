<?php
/**
 * A simple button which can be displayed on forms.
 * @ingroup Forms
 */
class Button extends Field
{
    public function __construct($label="")
    {
        $this->setLabel($label);
        $this->addAttribute("type","button");
    }

    public function render()
    {
        if($this->getLabel()!="")
        {
            $this->addAttribute("value", $this->getLabel());
        }
        $this->addAttribute("class","fapi-button");
        $this->addAttribute("id",$this->getId());
        return "<input ".$this->getAttributes()."/>";
    }

    public function getType()
    {
        return __CLASS__;
    }
}
