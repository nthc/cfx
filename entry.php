<?php
/**
 * If the request is intended for the API then setup the session handlers
 * since the API caller may not have session cookies stored.
 */

if(isset($_REQUEST["__api_session_id"]))
{
    session_id($_REQUEST["__api_session_id"]);
    unset($_REQUEST["__api_session_id"]);
    unset($_POST["__api_session_id"]);
    unset($_GET["__api_session_id"]);
}

/**
 * Initialize the session handler
 */
session_start();

require "wyf_bootstrap.php";

$authExcludedPaths = array(
    "system/login",
);

/**
 * Bootstrap for CLI utilization
 */
if($cliMode === true)
{
    for ($i = 1; $i < $argc; $i++)
    {
        if(substr($argv[$i], 0, 2) == "--")
        {
            $parameter = substr($argv[$i], 2, strlen($argv[$i]) - 2);
            $i++;
            $$parameter = $argv[$i];
        }
        else
        {
            die ("Incorrect parameter specification - {$argv[$i]}\n\n");
        }
    }
    
    $users = Model::load("system.users");
    $users->queryResolve = false;
    $user = $users->getWithField2("user_name", $username);
    
    if($password == "" && $username != "")
    {
        echo "Please enter your password :";
        system('stty -echo');
        $password = str_replace("\n","", fgets(STDIN));
        system('stty echo');
        echo "\n";
    }
    
    if($user[0]["password"] != md5($password) || $user[0]["role_id"] != '1')
    {
        die("Invalid username or password ($password).\n");
    }
    
    $_SESSION["role_id"] = 1;
    $_SESSION["user_id"] = $user[0]["user_id"];
    $_SESSION["logged_in"] = true;
    Application::$cli = true;
    Application::$cliOutput = $output;
    
    $_GET = array();
    $_POST = array();
    $_REQUEST = array();
    
    $_GET["q"] = $path;
    $_REQUEST["q"] = $path;
    
    if($apimode == null) 
    {
        $apimode = "yes";
    }
    
    $_POST["__api_mode"] = $apimode;
    $_GET["__api_mode"] = $apimode;
    $_REQUEST["__api_mode"] = $apimode;
    
    foreach(explode("&", $request) as $term) 
    {
        $termparts = explode("=", $term);
        $_POST[urldecode($termparts[0])] = urldecode($termparts[1]); 
        $_REQUEST[urldecode($termparts[0])] = urldecode($termparts[1]);
        $_GET[urldecode($termparts[0])] = urldecode($termparts[1]);
    }
    if($output != "") ob_start();
    Application::render();
    if($output != "") file_put_contents($output, ob_get_clean());
}
else
{
    $t = new TemplateEngine();
    Application::$templateEngine = $t;
            
    if ($_SESSION["logged_in"] == false && array_search($_GET["q"], $authExcludedPaths) === false && substr($_GET["q"], 0, 3) != "api")
    {
        $redirect = urlencode(Application::getLink("/{$_GET["q"]}"));
        foreach($_GET as $key=>$value) 
        {
            if($key == "q") continue;
            $redirect .= urlencode("$key=$value");
        }
        header("Location: ".Application::getLink("/system/login") . "?redirect=$redirect");
    }
    else if ($_SESSION["logged_in"] === true )
    {
        if ($_SESSION["user_mode"] == 2 && $_GET["q"] != "system/login/change_password")
        {
            header("Location: " . Application::getLink("/system/login/change_password"));
        }
        
        Application::addJavaScript("lib/js/ntentan.js");
        
        $t->assign('username', $_SESSION["user_name"]);
        if (isset($_GET["notification"]))
        {
            $t->assign('notification', "<div id='notification'>" . $_GET["notification"] . "</div>");
        }
    
        $menuFile = SOFTWARE_HOME . "app/cache/menus/side_menu_{$_SESSION["role_id"]}.html";
        if(file_exists($menuFile))
        {
            $t->assign(
                'side_menu', 
                file_get_contents($menuFile)
            );
        }
    
        $top_menu_items = explode("/", $_GET["q"]);
        for($i = 0; $i < count($top_menu_items); $i++)
        {
            $item = $top_menu_items[$i];
            $link .= "/" . $item;
            while(is_numeric($top_menu_items[$i + 1]))
            {
                $link .= "/" . $top_menu_items[$i + 1];
                $i++;
            }
            $item = str_replace("_", " ", $item);
            $item = ucwords($item);
            $top_menu .= " <a href='".Application::getLink($link)."'><span>$item</span></a>";
        }
        $t->assign('top_menu', $top_menu);
    }
    
    if($_SESSION['logged_in'] == true && ($_GET['q']!='api/table') && ENABLE_AUDIT_TRAILS === true)
    {
        $data = json_encode(
            array(
                'route' => $_GET['q'],
                'request' => $_REQUEST,
                'get' => $_GET,
                'post' => $_POST
            )
        );
        
        SystemAuditTrailModel::log(
            array(
                'item_id' => '0',
                'item_type' =>'routing_activity',
                'description' => "Accessed [{$_GET['q']}]",
                'type' => SystemAuditTrailModel::AUDIT_TYPE_ROUTING,
                'data' => $data
            )
        );
    }
    
    Application::addStylesheet("css/fapi.css", "lib/fapi/");
    Application::addStylesheet("css/main.css");
    
    Application::addJavaScript(Application::getLink("/lib/fapi/js/fapi.js"));
    Application::addJavaScript(Application::getLink("/lib/js/jquery.js"));
    Application::addJavaScript(Application::getLink("/lib/js/jquery-ui.js"));
    Application::addJavaScript(Application::getLink("/lib/js/json2.js"));
    
    Application::$site_name = Application::$config['name'];
    Application::render();
}
