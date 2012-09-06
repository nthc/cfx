<?php
class SystemUsersModel extends ORMSQLDatabaseModel
{
    public $database = '.users';
    
    public $references = array(
        "role_id" => array(
            "reference"         =>  ".roles.role_id",
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
