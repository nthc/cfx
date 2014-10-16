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
 * Add a path to the system's include path
 * @param string $path The path of the system
 * @param string $addBase The base of the path
 */
function add_include_path($path, $addBase = true)
{
    set_include_path(($addBase ? SOFTWARE_HOME : "") .$path . PATH_SEPARATOR . get_include_path());
}

function autoloader($class)
{
    include_once $class.".php";
}

/**
 * A re-implementation of the array splice function.
 * 
 * @param type $input
 * @param type $start
 * @param type $length
 * @param type $replacement
 * @return type 
 */
function array_splic(&$input, $start, $length=0, $replacement=null)
{
    $keys=array_Keys($input);
    $values=array_Values($input);
    if($replacement!==null)
    {
        $replacement=(array)$replacement;
        $rKeys=array_Keys($replacement);
        $rValues=array_Values($replacement);
    }

    // optional arguments
    if($replacement!==null)
    {
        array_splice($keys,$start,$length,$rKeys);
        array_splice($values,$start,$length,$rValues);
    }
    else
    {
        array_splice($keys,$start,$length);
        array_splice($values,$start,$length);
    }

    $input=array_Combine($keys,$values);

    return $input;
}

/**
 * Retrieves the property of an associative array
 * @param array $array
 * @param mixed $property
 * @return mixed 
 */
function get_property($array, $property)
{
    return isset($array[$property]) ? $array[$property] : null;
}

spl_autoload_register('autoloader');
