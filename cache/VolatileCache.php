<?php

class VolatileCache extends Cache
{
    private $data = [];
    public function addImplementation($key, $object, $ttl = 0)
    {
        $this->data[$key] = $object;
        return $object;
    }

    public function existsImplementation($key)
    {
        return isset($this->data[$key]);
    }

    public function getImplementation($key)
    {
        return $this->data[$key];
    }

}

