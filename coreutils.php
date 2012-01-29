<?php
function add_include_path($path, $addBase = true)
{
    set_include_path(($addBase ? SOFTWARE_HOME : "") .$path . PATH_SEPARATOR . get_include_path());
}

function __autoload($class)
{
    include_once $class.".php";
}

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
        array_Splice($keys,$start,$length,$rKeys);
        array_Splice($values,$start,$length,$rValues);
    }
    else
    {
        array_Splice($keys,$start,$length);
        array_Splice($values,$start,$length);
    }

    $input=array_Combine($keys,$values);

    return $input;
}

function get_property($array, $property)
{
    return isset($array[$property]) ? $array[$property] : null;
}