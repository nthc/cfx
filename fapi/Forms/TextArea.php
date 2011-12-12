<?php
//! A special field for accepting multiline text input.
//! \ingroup Forms
class TextArea extends Field
{
    public function __construct($label="",$name="",$description="")
    {
        $this->setLabel($label);
        $this->setName($name);
        $this->setDescription($description);
    }

    public function render()
    {
        $this->addAttribute('id', $this->getId());
        return "<textarea ".$this->getAttributes()." class='fapi-textarea ".$this->getCSSClasses()."' name='".$this->getName()."'>".$this->getValue()."</textarea>";
    }
}
?>
