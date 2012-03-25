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
    Application::render();
}
