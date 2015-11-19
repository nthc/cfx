<?php

/*
 * WYF Framework
 * Copyright (c) 2011 James Ekow Abaka Ainooson
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * This script bootstraps the entire WYF framework. It should be included in
 * CLI scripts which intend to use the wyf framework for their operations.
 */

// Load the core utilities which handle auto loading of classes.
include "coreutils.php";


//include auth files
$directoryPath = __DIR__."/modules/cfx_auth/";

add_include_path($directoryPath."constraints",false);
add_include_path($directoryPath."login",false);
add_include_path($directoryPath."logout",false);
add_include_path($directoryPath."password_history",false);
add_include_path($directoryPath."role_validity",false);
add_include_path($directoryPath."roles",false);
add_include_path($directoryPath."users",false);
add_include_path($directoryPath."users_roles",false);

//Add lib for auth
add_include_path(__DIR__."/lib",false);

// Load the applications configuration file and define the home
require "app/config.php";
define("SOFTWARE_HOME", $config['home']);

// Add the script which contains the third party libraries
require "app/includes.php";

// Setup the global variables needed by the redirected packages
global $redirectedPackage;
global $packageSchema;

$selected = getenv('CFX_SELECTED_DATABASE') !== false ? getenv('CFX_SELECTED_DATABASE') : $selected;

// Setup the database driver and other boilerplate stuff 
$dbDriver = $config['db'][$selected]['driver'];
$dbDriverClass = Application::camelize($dbDriver);
add_include_path(Application::getWyfHome("models/datastores/databases/$dbDriver"));

Db::$defaultDatabase = $selected;
SQLDBDataStore::$activeDriverClass = $dbDriverClass;

Application::$config = $config;
Application::$prefix = $config['prefix'];

Cache::init($config['cache']['method']);
define('CACHE_MODELS', $config['cache']['models']);
define('CACHE_PREFIX', "");
define('ENABLE_AUDIT_TRAILS', $config['audit_trails']);

