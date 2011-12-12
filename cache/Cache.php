<?php
/**
 * Caches are normally used to store frequently accessed data which need to
 * be preserved accross sessions. Caches are volatile storage and as such
 * information which might cause havock if lost should not be stored there.
 * @defgroup Caching
 */

/**
 * An abstract caching class. Defines all the methods needed to cache classes
 * based on base caching methods.
 * 
 * @author ekow
 * @ingroup Caching
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
     * Initialize the cache.
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
     * @param &key A unique identity for the object being cached
     * @param $object The object to be cached
     * @param $ttl The number of minutes the object should spend in 
     *                       the cache. If set to 0 the object exists in the
     *                       cache indefinately.
     */
    public static function add($key, $object, $ttl = 0)
    {
        return Cache::$object->addImplementation($key, $object);
    }
    
    /**
     * Get an object from the cache.
     * @param string $key  The unique key for the object to retrieve from the cache
     * @return The object or false if the object has expired or doesn't exist in the cache.
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
