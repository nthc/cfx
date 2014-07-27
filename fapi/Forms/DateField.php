<?php
/**
 * A special field for accepting dates from the user. Although this field uses a javascript
 * pop-up to allow a much more interactive way for capturing dates from the 
 * user, users can also type in their dates. If dates are entered in a wrong
 * format, an internal validation ensures that the user is prompted. The date
 * field can optionally capture and display time information along with the
 * date. The format accepted by this field is 'd/m/y h:m:s'.
 * 
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 * @ingroup Forms
 */
class DateField extends TextField
{
    /**
     * A flag which determines whether this field has time data attached or not.
     * @var boolean
     */
    protected $hasTime = false;

    /**
     * Creates a new DateField
     * @param type $label The label for the date field
     * @param type $name The name of the field
     * @param type $description A brief description of the field
     */
    public function __construct($label="",$name="",$description="")
    {
        parent::__construct($label,$name,$description);
    }

    public function getDisplayValue()
    {
        $format = $this->hasTime ? "d/m/Y H:i:s" : "d/m/Y";
        return $this->getValue()!==""?date($format ,(int)$this->getValue()):"";
    }
    
    public function setWithDisplayValue($value) 
    {
        $this->setValue($value);
    }

    public function render()
    {
        $format = $this->hasTime ? "d/m/Y H:i:s" : "d/m/Y";
        $this->addCSSClass("fapi-textfield");
        $this->addAttribute("class" , "auto-kal ".$this->getCSSClasses());
        $this->addAttribute("data-kal", "format: 'DD/MM/YYYY'");
        $this->addAttribute("id" , $this->getId());
        $this->addAttribute("name" , $this->getName());
        $this->addAttribute("value" , $this->getValue()!=="" && $this->getValue()!==false ? date($format ,(int)$this->getValue()) : $_REQUEST[$this->getName()]);
        return "<input ".$this->getAttributes()." />";
    }

    private function dateToTimestamp($date)
    {
        $decompose = explode("/",$date);
        $ret = strtotime("{$decompose[2]}-{$decompose[1]}-{$decompose[0]}");
        return $ret;
    }

    private function timeToStamp($time)
    {
        $decompose = explode(":", $time);
        return $decompose[0] * 3600 + $decompose[1] * 60 + $decompose[2];
    }

    /**
     * Set the hasTime flag.
     * @param type $value
     * @return DateField
     */
    public function setHasTime($value)
    {
        $this->hasTime = $value;
        return $this;
    }

    public function setValue($value)
    {
        if(is_numeric($value) && $_REQUEST[$this->getName()] != $value)
        {
            parent::setValue($value);
        }
        else
        {
            if(preg_match("/(\d{2})\/(\d{2})\/(\d{4})(\w\d{2}:\d{2}:\d{2})?/", $value) == 0 && $value != '')
            {
                parent::setValue(false);
                return $this;
            }
            
            if(strlen($value)>0)
            {
                if($this->hasTime)
                {
                    $parts = explode(" ",$value);
                    $datePart = $this->dateToTimestamp($parts[0]);
                    $timePart = $this->timeToStamp($parts[1]);
                    parent::setValue($datePart + $timePart);
                }
                else
                {
                    parent::setValue($this->dateToTimestamp($value));
                }
            }
            else
            {
                parent::setValue("");
            }
        }
        return $this;
    }
}
