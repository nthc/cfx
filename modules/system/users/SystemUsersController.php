<?php
class SystemUsersController extends ModelController 
{
	public $listFields = array(
	    ".users.user_id",
	    ".users.user_name",
	    ".users.first_name",
	    ".users.last_name",
	    ".roles.role_name"
	);
	
	public $modelName = ".users";
}