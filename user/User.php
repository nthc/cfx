<?php
/**
 * A class for working with data associated to the users.
 * 
 */
class User
{
    /**
     * Logs an activity.
     * @param string $activity
     * @param string $data
     */
    public static function log($activity,$data=null)
    {
        $db = Db::get();
        $data = Db::escape(json_encode($data));
                
        if(ENABLE_AUDIT_TRAILS === true && class_exists("SystemAuditTrailModel", false))
        {
            SystemAuditTrailModel::log(
                array(
                    'item_id' => 0,
                    'item_type' => 'system_activity',
                    'description' => $activity,
                    'type' => SystemAuditTrailModel::AUDIT_TYPE_SYSTEM
                )
            );
        }
    }

    /**
     * Checks whether the user has the permission to perform a particular action.
     *
     * @param string $permission The permission to be tested
     * @param integer $role_id The role for which the permission should be tested
     * 
     * @return boolean
     */
    public static function getPermission($permission,$role_id=null)
    {
        //the value to return to the calling function
        $returnValue = null;
        
        //check if USER_PERMISSION constant is defined, if it is call the modified permission method 
        //else call the default method
        if(defined('USER_PERMISSION'))
        {
            //Check if the permission exists and return a value
            if(Auth::getPermission($permission, $role_id))
            {
                $returnValue = true;
            }
            else
            {
                $returnValue = false;
            }
        }
        else
        {
            if(Auth::defaultGetPermission($permission, $role_id))
            {
                $returnValue = true;
            }
            else
            {
                $returnValue = false;
            }
        }
        return $returnValue;
    }

    /**
     * 
     * @param <type> $module
     * @param <type> $role_id
     */
    public static function getAccess($module,$role_id=null)
    {
        $role_id = $role_id==null?$_SESSION["role_id"]:$role_id;

    }
}
