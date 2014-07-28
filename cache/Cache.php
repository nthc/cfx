<?php
/*
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
 * 
 */

/**
 * The base class for all Cache class implementations. 
 * 
 * @author ekowabaka
 * @package wyf.caching
 */
abstract class Cache
{
    /**
     * An instance of a cache object. This instance is kept locally so that the
     * cache object can behave as a singleton.
     * @var Cache
     */ 
    private static $object;
    
    /**
     * Initialize the caching engine.
     * 
     * @param $method The name of the caching backend to use.
     */
    public static function init($method)
    {
        if($method == "") $method = "file";
        $class = ucfirst($method) . "Cache";
        Cache::$object = new $class();
    }
    
    /**
     * Add an object to the cache.
     * @param string $key A unique identity for the object being cached
     * @param mixed $object The object to be cached
     * @param int $ttl The number of minutes the object should spend in 
     *                       the cache. If set to 0 the object exists in the
     *                       cache indefinately.
     */
    public static function add($key, $object, $ttl = 0)
    {
        return Cache::$object->addImplementation($key, $object);
    }
    
    /**
     * Get an object from the cache. Returns the object or false if the object 
     * has expired or doesn't exist in the cache. 
     * 
     * @param string $key  The unique key for the object to retrieve from the cache
     * @return mixed
     */
    public static function get($key)
    {
        return Cache::$object->getImplementation($key);
    }
    
    /**
     * Check if an object with a key exists in the cache or not.
     * @param string $key
     * @return True or false if object exists or not.
     */
    public static function exists($key)
    {
        return Cache::$object->existsImplementation($key);
    }
    
    /**
     * A function which implements the add method for a given backend.
     * @param string $key
     * @param mixed $object
     * @param int $ttl
     * @see Cache::add()
     */
    public abstract function addImplementation($key, $object, $ttl=0);
    
    /**
     * A function which implements the get method for a given backend.
     * @param string $key
     */
    public abstract function getImplementation($key);
    
    /**
     * A function which implements the exists method for a given backend.
     * @param string $key
     */
    public abstract function existsImplementation($key);
}
