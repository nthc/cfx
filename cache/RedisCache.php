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
 * A memcache storing backend for the Caching framework. 
 * 
 * @author ekow
 * @package wyf.caching
 */

class RedisCache extends Cache
{
    private $redis;
    
    private function getServerKey($key)
    {
        return Application::$config['cache']['server_key'] . ":$key";
    }
    
    private function instance()
    {
        if(!$this->redis->isConnected()) {
            if(!$this->redis->connect(Application::$config['cache']['host'], Application::$config['cache']['port'])) {
                throw new Exception("Could not connect to redis server");
            }   
        }
        return $this->redis;
    }
    
    public function __construct()
    {
        $this->redis = new Redis();
    }
    
    public function addImplementation($key, $object, $ttl = 0)
    {
        $this->instance()->set($this->getServerKey($key), serialize($object));
        return $object;
    }
    
    public function getImplementation($key)
    {
        return unserialize($this->instance()->get($this->getServerKey($key)));
    }
    
    public function existsImplementation($key)
    {
        return $this->instance()->exists($this->getServerKey($key));
    }
}