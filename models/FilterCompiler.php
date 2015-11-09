<?php

class FilterCompiler
{
    private static $lookahead;
    private static $token;
    private static $filter;
    private static $tokens = array(
        'equals' => '\=',
        'bind_param' => '\\?',
        'named_bind_param' => '\:[a-z][a-z0-9\_]+',
        'number' => '[0-9]+',
        'between' => 'between\b',
        'in' => 'in\b',
        'like' => 'like\b',
        'is' => 'is\b',
        'and' => 'and\b',
        'not' => 'not\b',
        'or' => 'or\b',
        'greater_or_equal' => '\>\=',
        'less_or_equal' => '\<\=',
        'not_equal' => '\<\>',
        'greater' => '\>',
        'less' => '\<',
        'add' => '\+',
        'subtract' => '\-',
        'multiply' => '\*',
        'mod' => '\%',        
        'cast' => '::',
        'function' => '[a-zA-Z][a-zA-Z0-9\_]*\s*\(',
        'identifier' => '[a-zA-Z][a-zA-Z0-9\.\_\:]*\b',
        'obracket' => '\(',
        'cbracket' => '\)',
        'comma' => ','
    );
    
    private static $operators = array(
        array('between', 'or'),
        array('and'),
        array('not'),
        array('equals', 'greater', 'less', 'greater_or_equal', 'less_or_equal', 'not_equal', 'is'),
        array('add', 'subtract'),
        array('in'),
        array('multiply', 'mod', 'cast', 'like')
    );
    
    public static function compile($filter)
    {
        self::$filter = $filter;
        self::getToken();
        $expression = self::parseExpression();
        if(self::$token !== false)
        {
            throw new Exception("Unexpected '" . self::$token . "' in filter [$filter]");
        }
        $parsed = self::renderExpression($expression);
        return $parsed;
    }
    
    private static function renderExpression($expression)
    {
        if(is_string($expression))
        {
            return $expression;
        }
        else if(is_array($expression))
        {
            return "(" . self::renderExpression($expression['left']) . " {$expression['opr']} " . self::renderExpression($expression['right']) . ")";
        }
    }
    
    private static function match($token)
    {
        if($token != self::$lookahead)
        {
            throw new Exception("Expected $token but found " . self::$lookahead);
        }
    }
    
    private static function parseBetween()
    {
        self::match('bind_param');
        $left = self::$token;
        self::getToken();
        self::match('and');
        self::getToken();
        $right = self::$token;    
        self::getToken();
        return "$left AND $right";
    }
    
    private static function parseIn()
    {
        $expression = "(";
        self::match('obracket');
        self::getToken();
        
        do{
            $expression .= self::parseExpression();
            if(self::$lookahead === 'comma')
            {
                $expression .= ',';
                self::getToken();
                continue;
            }
            else
            {
                break;
            }
        }
        while(true);
        
        self::match('cbracket');
        
        self::getToken();
        
        $expression .= ')';
        return $expression;
    }

    private static function parseFunctionParams()
    {
        $parameters = '';
        $size = 0;
        do{
            $size++;
            $parameters .= self::renderExpression(self::parseExpression());
            if(self::$lookahead == 'comma')
            {
                self::getToken();
                $parameters .= ", ";
            }
            else if(self::$lookahead == 'cbracket')
            {
                self::getToken();
                break;
            }
            else
            {
                break;
            }
        }
        while($size < 100);
        return $parameters;
    }
    
    private static function parseFactor()
    {
        $return = null;
        switch(self::$lookahead)
        {
            case 'function':
                $name = self::$token;
                self::getToken();
                $parameters = self::parseFunctionParams();
                $return = "$name$parameters)";
                break;
            case 'identifier':
            case 'bind_param':
            case 'named_bind_param':
            case 'number':
                $return = self::$token;
                self::getToken();
                break;
            case 'obracket':
                self::getToken();
                $expression = self::parseExpression();  
                $return = self::renderExpression($expression);
                self::getToken();
                break;
        }
        
        return $return;
    }
    
    private static function parseRightExpression($level, $opr)
    {
        switch($opr)
        {
            case 'between': return self::parseBetween();
            case 'in': return self::parseIn();
            default: return self::parseExpression($level);
        }
    }
    
    private static function parseExpression($level = 0)
    {
        if($level === count(self::$operators))
        {
            return self::parseFactor();
        }
        else
        {
            $expression = self::parseExpression($level + 1);
        }
        
        while(self::$token != false)
        {
            if(array_search(self::$lookahead, self::$operators[$level]) !== false)
            {
                $left = $expression;
                $opr = self::$token;
                self::getToken();
                $right = self::parseRightExpression($level + 1, strtolower($opr));
                $expression = array(
                    'left' => $left,
                    'opr' => $opr,
                    'right' => $right
                );
            }
            else
            {
                break;
            }
        }
        
        return $expression;
    }
    
    private static function getToken()
    {
        self::eatWhite();
        self::$token = false;
        foreach(self::$tokens as $token => $regex)
        {
            if(preg_match("/^$regex/i", self::$filter, $matches))
            {
                self::$filter = substr(self::$filter, strlen($matches[0]));
                self::$lookahead = $token;
                self::$token = $matches[0];
                break;
            }
        }
        
        if(self::$token === false && strlen(self::$filter) > 0)
        {
            throw new Exception("Unexpected character [" . self::$filter[0] . "] begining " . self::$filter . ".");
        }
    }
    
    private static function eatWhite()
    {
        if(preg_match("/^\s*/", self::$filter, $matches))
        {
            self::$filter = substr(self::$filter, strlen($matches[0]));
        }
    }
}
