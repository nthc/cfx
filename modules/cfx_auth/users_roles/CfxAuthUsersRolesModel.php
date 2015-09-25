<?php

class CfxAuthUsersRolesModel extends ORMSQLDatabaseModel
{
    public $database = 'auth.users_roles';
    public $showInMenu = 'false';

    public $references = array(
       "role_id" => array(
           "reference"         =>  "system.roles.role_id",
           "referenceValue"    =>  "role_name"
       ),

       "user_id" => array(
           "reference"         =>  "system.users.user_id",
           "referenceValue"    =>  "user_name"
       ),
    );    
}