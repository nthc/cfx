<?php


class CfxAuthRolesController extends ModelController{

  public $modelName = "auth.roles";
  
    public function __construct() {
        parent::__construct("common.roles");
        $this->table->addOperation("permissions", "Permissions");
        //$this->table->addOperation("constraints", "Set Constraints");
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
     * @param $params Passes the role_id through an array.
     * @return String
     */
    public function permissions($params)
    {        
        if ($_POST["is_sent"] == "yes")
        {
            self::insertIntoRoleValidity($params);
            
            //Save the permission values
            $this->permissions = Model::load("system.permissions");
            $permissions = $_POST;
            array_pop($permissions);
            
            foreach($permissions as $permission => $value)
            {
                $this->permissions->delete("role_id = '{$params[0]}' AND permission = '$permission'");
                $this->permissions->setData(array(
                    "role_id"     => $params[0], 
                    "permission" => $permission,
                    "value"         => $value[0],
                    "module"     => $value[1],
                ));
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
            
            User::log("Set permissions");
        }

        $path = $params;
        array_shift($path);
        $accum = "/system/roles/permissions/{$params[0]}";
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
     * @param $menu     An array containing a list of all the modules for which 
     *                     the tree should be generated
     * 
     * @param $roleId     The id for the role which is currently being processed
     * @param $level     The level of the tree
     * @return String
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
                    $urlPath = substr(
                        Application::$prefix, 0, 
                        strlen(Application::$prefix)-1) . 
                        substr("$originalPath/$entry",strlen($prefix));
                    $modulePath = explode("/", substr(substr("$originalPath/$entry", strlen($prefix)), 1));
                    $module = Controller::load($modulePath, false);
                }
                else
                {
                    $urlPath = substr(
                        Application::$prefix, 0, 
                        strlen(Application::$prefix)-1) . 
                        substr("$path/$entry",strlen($prefix));
                    $modulePath = explode("/", substr(substr("$path/$entry", strlen($prefix)), 1));
                    $module = Controller::load($modulePath, false);
                }
                                
                if($module->showInMenu())
                {
                    //$children = $this->getPermissionList("$path/$entry", $prefix);
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
     * @param $roleId     The id of the role for which the menu is being generated
     * @param $path     The path of the modules for which the menus is to be
     *                     generated.
     * @return String
     */
    private function generateMenus($roleId,$path="app/modules")
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
                    $urlPath = substr(
                        Application::$prefix, 0, 
                        strlen(Application::$prefix)-1) . 
                        substr("$originalPath/$entry",strlen($prefix));
                    $modulePath = substr("$originalPath/$entry", strlen($prefix));
                    $value = $this->permissions->get(array("conditions"=>"roles.role_id='$roleId' AND module = '{$modulePath}' AND value='1'"));
                    $children = $this->generateMenus($roleId, "$originalPath/$entry");
                }
                else
                {
                    $urlPath = substr(
                        Application::$prefix, 0, 
                        strlen(Application::$prefix)-1) . 
                        substr("$path/$entry",strlen($prefix));
                    $modulePath = substr("$path/$entry", strlen($prefix));
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

    public function constraints($params)
    {
         //Load necessary Models
         $roleModel = Model::load('auth.roles');
         $constraintModel = Model::load('auth.constraints');

         $role = $roleModel->getWithField('role_id', $params[0]);
         $constraints = $constraintModel->getWithField("role_id", "$params[0]");

         //Label at the top of the inner template
         $this->label = "Login Constraints for the '".$role[0]['role_name']."' role";

         //create a new form
         $form = new Form();
         $form->setRenderer("default");

         //Fieldset to group days of week checkboxes
         $daysFieldset = Element::create('ColumnContainer', 7);

         //create checkbox fields and set their respective values
         $mon = Element::create("Checkbox","Monday","mon","","1");
         $mon->setValue(($constraints[0]['days_of_week_value'] & 1) == 1? "1":"0");
         $daysFieldset->add($mon);

         $tue = Element::create("Checkbox","Tuesday","tue","","2");
         $tue->setValue(($constraints[0]['days_of_week_value'] & 2) == 2? "2":"0");
         $daysFieldset->add($tue);

         $wed = Element::create("Checkbox","Wednesday","wed","","4");
         $wed->setValue(($constraints[0]['days_of_week_value'] & 4) == 4? "4":"0");
         $daysFieldset->add($wed);

         $thu = Element::create("Checkbox","Thursday","thu","","8");
         $thu->setValue(($constraints[0]['days_of_week_value'] & 8) == 8? "8":"0");
         $daysFieldset->add($thu);

         $fri = Element::create("Checkbox","Friday","fri","","16");
         $fri->setValue(($constraints[0]['days_of_week_value'] & 16) == 16? "16":"0");
         $daysFieldset->add($fri);

         $sat = Element::create("Checkbox","Saturday","sat","","32");
         $sat->setValue(($constraints[0]['days_of_week_value'] & 32) == 32? "32":"0");
         $daysFieldset->add($sat);

         $sun = Element::create("Checkbox","Sunday","sun","","64");
         $sun->setValue(($constraints[0]['days_of_week_value'] & 64) == 64? "64":"0");
         $daysFieldset->add($sun);

         $form->add($daysFieldset);

         //FieldSet to group beginning time section
         $beginningTimeFieldset = Element::create('ColumnContainer', 2);

         //Starting times
         $timeRangeStartFieldSet = Element::create("FieldSet","Beginning Time");

         $hourStartSelection = Element::create("SelectionList","Hour","hour_start");

         //add all options to the hour start selection field
         for($i = 0;$i <= 23; $i++)
         {
             $hourStartSelection->addOption(sprintf("%02d", $i));
         }

         //add hour start to begin time range field set
         $beginningTimeFieldset->add($hourStartSelection);

         $minuteStartSelection = Element::create("SelectionList","Minutes","minutes_start");

         //add all options to the minute start selection field
         for($i = 0;$i <= 59; $i++)
         {
             $minuteStartSelection->addOption(sprintf("%02d", $i));
         }

         //add minute start to begin time range field set
         $beginningTimeFieldset->add($minuteStartSelection)->addAttribute('style', 'width:30%');

         //Add the column container fieldset to the main fieldset
         $timeRangeStartFieldSet->add($beginningTimeFieldset);


         $endingTimeFieldset = Element::create('ColumnContainer', 2);

         //Ending times
         $timeRangeEndFieldSet = Element::create("FieldSet","Ending Time");

         $hourEndSelection = Element::create("SelectionList","Hour","hour_end");

          //add all options to the hour end selection field
         for($i = 0;$i <= 23; $i++)
         {
             $hourEndSelection->addOption(sprintf("%02d", $i));
         }

         $endingTimeFieldset->add($hourEndSelection);

         $minuteEndSelection = Element::create("SelectionList","Minutes","minutes_end");

          //add all options to the minute end selection field
         for($i = 0;$i <= 59; $i++)
         {
             $minuteEndSelection->addOption(sprintf("%02d", $i));
         }

         $endingTimeFieldset->add($minuteEndSelection)->addAttribute('style', 'width:30%');

         //Add the column container fieldset to the main fieldset
         $timeRangeEndFieldSet -> add($endingTimeFieldset);

         $modeSelection = new SelectionList("Mode","mode");
         $modeSelection->addOption("Allow","allow");
         $modeSelection->addOption("Deny","deny");


         $roleIdHiddenField = Element::create("HiddenField","role_id",$params[0]);

         //populate fields with values from database if the data for that partular role already exists
         if($constraints[0] != null)
         {
             $hourStartSelection->setValue(substr($constraints[0]['time_range_start'],0,2));
             $hourEndSelection->setValue(substr($constraints[0]['time_range_end'],0,2));

             $minuteStartSelection->setValue(substr($constraints[0]['time_range_start'],3));
             $minuteEndSelection->setValue(substr($constraints[0]['time_range_end'],3));

             $modeSelection->setValue($constraints[0]['mode']);
         }

         //Add components to the form
         $form->add($timeRangeStartFieldSet);
         $form->add($timeRangeEndFieldSet);
         $form->add($modeSelection);
         $form->add($roleIdHiddenField);
         $form->setValidatorCallback("{$this->getClassName()}::constraint_callback");
         $form->setShowClear(false);

         //render the form
         return $form->render();

     }

     public static function constraint_callback($data, $form)
     {
         if($data['hour_start'] == '' || $data['hour_end'] == '')
         {
             $errors[] = "The Hour fields cannot be empty";
         }

         if($data['minutes_start'] == '' || $data['minutes_end'] == '')
         {
             $errors[] = "The Minutes fields cannot be empty";
         }

         if($data['mode'] == '')
         {
             $errors[] = "The Mode field cannot be empty";
         }

         foreach($errors as $error)
         {
             $form->addError($error);
         }

         //if there are errors, return to form with the errors
         if(!empty($errors))
         {
             return true;
         }

         $constraintModel = Model::load('auth.constraints');

         $constraints = $constraintModel->getWithField('role_id', $data['role_id']);

         //merge all inputs into variables
         //counter to exit the loop after the days of the week are done
         $count = 1;
         $days_of_week_value = 0;
         foreach($data as $d)
         {
             if((int)$d != 0)
             {
                 $days_of_week_value = $days_of_week_value | (int)$d;
             }
             ++$count;
             if($count == 8)
             {
                 break;
             }
         }

         //concatenate times
         $time_range_start = $data['hour_start'].":".$data['minutes_start'];
         $time_range_end = $data['hour_end'].":".$data['minutes_end'];

         //If the constraint does not exist for the role, add a new constraint
         if(count($constraints) == 0)
         {
             $constraintModel->setData(array(
             'days_of_week_value' => $days_of_week_value,
             'time_range_start' => $time_range_start,
             'time_range_end' => $time_range_end,
             'mode' => $data['mode'],
             'role_id' => $data['role_id'],
             'time' => time()
             ));
             $constraintModel->save();

             if(empty($errors))
             {
                 Application::redirect("/auth/roles?notification=Contraint added successfully");
             }
         }
         //else if the constraint exists, update it
         else
         {
             $constraints[0]['days_of_week_value'] = $days_of_week_value;
             $constraints[0]['time_range_start'] = $time_range_start;
             $constraints[0]['time_range_end'] = $time_range_end;
             $constraints[0]['mode'] = $data['mode'];
             $constraints[0]['role_id'] = $data['role_id'];
             $constraints[0]['time'] = time();

             $constraintModel->setData($constraints[0]);
             $constraintModel->update("role_id",$data['role_id']);

             if(empty($errors))
             {
                 Application::redirect("/auth/roles?notification=Contraint updated successfully");
             }
         }
         return true;
     }

     private static function insertIntoRoleValidity($params)
     {
          $roleValidityModel = Model::load('auth.role_validity');

          $roleValidityData = $roleValidityModel->getWithField("role_id", $params[0]);

          if(count($roleValidityData) > 0)
          {
              $roleValidityData[0]["last_modified"] = time();
              $roleValidityModel->setData($roleValidityData[0]);
              $roleValidityModel->update("role_id", $params[0]);
          }

          else
          {
              $roleValidityModel ->setData(array(
                  'role_id' => $params[0],
                  'last_modified' => time()
              ));

              $roleValidityModel->save();
          }



     }
}
