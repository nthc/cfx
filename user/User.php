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
        $role_id = $role_id===null?$_SESSION["role_id"]:$role_id;
        if($role_id==1)
        {
            return true;
        }
        else
        {
            $model = model::load("system.permissions");
            $data = $model->get(
                array(
                    "fields"=>array("value"),
                    "conditions"=>"role_id = $role_id AND permission='$permission'"
                ),
                Model::MODE_ASSOC,
                false,
                false
            );
            return $data[0]["value"];
        }
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
