<?php
include_once("TextField.php");
/**
 * A text field for accepting email addresses. This field validates
 * the email addresses using a regular expression.
 * @ingroup Forms
 */
class EmailField extends TextField
{
    public function __construct($label="",$name="",$description="",$value="")
    {
        parent::__construct($label,$name,$description,$value);
        $this->setRegExp('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/');
    }
    
    public function validate()
    {
        if(!parent::validate())
        {
            array_push($this->errors, "Invalid email address entered");
            $this->error = true;
            return false;
        }
        else
        {
            return true;
        }
    }
}
?>
