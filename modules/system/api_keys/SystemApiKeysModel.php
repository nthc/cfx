<?php

class SystemApiKeysModel extends ORMSQLDatabaseModel
{
    public $database = 'system.api_keys';
    
    public function preValidateHook() 
    {
        $this->datastore->data['active'] = '1';
        $this->datastore->data['key'] = md5($this->datastore->data['user_id']) . time();
        $this->datastore->data['secret'] = 
            sha1(uniqid()) . 
            sha1($this->datastore->data['key']) . 
            sha1(time());
    }
}
