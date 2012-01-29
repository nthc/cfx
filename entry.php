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

/**
 * Load the core utilities which handle auto loading of classes.
 */
include "coreutils.php";

/**
 * Set the default timezone to Africa/Accra
 */
date_default_timezone_set("Africa/Accra");


/**
 * Tone down on error reporting. Let's igonore notices so that PHP is not too
 * loud.
 */
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);

require "app/config.php";

/**
 * Setup default include paths.
 */

add_include_path("lib");
add_include_path("lib/controllers");
add_include_path("lib/fapi/Forms");
add_include_path("lib/toolbar");
add_include_path("lib/tapi");
add_include_path("lib/rapi");
add_include_path("lib/user");
add_include_path("lib/models");
add_include_path("lib/models/datastores");
add_include_path("lib/cache/");
add_include_path("lib/models/datastores/databases/$db_driver/");

require_once "lib/models/datastores/databases/$db_driver/$db_driver.php";
require "app/includes.php";

global $redirectedPackage;
global $packageSchema;


SQLDBDataStore::$activeDriver = $db_driver;
Cache::init($cache_method);
define('CACHE_MODELS', $cache_models);
define('CACHE_PREFIX', "");

if($enable_audit_trails)
{
    require_once "app/modules/system/audit_trail/SystemAuditTrailModel.php";
}

/**
 * Bootstrap for CLI utilization
 */
if(defined("STDIN"))
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
    if($_SESSION['logged_in'] == true && ($_GET['q']!='api/table') && $enable_audit_trails)
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
        
        
        /*Db::query(
            "INSERT INTO common.audit_trail
            (
                user_id, item_id, item_type, description, audit_date,type
            )
            VALUES(
                {$_SESSION['user_id']},
                '0',
                'routing_activity',
                'Accessed [{$_GET['q']}]',
                CURRENT_TIMESTAMP,4
            )"
        );
        Db::query("INSERT INTO common.audit_trail_data(audit_trail_id, data) VALUES(LASTVAL(), )")*/
}    
    Application::render();
}
