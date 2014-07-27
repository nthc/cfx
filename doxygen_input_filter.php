<?php

error_reporting(E_ALL ^ E_NOTICE);

$file = fopen($argv[1], 'r');
global $pendingVariableType;
$pendingVariableType = false;

function replace_lines($matches)
{
    global $pendingVariableType;
    fputs(STDERR, "{$matches['protection']} {$matches['static']} {$pendingVariableType} {$matches['function']} \n");
    return "{$matches['protection']} {$matches['static']} {$pendingVariableType} {$matches['function']} ";
}

while(!feof($file))
{
    $line = fgets($file);
    if(preg_match('/\@var (?<type>[a-zA-Z][a-zA-Z\_]*)/', $line, $matches))
    {
        $pendingVariableType = $matches['type'];
    }
    else
    {
        echo preg_replace_callback(
            '/\b(?<protection>public|private|protected|const)\s+(?<static>static)?\s+(?<function>function)?/',
            'replace_lines', 
            $line
        );
    }
    /*else if(preg_match('/\b(?<protection>public|private|protected|const)\s+(?<static>static)?\s+(?<function>function)?/', $line, $matches) && $pendingVariable !== false)
    {
        //fputs(STDERR, print_r($matches, true));
        //echo "$pendingVariable $line";
        //preg_replace('/\b(public|private|protected/', $pendingVariable, $file);
        //$pendingVariable = false;
        
        if(isset($matches['function']))
        {
            
        }
    }
    else
    {
        echo $line;
    }*/
}


