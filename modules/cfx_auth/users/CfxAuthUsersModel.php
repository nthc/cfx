<?php

class CfxAuthUsersModel extends ORMSQLDatabaseModel{

    public $database = 'auth.users';
    
    public $references = array(
        "role_id" => array(
            "reference"         =>  "auth.roles.role_id",
            "referenceValue"    =>  "role_name"
        )
    );
    
    public function preValidateHook()
    {
        if($this->datastore->data["password"]=="")
        {
            $this->datastore->data["password"] = md5($this->datastore->data["user_name"]);
        }
        unset($this->datastore->data['user_id']);
    }
    
    public function preAddHook()
    {
        $this->datastore->data["user_status"] = 2;
    }

}
