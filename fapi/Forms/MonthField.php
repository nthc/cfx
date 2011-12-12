<?php
//! The MonthField is a SelectionList which is used to show a list of
//! months.
//! \ingroup Forms
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
?>
