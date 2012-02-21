<?php
error_reporting(E_ALL ^ E_NOTICE);
set_include_path(get_include_path() . PATH_SEPARATOR . "../../" . PATH_SEPARATOR . "../../../");

date_default_timezone_set("Africa/Accra");

require "coreutils.php";
require "app/config.php";
require "app/includes.php";

add_include_path("lib");
add_include_path("lib/models");
add_include_path("lib/models/datastores");
add_include_path("lib/models/datastores/databases/oracle");
add_include_path("lib/models/datastores/databases/postgresql");
add_include_path("lib/cache");
add_include_path("lib/rapi");

Cache::init($cache_method);
SQLDBDataStore::$activeDriver = $db_driver;

define('CACHE_PREFIX', "../../");
define('CACHE_MODELS', $cache_models);

if($enable_audit_trails)
{
    require_once "app/modules/system/audit_trail/SystemAuditTrailModel.php";
}
