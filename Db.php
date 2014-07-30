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
 * A database access class. This class allows the application to access
 * the postgresql database directly without going through the framework.
 * Through this class connections could be established with multiple databases.
 * The framework uses this class under the hood to perform its database
 * access too.
 */
class Db
{
    const MODE_ASSOC = "assoc";
    const MODE_ARRAY = "array";
    
    /**
     * The instances of the various databases
     * @var array
     */
    private static $instances = array();
    
    public static $defaultDatabase;
    public static $error;
    private static $lastQuery;
    
    /**
     * The last instance of the database
     * @var type 
     */
    private static $lastInstance;
    
    public static function escape($string, $instance = null)
    {
        if($instance === null) $instance = Db::$lastInstance;
        if($instance == null)
        {
            return pg_escape_string($string);
        }
        else
        {
            return pg_escape_string(Db::$instances[$instance], $string);
        }
    }
    
    public static function rawQuery($query, $instance = null)
    {
        if($instance === null) $instance = Db::$lastInstance;
        $instance = Db::getCachedInstance($instance);
        $result = pg_query($instance, $query);
        return $result;
    }
    
    public static function query($query, $instance = null, $mode = null)
    {
        if($instance === null) $instance = Db::$lastInstance;
        $instance = Db::getCachedInstance($instance);
        $result = pg_query($instance, $query);
        self::$lastQuery = $query;
                
        if($result === false)
        {

            Db::$error = pg_errormessage($instance);
            return false;
        }        
        else if(pg_num_rows($result) > 0)
        {
            if($mode == Db::MODE_ARRAY)
            {
                $rows = array();
                while($row = pg_fetch_array($result, null, PGSQL_NUM))
                {
                    $rows[] = $row;
                }
                return $rows;
            }
            else
            {
                return pg_fetch_all($result);
            }
        }
        else
        {
            return array();
        }
    }
    
    public static function close($db = null)
    {
        $db = $db == null ? Db::$defaultDatabase : $db;
        if(is_resource(Db::$instances[$db])) 
        {
            pg_close(Db::$instances[$db]);
            unset(Db::$instances[$db]);
        }
        else
        {
            Db::$instances = null;
        }
        
    }
    
    public static function reset($db = null, $atAllCost = false)
    {
        Db::close($db);
        Db::get($db, $atAllCost);
    }
    
    public static function getCachedInstance($db)
    {
        if(isset(Db::$instances[$db]))
        {
            return Db::$instances[$db];
        }
        else
        {
            return Db::get($db, false);
        }
    }
    
    /**
     * Returns an instance of a named database. All the database configurations
     * are stored in the app/config.php
     * @param string $db The tag of the database in the config file
     * @param boolean $atAllCost When set to true this function will block till a valid db is found.
     * @return resource
     */
    public static function get($db = null, $atAllCost = false)
    {
        if(class_exists('Application'))
        {
            if(!isset(Application::$config))
            {
                require "app/config.php";
                if($db == null) $db = $selected;
            }
            else 
            {
                $database = Application::$config['db'];
                if($db == null) $db = self::$defaultDatabase;
            }
        }
        else if(is_array($db))
        {
            // When connecting from outside the framework
            $index = json_encode($db);
            $database[$index] = $db;
            $db = $index;
        }
        else
        {
            throw new Exception('Invalid configuration parameters passed');
        }
        
        unset(Db::$instances[$db]);
        
        while(!is_resource(Db::$instances[$db]))
        {
            $db_host = $database[$db]["host"];
            $db_port = $database[$db]["port"];
            $db_name = $database[$db]["name"];
            $db_user = $database[$db]["user"];
            $db_password = $database[$db]["password"];
            
            Db::$instances[$db] = pg_connect("host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_password");
            
            if(!Db::$instances[$db]) 
            {
                if($atAllCost)
                {
                    sleep(10);
                }
                else
                {
                    throw new Exception("Could not connect to $db database");
                }
            }
        }
        Db::$lastInstance = $db;
        return Db::$instances[$db];
    }
    
    public function getLastQuery()
    {
        return self::$lastQuery;
    }
}
