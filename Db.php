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
 * 
 * @package wyf.core
 */
class Db
{
    /**
     * The instances of the various databases
     * @var array
     */
    private static $instances = array();
    
    public static $defaultDatabase;
    
    /**
     * The last instance of the database
     * @var type 
     */
    private static $lastInstance;
    
    public static function escape($string, $instance = null)
    {
        if($instance === null) $instance = Db::$lastInstance;
        return pg_escape_string(Db::$instances[$instance], $string);
    }
    
    public static function query($query, $instance = null)
    {
        if($instance === null) $instance = Db::$lastInstance;
        $result = pg_query(Db::get($instance), $query);
        return is_bool($result) ? null : pg_fetch_all($result);
    }
    
    public static function reset($db = 'main')
    {
        if(is_resource(Db::$instances[$db])) 
        {
            pg_close(Db::$instances[$db]);
        }
        else
        {
            Db::$instances = null;
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
            $index = json_encode($db);
            $database[$index] = $db;
            $db = $index;
        }
        else
        {
            throw new Exception('Invalid configuration parameters passed');
        }
        
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
}
