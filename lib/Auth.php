<?php

class Auth
{    
    
    private static $permissionsModel;
    
    public static function getPermission($permission,$role_id=null,$user_id=null)
    {
        if($role_id === null)
        {
            $user_id = $user_id == null?$_SESSION['user_id']:$user_id;
            $userRoleModel = model::load("auth.users_roles");
            $usersRolesData = $userRoleModel->get(
                array(
                    "filter"=>"user_id =?",
                    "bind"=>array($user_id)
                ),
                Model::MODE_ASSOC,
                false,
                false
            );

            foreach ($usersRolesData as $rolesData)
            {
                if(User::defaultGetPermission($permission, $rolesData['role_id']))
                {
                    //if any of the permissions for that role exists return true to calling function
                    return true;
                }
            }

            //return false to calling function, if none of the permissions returns true 
            return false;
        }
        else
        {
            return User::defaultGetPermission($permission, $role_id);
        }
    }
}