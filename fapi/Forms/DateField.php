<?php
/**
 * A special field for accepting dates from the user. This field ensures
 * that the date entered is a valid date.
 * @author ekow
 * @ingroup Forms
 */
class DateField extends TextField
{
    public $hasTime = false;

    public function __construct($label="",$name="",$description="")
    {
        parent::__construct($label,$name,$description);
    }

    public function getDisplayValue()
    {
        $format = $this->hasTime ? "jS F, Y H:i:s" : "jS F, Y";
        return $this->getValue()!==""?date($format ,(int)$this->getValue()):"";
    }

    public function render()
    {
        $format = $this->hasTime ? "d/m/Y H:i:s" : "d/m/Y";
        $this->addCSSClass( "fapi-textfield");
        $this->addAttribute( "class" , "fapi-sidefield ".$this->getCSSClasses());
        $this->addAttribute( "id" , $this->getId());
        $this->addAttribute( "name" , $this->getName());
        $this->addAttribute( "value" , $this->getValue()!=="" && $this->getValue()!==false ? date($format ,(int)$this->getValue()) : $_REQUEST[$this->getName()]);
        $id = $this->getId();
        return "<input ".$this->getAttributes()." /><input class='fapi-sidebutton' type='button' value='..' onclick=\"$('#date-picker-$id').datepicker({altField:'#$id',changeYear:true,changeMonth:true,changeDate:true,maxDate:null,yearRange:'1900:2300',dateFormat:'dd/mm/yy'}).toggle()\" /><div class='fapi-datepicker' id='date-picker-$id'></div>";
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
