<?php
class SystemApiKeysForm extends Form
{
    public function __construct()
    {
        parent::__construct();
        $this->add(
            Element::create('ModelField', 'system.users.user_id', 'user_name'),
            Element::create('TextField', 'API Key', 'key')->addAttribute('disabled', 'disabled'),
            Element::create('TextField', 'Secret', 'secret')->addAttribute('disabled', 'disabled')
        );
    }
}