<?php
/**
 * A database access class. This class allows the application to access
 * the postgresql database directly without going through the framework.
 * Through this class connections could be established with multiple databases.
 * 
 * @ingroup Utilities
 */
class Db
{
    /**
     * The instances of the various databases
     * @var array
     */
	private static $instances = array();
    
    /**
     * 
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
        return pg_fetch_all($result);
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
    
    public static function getParameters($db = 'main')
    {
        require "app/config.php";
        if($db == 'main')
        {
            return array(
                'host' => $db_host,
                'port' => $db_port,
                'name' => $db_name,
                'user' => $db_user,
                'password' => $db_password
            );
        }
        else
        {
            return array (
                'host' => $database[$db]['host'],
                'port' => $database[$db]['port'],
                'name' => $database[$db]['name'],
                'user' => $database[$db]['user'],
                'password' => $database[$db]['password']
            );
        }
    }
    
    /**
     * Returns an instance of a named database. All the database configurations
     * are stored in the <tt>app/config.php</tt>.
     * @param string $db
     * @return resource
     */
    public static function get($db = null)
    {
        if($db == null)
        {
            require "app/config.php";
            $db = 'main';
            Db::$instances['main'] = pg_connect("host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_password");            
            if(!Db::$instances['main']) 
            {
                throw new Exception("Could not connect to $db database");
            }            
        }
        else if(@!is_resource (Db::$instances[$db])) 
        {
        	require "app/config.php";
            $db_host = $database[$db]["host"];
            $db_port = $database[$db]["port"];
            $db_name = $database[$db]["name"];
            $db_user = $database[$db]["user"];
            $db_password = $database[$db]["password"];
            
            Db::$instances[$db] = pg_connect("host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_password");
            
            if(!Db::$instances[$db]) {
                throw new Exception("Could not connect to $db database");
            }
        }
        
        Db::$lastInstance = $db;
        return Db::$instances[$db];
    }
}