<?php
/**
 * 
 */
class CfxLogout extends Controller
{
    public function __construct()
    {

    }

    public function getContents()
    {
        User::log("Logged out");
        $_SESSION = array();
        Application::$template = "login.tpl";
        Application::redirect("/");
    }
}
