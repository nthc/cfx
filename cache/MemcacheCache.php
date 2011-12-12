<?php
/**
 * A memcache storing backend for the Caching framework. 
 * 
 * @author ekow
 * @ingroup Caching
 */

class MemcacheCache extends Cache
{
    private $memcache;
    private $cache;
    
    public function __construct()
    {
        $this->memcache = new Memcache();
        if(!$this->memcache->addServer('localhost'))
        {
            throw new Exception("Could not connect to memcached server");
        }
    }
    
    public function addImplementation($key, $object, $ttl = 0)
    {
        if(!$this->memcache->set($key, serialize($object), false, $ttl))
        {
            throw new Exception("Could not add to memcached server");
        }
        return $object;
    }
    
    public function getImplementation($key)
    {
        $object = unserialize(isset($this->cache[$key]) ? $this->cache[$key] : $this->memcache->get($key));
        return $object;
    }
    
    public function existsImplementation($key)
    {
        $var = $this->memcache->get($key);
        if($var === false)
        {
            return false;
        }
        else
        {
            $this->cache[$key] = $var;
            return true;
        }
    }
}