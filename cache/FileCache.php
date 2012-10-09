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
 * A file storing backend for the the Caching framework. 
 *  
 * @author ekow
 * @package wyf.caching
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
