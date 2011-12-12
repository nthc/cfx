<?php
include_once "Field.php";
//! A SubmitButton. This button is responsible for submitting the form.
//! You may not need to use this in your forms as they are automaticallu
//! added for you.
//! \ingroup Forms
class SubmitButton extends Button
{
    public function __construct($label="")
    {
        parent::__construct($label);
    }

    public function render()
    {
        $this->setAttribute("type","submit");
    }

    public function getType()
    {
        return __CLASS__;
    }
}
?>
