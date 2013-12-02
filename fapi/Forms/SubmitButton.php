<?php
include_once "Field.php";
//! A SubmitButton. This button is responsible for submitting the form.
//! You may not need to use this in your forms as they are automaticallu
//! added for you.
//! \ingroup Forms
class SubmitButton extends Button
{
    public function __construct($label="", $value = "")
    {
        parent::__construct($label);
    }

    public function render()
    {
        $this->setAttribute("type","submit");
        return parent::render();
    }

    public function getType()
    {
        return __CLASS__;
    }
}
?>
