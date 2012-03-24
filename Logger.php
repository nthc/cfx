<?php
/**
 * A simple class for logging software activities. Normally used during
 * debugging.
 * @ingroup Utilities
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
     * @param string $msg
     * @param string $format
     */
    public static function log($msg, $format = "[%s] %s\n")
    {
        $fd = Logger::$fd[self::$path];
        if(!is_resource($fd)) self::setPath();
        fwrite($fd, sprintf($format, date("Y/m/d h:i:s"), $msg));
    }
}
