<?php
//! The PasswordField is a TextField which obscures the data in it. It is
//! used for recieving and displaying passwords. The value it returns 
//! on submission is the MD5 check sum of the actual password.
//! \ingroup Forms
class PasswordField extends TextField
{
    protected $md5 = true;
    
    public function __construct($label="",$name="",$description="")
    {
        parent::__construct($label,$name,$description);
        $this->setAttribute("type","password");
    }
    
    public function setEncrypted($encrypted)
    {
        $this->md5 = $encrypted;
        return $this;
    }
    
    public function getData($storable=false)
    {
        parent::getData();
        if($this->md5)
        {
            if($this->getValue()!="") $this->setValue(md5($this->getValue()),false);
        }
        return array($this->getName(false) => $this->getValue());
    }
    

    /*public function render()
    {
        $this->addAttribute("class","fapi-textfield ".$this->getCSSClasses());
        $this->addAttribute("name",$this->getName());
        $this->addAttribute("id",$this->getId());
        return "<input {$this->getAttributes()} />"; //class="fapi-textfield '.$this->getCSSClasses().'" type="text" name="'.$this->getName().'" id="'.$this->getId().'" value="'.$this->getValue().'" />';
    }*/
    
    public function getDisplayValue()
    {
        return "This field cannot be viewed";
    }
}
?>
