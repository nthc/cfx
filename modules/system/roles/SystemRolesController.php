<?php
/**
 * Roles are groups of people with diferent sets of privileges. This class 
 * provides an extension to the ModelController which makes ot possible to edit 
 * the permissions of a particular role that users belong to. This class forms 
 * part of the user management system.
 * 
 * @author james
 */
class SystemRolesController extends ModelController
{
    private $id = 0;
    private $save;
    private $permissions;

    public function __construct()
    {
        parent::__construct(".roles");
        $this->table->addOperation("permissions", "Permissions");
    }
    
    public function flatenMenu($menu)
    {
        $flatened = array();
        foreach($menu as $item) 
        {
            $flatened[$item["path"]] = $item;
            if(count($item["children"]) > 0)
            {
                $flatened = array_merge($flatened, $this->flatenMenu($item["children"]));
            }
        }
        return $flatened;
    }

    /**
     * The method which displays the permissions so they could be setup. This 
     * method is called by the application and it performs recursive calls to 
     * the drawPermissions() method.
     * 
     * @param $params array Passes the role_id through an array.
     * @return String
     */
    public function permissions($params)
    {        
        if ($_POST["is_sent"] == "yes")
        {
            //Save the permission values
            $this->permissions = Model::load("system.permissions");
            $permissions = $_POST;
            array_pop($permissions);
            foreach($permissions as $permission => $value)
            {
                $this->permissions->delete("role_id = '{$params[0]}' AND permission = '$permission'");
                $this->permissions->setData(
                    array(
                        "role_id"     => $params[0], 
                        "permission" => $permission,
                        "value"         => $value[0],
                        "module"     => $value[1],
                    )
                );
                $this->permissions->save();
            }
                        
            //Generate a new side menu for this role
            $menu = $this->generateMenus($params[0]);
            $flatened = $this->flatenMenu($menu);
            $sideMenu = Controller::load( array(
                "system", "side_menu", "generate", serialize($menu)
            ));
            
            file_put_contents(
                "app/cache/menus/side_menu_{$params[0]}.html",
                $sideMenu->content
            );
            
            file_put_contents(
                "app/cache/menus/menu_{$params[0]}.object",
                serialize($flatened)     
            );
            
            User::log("Set permissions");//, $permissions);
        }

        $path = $params;
        array_shift($path);
        $accum = Application::$prefix . "/system/roles/permissions/{$params[0]}";
        $menu = "<a href='$accum'>Permissions</a>";
        
        foreach($path as $section)
        {
            $accum .= "/$section";
            $menu .= " > <a href='$accum'>".ucfirst($section)."</a>";
        }
        $path = implode("/", $path);
        
        $ret .= "<form class='fapi-form' method='post'>" . $menu . "<br/><hr/>"
             . $this->drawPermissions(
                      $this->getPermissionList("app/modules/$path","app/modules"),
                      $params[0]
               )
             . "<input type='hidden' name='is_sent' value='yes'/>"
             . $this->save
             . "</div></form>";
             
        Application::setTitle("Role Permissions");
        return $ret;
    }

    /**
     * A method called recursively to draw the permission tree. Each call to this
     * method causes ot to go through the menu for the purpose of generating
     * HTML representation of the permissions tree.
     *
     * @param $menu     array An array containing a list of all the modules for which 
     *                 	the tree should be generated
     * 
     * @param $roleId   integer The id for the role which is currently being processed
     * @param $level    integer The level of the tree
     * @return string
     */
    private function drawPermissions($menu, $roleId, $level = 0)
    {
        $this->id++;
        if(!is_array($menu)) 
        {
            return;
        }
        
        $ret = $ret . "<ul class='permission "
             . ($level==0?"root-permission":"sub-permission")
             . " permission-level-$level'  id='permission-{$this->id}'>";

        foreach($menu as $item)
        {
            $link = is_array($item["permissions"]) ? "<b>{$item["title"]}</b>" : "<a href='{$this->urlPath}/permissions/$roleId{$item["path"]}'>{$item["title"]}</a>";

            $ret = $ret . "<li $style $extra >$link";

            if(is_array($item["permissions"]))
            {
                $this->save = "<div id='fapi-submit-area'><input type='submit' value='Save'/>";
                $ret =$ret . "<div class='permission-box' style='padding-bottom:20px;width:500px'>
                                <table width='100%'>
                                    <thead><tr><td width='50%'></td><td><center>Enabled</center></td><td><center>Disabled</center></td></tr></thead>";
                foreach($item["permissions"] as $permission)
                {
                    $permissionName = str_replace(".", "_", $permission["name"]);
                    $enableChecked = User::getPermission($permissionName, $roleId) == true ? "checked='checked'" : "";
                    $disabledChecked = User::getPermission($permissionName, $roleId) == true ? "" : "checked='checked'";
                    
                    $ret .= "<tr>
                                <td>{$permission["label"]}</td>
                                <td><center><input type='radio' name='{$permission["name"]}[]' value='1' $enableChecked /></center></td>
                                <td>
                                    <center><input type='radio' name='{$permission["name"]}[]' value='0' $disabledChecked /></center>
                                    <input type='hidden' name='{$permission["name"]}[]' value = '{$item["path"]}' />
                                </td>
                             </tr>";
                }
                $ret .= "</table></div>";
            }

            if(count($item["children"]>0))
            {
                $ret = $ret 
                     . $this->drawPermissions($item["children"],$roleId,$level+1);
            }
            $ret = $ret . "</li>";
        }

        $ret = $ret . "</ul>";
        return $ret;
    }

    /**
     * This recursive method is called to generate a structured array representation
     * of the modules in the system. This helps to generate the permissions tree.
     * It individually loads every module and extracts the list of permissions
     * from it. The output from this method is passed to the drawPermissions
     * method for the purpose of generating the permissions tree.
     *
     * @param $path     The directory path where the modules are stored
     * @param $prefix     A prefix which should be removed from the path name when
     *                    generating the modules path which is to be used in the
     *                    Controller::load() method.
     * @return Array
     */
    private function getPermissionList($path,$prefix)
    {
        $redirected = false;
        
        if(file_exists($path . "/package_redirect.php"))
        {
            include $path . "/package_redirect.php";
            $originalPath = $path;
            $path = $redirect_path; 
            $d = dir($path);
            $redirected = true;
            $redirects = Cache::get("permission_redirects");
            if($redirects == null)
            {
                $redirects = array();    
            }
            $redirects[] = array(
                "from"  =>  $originalPath,
                "to"    =>  $path
            );
            Cache::add("permission_redirects", $redirects);
        }
        else
        {
            $redirects = Cache::get("permission_redirects");
            if(is_array($redirects))
            {
                foreach($redirects as $redirect)
                {
                    if(substr_count($path, $redirect["from"]) > 0)
                    { 
                        $redirected = true;
                        $originalPath = $path;
                        $path = str_replace($redirect["from"], $redirect["to"], $path);
                        break;
                    }
                }
            }
            $d = dir($path);
        }
        
        $list = array();
        while (false !== ($entry = $d->read()))
        {
            if($entry != "." && $entry != ".."  && is_dir("$path/$entry"))
            {
                if($redirected)
                {
                    $urlPath = substr("$originalPath/$entry",strlen($prefix));
                    $modulePath = explode("/", substr(substr("$originalPath/$entry", strlen($prefix)), 1));
                    $module = Controller::load($modulePath, false);
                }
                else
                {
                    $urlPath = str_replace("//", "/", substr("$path/$entry",strlen($prefix)));
                    $modulePath = explode("/", substr(substr("$path/$entry", strlen($prefix)), 1));
                    if($modulePath[0] == '') array_shift($modulePath);
                    $module = Controller::load($modulePath, false);
                }
                
                if($module->showInMenu())
                {
                    $permissions = $module->getPermissions();
                    $list[] = array(
                        "title"          => ucwords(str_replace("_", " ", $entry)), 
                        "path"          => $urlPath, 
                        "children"      => $children, 
                        "permissions" => $permissions
                    );
                }
            }
        }
        array_multisort($list, SORT_ASC);
        return $list;
    }

    /**
     * This recursive method is called to generate the HTML representation of
     * the cached menu that is shown in the menu pane whenever anyone with the
     * associated role id logs in. In the generation of the menu controllers
     * to which the user has no permissions are ignored.
     *
     * @param $roleId     	string 		The id of the role for which the menu is being generated
     * @param $path     	string 		The path of the modules for which the menus is to be
     *	                     			generated.
     *
     * @return String
     */
    private function generateMenus($roleId, $path="app/modules")
    {
        $prefix = "app/modules";
        //$d = dir($path);
        
        if(file_exists($path . "/package_redirect.php"))
        {
            include $path . "/package_redirect.php";
            $originalPath = $path;
            $path = $redirect_path; 
            $d = dir($path);
            $redirected = true;
            $redirects = Cache::get("permission_redirects");
            if($redirects == null)
            {
                $redirects = array();   
            }
            $redirects[] = array(
                "from"  =>  $originalPath,
                "to"    =>  $path
            );
            Cache::add("permission_redirects", $redirects);
        }
        else
        {
            $redirects = Cache::get("permission_redirects");
            if(is_array($redirects))
            {
                foreach($redirects as $redirect)
                {
                    if(substr_count($path, $redirect["from"]) > 0)
                    { 
                        $redirected = true;
                        $originalPath = $path;
                        $path = str_replace($redirect["from"], $redirect["to"], $path);
                        break;
                    }
                }
            }
            $d = dir($path);
        }        
        
        $list = array();
        
        // Go through every file in the module directory
        while (false !== ($entry = $d->read()))
        {
            // Ignore certain directories
            if ($entry != "." && $entry != ".." && is_dir("$path/$entry"))
            {
                if($redirected)
                {
                    $urlPath = substr("$originalPath/$entry",strlen($prefix));
                        $modulePath = substr("$originalPath/$entry", strlen($prefix)
                    );
                    
                    $this->permissions->queryResolve = true;
                    $value = $this->permissions->get(array("conditions"=>"roles.role_id='$roleId' AND module = '{$modulePath}' AND value='1'"));
                    $children = $this->generateMenus($roleId, "$originalPath/$entry");
                }
                else
                {
                    $urlPath = substr("$path/$entry",strlen($prefix));
                        $modulePath = substr("$path/$entry", strlen($prefix)
                    );
                        
                    $this->permissions->queryResolve = true;
                    $value = $this->permissions->get(array("conditions"=>"roles.role_id='$roleId' AND module = '{$modulePath}' AND value='1'"));
                    $children = $this->generateMenus($roleId, "$path/$entry");
                }
                
                if(file_exists("app/modules/" . $modulePath . "/package.xml"))
                {
                    $xml = simplexml_load_file("app/modules/" . $modulePath . "/package.xml");
                    $label = (string)reset($xml->xpath("/package:package/package:label"));
                    $icon = (string)reset($xml->xpath("/package:package/package:icon"));
                }
                else
                {
                    $label = "";
                    $icon = "";
                }
                
                if(count($children)>0 || count($value)>0)
                {
                    $list[] = array(
                        "title"        => $label==''?ucwords(str_replace("_", " ", $entry)):$label,
                        "path"      => $urlPath,
                        "children"  => $children,
                        "icon"      => $icon
                    );
               }
            }
        }
        array_multisort($list,SORT_ASC);
        return $list;
    }
}
