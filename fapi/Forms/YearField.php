<?php

class YearField extends SelectionList
{
    public function __construct($label="",$name="",$descriptiom="")
    {
        parent::__construct($label,$name,$description);
        for($i = 1900; $i < 2200; $i++)
        {
            $this->addOption($i);
        }
    }    
}
