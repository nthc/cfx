<?php

$_ENV['CFX_SELECTED_DATABASE'] = 'test';
$_ENV['CFX_TEST'] = true;

if(is_file(getcwd() . $_SERVER['REQUEST_URI'])) {
    return false;
} else {
    if(isset($_SERVER['PATH_INFO'])) {
        $route = substr($_SERVER['PATH_INFO'], 1);
        if(substr($route, -1) === '/') {
            $route = substr($route, 0, -1);
        }
        $_GET['q'] = $route;
    }
    require 'index.php';
}
