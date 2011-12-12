<?php
class LogoContent extends ReportContent
{
    public $image;
    public $address = array();
    public $title;
    
    public function setAddress($address)
    {
        if(is_array($address))
        {
            $this->address = $address;
        }
    }
    
    public function getType()
    {
        return "logo";
    }
}
