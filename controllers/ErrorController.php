<?php
/**
 * A controller used to show error messages. This controller is automatically
 * loaded whenever another controller requested does not exist or does not
 * contain the method that is being requested.
 * 
 * @package framework.controllers
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 *
 */
class ErrorController extends Controller
{
    public function __construct()
    {
        Application::setTitle("Access Restricted");
        $this->label = "Error";
        $this->description = "There was an error loading the content that you requested.";
    }

    /**
     * (non-PHPdoc)
     * @see lib/controllers/Controller::getContents()
     */
    public function getContents()
    {
        $error_message =
        "You may be seeing this message because
        <ol>
            <li>You may not have the right to access the content you are requesting</li>
            <li>The content you are requesting does not exist</li>
            <li>There is an error with the system</li>
        </ol>";
        return $error_message;
    }
}

