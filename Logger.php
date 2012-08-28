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
        fwrite(Logger::$fd[self::$path], sprintf($format, date("Y/m/d h:i:s"), $msg));
    }
}
