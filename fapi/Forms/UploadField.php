<?php
/**
 * 
 * @author James Ainooson
 * @ingroup Forms
 */
class UploadField extends Field
{
    public function __construct($label="",$name="",$description="",$value="",$destinationFile="")
    {
        Field::__construct($name,$value);
        Element::__construct($label, $description);
        $this->addAttribute("type","file");
        $this->hasFile = true;
    }
    
    public function render()
    {
        $this->setAttribute("id",$this->getId());
        $this->addAttribute("name",$this->getName());
        $attributes = $this->getAttributes();
        $ret .= "<input $attributes type='file' class='fapi-fileupload' />";
        return $ret;
    }
}
