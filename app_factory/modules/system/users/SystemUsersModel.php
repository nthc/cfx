<?php
class SystemUsersModel extends ORMSQLDatabaseModel
{
    public $database = 'common.users';
    
    public $references = array(
        "role_id" => array(
            "reference"         =>  "system.roles.role_id",
            "referenceValue"    =>  "role_name"
        )
    );
    
    public function preValidateHook()
    {
        if($this->datastore->data["password"]=="")
        {
            $this->datastore->data["password"] = md5($this->datastore->data["user_name"]);
        }
    }
    
    public function preAddHook()
    {
        $this->datastore->data["user_status"] = 2;
    }    
}
