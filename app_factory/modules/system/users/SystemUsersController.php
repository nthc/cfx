<?php
class SystemUsersController extends ModelController 
{
	public $listFields = array(
	    "system.users.user_id",
	    "system.users.user_name",
	    "system.users.first_name",
	    "system.users.last_name",
	    "system.roles.role_name"
	);
	
	public $modelName = "system.users";
}