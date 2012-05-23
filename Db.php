<?php
/**
 * A database access class. This class allows the application to access
 * the postgresql database directly without going through the framework.
 * Through this class connections could be established with multiple databases.
 * 
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
     * @param string $db
     * @return resource
     */
    public static function get($db = null)
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
        
        if(!is_resource(Db::$instances[$db]))
        {
            $db_host = $database[$db]["host"];
            $db_port = $database[$db]["port"];
            $db_name = $database[$db]["name"];
            $db_user = $database[$db]["user"];
            $db_password = $database[$db]["password"];
            
            Db::$instances[$db] = pg_connect("host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_password");
            
            if(!Db::$instances[$db]) 
            {
                throw new Exception("Could not connect to $db database");
            }
        }
        
        Db::$lastInstance = $db;
        return Db::$instances[$db];
    }
}

