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
    public static $path;

    /**
     * Writes to the log file.
     * @param string $msg
     * @param string $format
     */
    public static function log($msg, $format = "[%s] %s\n")
    {
        if(!is_resource(Logger::$fd))
        {
            Logger::$fd = fopen(Logger::$path == "" ? SOFTWARE_HOME . "app/temp/log.sql" : Logger::$path, "a");
        }
        fwrite(Logger::$fd, sprintf($format, date("Y/m/d h:i:s"), $msg));
    }
}
