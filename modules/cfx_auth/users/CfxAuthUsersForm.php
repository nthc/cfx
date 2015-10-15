<?php

class CfxAuthUsersForm extends Form{
    
    public function __construct()
    {
        parent::__construct();
        $this->add(
            Element::create("TextField", "Username", "user_name"),
            Element::create("TextField", "Firstname", "first_name"),
            Element::create("TextField", "Lastname", "last_name"),
            Element::create("TextField", "Othernames", "other_names"),
            Element::create("TextField", "Email", "email"),
            Element::create("HiddenField", "password")
        );
        $this->addAttribute("style", "width:450px");
    }
    
}
