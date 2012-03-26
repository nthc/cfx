<?php

require_once "lib/modules/system/reminders/SystemRemindersWidget.php";

class DashboardController extends Controller
{
    public function __construct()
    {
        add_include_path("app/modules/system/reminders");
        $this->label = "Dashboard";
        $this->_showInMenu = "false";
    }
    
    public function getContents()
    {
    	if($_SESSION["role_id"]=="") return;
        $permissionsModel = Model::load("system.permissions");
        $widgets = array();
        $permissions = $permissionsModel->get(
            array(
                "conditions"    =>  "module = '//dashboard' AND permissions.role_id='{$_SESSION["role_id"]}'"
            ),
            Model::MODE_ASSOC,
            false, false
        );
                
        foreach($permissions as $permission)
        {
            if(
                $permission["permission"] == "system/reminders" ||
                $permission["permission"] == "can_log_in_to_web"
            ) continue;
            if($permission['value'] == '1')
            {
	            add_include_path("app/modules/{$permission["permission"]}");
	            $widgetClass =  Application::camelize($permission["permission"], "/")."Widget";
	            $widget = new $widgetClass();
	            if($widget->order > 0)
	            {
	                $widgets[$widget->order] = $widget;
	            }
	            else
	           {
	                $widgets[] = $widget;
	            }
            }
        }
        
        arsort($widgets);
        $widgets[] = new SystemRemindersWidget();
        
        $layout = array();
        $k = 0;
        
        foreach($widgets as $i => $widget)
        {
            $widget = Widget::wrap($widget);
            if($widget !== false)
            {
                $layout[$k % 2] .= $widget;
                $k++;
            }
        }
        $ret = "<div id='widgets-wrapper'><div id='widgets-left'>{$layout[1]}</div><div id='widgets-right'>{$layout[0]}</div></div>";
        return $ret;
    }
    
    public function getPermissions()
    {
        $permissions = array(
           array("name"=>"can_log_in_to_web", "label"=>"Can log into the dashboard")
        );
        
        return array_merge($permissions, $this->getWidgetsList());
    }
    
    public function getWidgetsList($path = "")
    {
        $prefix = "app/modules/";
        $d = dir($prefix . $path);
        $list = array();
        while (false !== ($entry = $d->read()))
        {
            if($entry != "." && $entry != ".." && is_dir("$prefix$path/$entry"))
            {
                if(file_exists("$prefix$path/$entry/" . Application::camelize("$path/$entry", "/") . "Widget.php"))
                {
                    add_include_path("$prefix$path/$entry");
                    $widgetClass = Application::camelize("$path/$entry", "/") . "Widget";
                    $widget = new $widgetClass();
                    Application::camelize("$path/$entry");
                    $list[] = array(
                        "label" =>  "{$widget->label} Widget",
                        "name"  =>  substr("$path/$entry",1)
                    );
                }
                $list = array_merge($list, $this->getWidgetsList("$path/$entry"));
            }
        }
        return $list;
    }
}
