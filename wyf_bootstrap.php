<?php
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

/**
 * Add the configuration file
 */
require "app/config.php";
define("SOFTWARE_HOME", $config['home']);


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
require "app/includes.php";

global $redirectedPackage;
global $packageSchema;


$dbDriver = $config['db'][$selected]['driver'];
$dbDriverClass = Application::camelize($dbDriver);
add_include_path("lib/models/datastores/databases/$dbDriver");
Db::$defaultDatabase = $selected;
SQLDBDataStore::$activeDriverClass = $dbDriverClass;

Application::$config = $config;


Cache::init($config['cache']['method']);
define('CACHE_MODELS', $config['cache']['models']);
define('CACHE_PREFIX', "");
define('ENABLE_AUDIT_TRAILS', $config['audit_trails']);

if(ENABLE_AUDIT_TRAILS === true)
{
    require_once "app/modules/system/audit_trail/SystemAuditTrailModel.php";
}
