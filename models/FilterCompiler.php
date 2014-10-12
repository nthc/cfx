<?php

class FilterCompiler
{
    private static $lookahead;
    private static $token;
    private static $filter;
    private static $tokens = array(
        'equals' => '\=',
        'bind_param' => '\?|:[a-z][a-z0-9\_]+',
        'between' => 'between\b',
        'like' => 'like\b',
        'in' => 'in\b',
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
        'identifier' => '[a-zA-Z][a-zA-Z0-9\.\_]*\b',
        'obracket' => '\(',
        'cbracket' => '\)',
    );
    
    private static $operators = array(
        array('between', 'or' /*'in', 'like'*/),
        array('and'),
        array('not'),
        array('equals', 'greater', 'less', 'greater_or_equal', 'less_or_equal', 'not_equal', 'is'),
        array('add', 'subtract'),
        array('multiply')
    );
    
    public static function compile($filter)
    {
        self::$filter = $filter;
        self::getToken();
        $expression = self::parseExpression();
        if(self::$token !== false)
        {
            throw new Exception("Unexpected " . self::$token);
        }
        return self::renderExpression($expression);
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
    
    private static function parseFactor()
    {
        $return = null;
        switch(self::$lookahead)
        {
            case 'identifier':
            case 'bind_param':
                $return = self::$token;
                self::getToken();
                break;
            case 'obracket':
                self::getToken();
                $expression = self::parseExpression();  
                $return = self::renderExpression($expression);
                self::getToken();
                break;
            /*default:
                throw new Exception("Unexpected " . self::$token);*/
        }
        
        return $return;
    }
    
    private static function parseRightExpression($level, $opr)
    {
        switch($opr)
        {
            case 'between': return self::parseBetween();
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
    }
    
    private static function eatWhite()
    {
        if(preg_match("/^\s*/", self::$filter, $matches))
        {
            self::$filter = substr(self::$filter, strlen($matches[0]));
        }
    }
}
