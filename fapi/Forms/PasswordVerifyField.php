<?php
/**
 * The PasswordVerifyField for getting two passwords and verifying them.
 *
 */
class PasswordVerifyField extends Field
{
    protected $passwordField1;
    protected $passwordField2;
    protected $container;
    
    public function __construct($name="")
    {
        $this->setName($name);
        $container = new BoxContainer();
        $passwordField1 = new PasswordField("Password","password_1","The password you want to be associated with your account.");
        $container->add($passwordField1);
        $passwordField2 = new PasswordField("Retype-Password","password_2","Retype the password you entered above");
        $container->add($passwordField2);
    }
    
    public function render()
    {
        $container->render();
    }
    
    public function getData($storable=false)
    {
        if($this->getMethod()=="POST")
        {
            return array($this->getName()=>$this->getValue());
        }
    }
    
    public function validate()
    {
        
    }
}
?>