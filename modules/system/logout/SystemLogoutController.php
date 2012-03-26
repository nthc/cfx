<?php
/**
 * A controller for logging users out of the framework
 * @author abaka
 */
class SystemLogoutController extends Controller
{
    public function __construct()
    {

    }

    public function getContents()
    {
        User::log("Logged out");
        $_SESSION = array();
        header("Location: ".Application::getLink("/"));
        Application::$template = "login.tpl";
        return "You have been logged out.";
    }
}