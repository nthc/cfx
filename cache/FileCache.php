<?php

/**
 * A file storing backend for the the Caching framework.
 *  
 * @author ekow
 * @ingroup Caching
 */
class FileCache extends Cache
{
	/**
	 * (non-PHPdoc)
	 * @see lib/cache/Cache::addImplementation()
	 */
    public function addImplementation($key, $object, $ttl = 0)
    {
        file_put_contents(SOFTWARE_HOME . "app/cache/code/$key", serialize($object));
        return $object;
    }
    
    /**
     * (non-PHPdoc)
     * @see lib/cache/Cache::getImplementation()
     */
    public function getImplementation($key)
    {
        if(file_exists(SOFTWARE_HOME . "app/cache/code/$key")) {
          $return = unserialize(file_get_contents(SOFTWARE_HOME . "app/cache/code/$key")); 
        }
        else
        {
            $return = null;
        }
        return $return;
    }
    
    /**
     * (non-PHPdoc)
     * @see lib/cache/Cache::existsImplementation()
     */
    public function existsImplementation($key)
    {
        return file_exists(CACHE_PREFIX ."app/cache/code/$key");
    }
}
