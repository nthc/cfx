<?php

class AuthMenu
{
    private static $permissionsModel;
    
    public static function generate($userId)
    {
        $usersRolesModel = Model::load("auth.users_roles")->setQueryResolve(false);        
        $roles = $usersRolesModel->getWithField2('user_id', $userId);
        self::$permissionsModel = Model::load('system.permissions');
        $menu = [];
        
        foreach($roles as $role) {
            $menu = self::mergeMenus($menu, self::generateMenus($role['role_id']));
        }
        
        $flatened = self::flatenMenu($menu);
        $sideMenu = Controller::load( array(
            "system", "side_menu", "generate", serialize($menu)
        ));

        file_put_contents(
            "app/cache/menus/side_menu_u{$userId}.html",
            $sideMenu->content
        );

        file_put_contents(
            "app/cache/menus/menu_u{$userId}.object",
            serialize($flatened)     
        );        
    }
    
    private static function mergeMenus($menu1, $menu2)
    {
        foreach($menu2 as $key => $value)
        {
            if(isset($menu1[$key]))
            {
                $menu1[$key]['children'] = self::mergeMenus($menu1[$key]['children'], $menu2[$key]['children']);
            }
            else
            {
                $menu1[$key] = $menu2[$key];
            }
        }
        return $menu1;
    }
    
    private static function generateMenus($roleId, $path = "app/modules")
    {
        $prefix = "app/modules";
        //$d = dir($path);

        if (file_exists($path . "/package_redirect.php")) {
            include $path . "/package_redirect.php";
            $originalPath = $path;
            $path = $redirect_path;
            $d = dir($path);
            $redirected = true;
            $redirects = Cache::get("permission_redirects");
            if ($redirects == null) {
                $redirects = array();
            }
            $redirects[] = array(
                "from" => $originalPath,
                "to" => $path
            );
            Cache::add("permission_redirects", $redirects);
        } else {
            $redirects = Cache::get("permission_redirects");
            if (is_array($redirects)) {
                foreach ($redirects as $redirect) {
                    if (substr_count($path, $redirect["from"]) > 0) {
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
        while (false !== ($entry = $d->read())) {
            // Ignore certain directories
            if ($entry != "." && $entry != ".." && is_dir("$path/$entry")) {

                if ($redirected) {
                    $urlPath = substr(
                        Application::$prefix, 0, strlen(Application::$prefix) - 1) .
                        substr("$originalPath/$entry", strlen($prefix));
                    $modulePath = substr("$originalPath/$entry", strlen($prefix));
                    $value = self::$permissionsModel->get(array("conditions" => "roles.role_id='$roleId' AND module = '{$modulePath}' AND value='1'"));
                    $children = self::generateMenus($roleId, "$originalPath/$entry");
                } else {
                    $urlPath = substr(
                                    Application::$prefix, 0, strlen(Application::$prefix) - 1) .
                            substr("$path/$entry", strlen($prefix));
                    $modulePath = substr("$path/$entry", strlen($prefix));
                    self::$permissionsModel->queryResolve = true;
                    $value = self::$permissionsModel->get(
                        array(
                            "conditions" => 
                            "roles.role_id='$roleId' AND module = '{$modulePath}' AND value='1'"
                        )
                    );
                    $children = self::generateMenus($roleId, "$path/$entry");
                }

                if (file_exists("app/modules/" . $modulePath . "/package.xml")) {
                    $xml = simplexml_load_file("app/modules/" . $modulePath . "/package.xml");
                    $label = (string) reset($xml->xpath("/package:package/package:label"));
                    $icon = (string) reset($xml->xpath("/package:package/package:icon"));
                } else {
                    $label = "";
                    $icon = "";
                }

                if (count($children) > 0 || count($value) > 0) {
                    $list[$urlPath] = array(
                        "title" => $label == '' ? ucwords(str_replace("_", " ", $entry)) : $label,
                        "path" => $urlPath,
                        "children" => $children,
                        "icon" => $icon
                    );
                }
            }
        }
        array_multisort($list, SORT_ASC);
        return $list;
    }
 
    public static function flatenMenu($menu)
    {
        $flatened = array();
        foreach($menu as $item) 
        {
            $flatened[$item["path"]] = $item;
            if(count($item["children"]) > 0)
            {
                $flatened = array_merge($flatened, self::flatenMenu($item["children"]));
            }
        }
        return $flatened;
    }    
}

