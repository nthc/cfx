<?php
/**
 * A simple class for logging software activities.
 */
class Logger
{
    /**
     * File descriptor for the log file
     * @var resource
     */
    private static $fd;

    /**
     * Path of the log file
     * @var string
     */
    private static $path;
    
    /**
     * Set the path which would be used for logging.
     * @param unknown_type $path
     * @throws Exception
     */
    public static function setPath($path = 'app/logs/wyf.log')
    {
        if($path != self::$path)
        {
            Logger::$fd[$path] = fopen($path, "a");
            self::$path = $path;
            if(Logger::$fd[$path] == false)
            { 
                throw new Exception("Could not open log file [$path]");
            }
        }
    }

    /**
     * Writes to the log file.
     * @param string $msg The message to be logged.
     * @param string $format Much like the format you can send to the printf command
     */
    public static function log($msg, $format = "[%s] %s\n")
    {
        $fd = Logger::$fd[self::$path];
        if(!is_resource($fd)) self::setPath();
        fwrite($fd, sprintf($format, date("Y/m/d h:i:s"), $msg));
    }
}
