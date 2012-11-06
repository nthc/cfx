<?php
/*
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
*
*/

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

