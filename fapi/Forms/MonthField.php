<?php
/**
 * A field which lists the months in the year. The values associated with these
 * months are the numbers which correspond with the position of the month in the
 * annual calendar.
 * 
 * @package wyf.fapi 
 * @author James Ainooson <jainooson@gmail.com>
 */
class MonthField extends SelectionList
{
    public function __construct($label="",$name="",$descriptiom="")
    {
        parent::__construct($label,$name,$description);
        $this->addOption("January","01");
        $this->addOption("February","02");
        $this->addOption("March","03");
        $this->addOption("April","04");
        $this->addOption("May","05");
        $this->addOption("June","06");
        $this->addOption("July","07");
        $this->addOption("August","08");
        $this->addOption("September","09");
        $this->addOption("October","10");
        $this->addOption("November","11");
        $this->addOption("December","12");
    }
}