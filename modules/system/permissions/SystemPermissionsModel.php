<?php
class SystemPermissionsModel extends ORMSQLDatabaseModel
{
	public $database = ".permissions";
	
	public $showInMenu = "false";
	
	public $references = array(
        "role_id" => array(
            "reference"         =>  ".roles.role_id",
            "referenceValue"    =>  "role_name"
        )
    );
}