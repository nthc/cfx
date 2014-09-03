<?php
class Sessions
{
    private $new = false;
    private $lifespan = 1800;
    private $id;
    private static $handler = false;
    
    public static function getHandler()
    {
        if(self::$handler === false)
        {
            self::$handler = new Sessions();
        }
        
        return self::$handler;
    }
    
    public function open($sessionPath, $sessionName)
    {
        
    }
    
    public function write($sessionId, $data)
    {
        if($this->new)
        {
            Db::query(
                sprintf(
                    "INSERT into system.sessions(id, data, expires, lifespan) VALUES('%s', '%s', %d, %d)",
                    $sessionId, 
                    Db::escape($data), 
                    time() + $this->lifespan, 
                    $this->lifespan
                ), 
                'main'
            );
        }
        else
        {
            if($_GET['no_extend']==true)
            {
                return true;
            }
            else{
            Db::query(
                sprintf(
                    "UPDATE system.sessions SET data = '%s', expires = %d WHERE id = '%s'",
                    db::escape($data), time() + $this->lifespan, $sessionId
                ),
                'main'
            );
            }
        }
        return true;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public static function bindUser($userId)
    {
        if(Application::$config['custom_sessions'])
        {
            Db::query(sprintf("DELETE FROM system.sessions WHERE user_id = %d", $userId), 'main');
            Db::query(sprintf("UPDATE system.sessions SET user_id = %d WHERE id = '%s'", $userId, self::getHandler()->getId()), 'main');
        }
    }
    
    public function read($sessionId)
    {
        $this->id = $sessionId;
        $result = reset(
            Db::query(
                sprintf("SELECT data, lifespan, expires FROM system.sessions WHERE id = '%s'", $sessionId, time()),
                'main'
            )
        );
        if($result['expires'] <= time())
        {
            Db::query(sprintf("DELETE FROM system.sessions WHERE id = '%s'", $sessionId), 'main');
            $this->new = true;
            return '';
        }
        else if(count($result) == 0)
        {
            Db::query(sprintf("DELETE FROM system.sessions WHERE id = '%s'", $sessionId), 'main');
            $this->new = true;
            return '';
        }
        else
        {
            $this->lifeSpan = $result['lifespan'];
            return $result['data'];
        }
    }
    
    public function close()
    {
        return true;
    }
    
    public function destroy($sessionId)
    {
        Db::query(sprintf("DELETE FROM system.sessions WHERE id = '%s'", $sessionId), 'main');
        return true;        
    }
    
    public function gc($lifetime)
    {
        Db::query(sprintf("DELETE FROM system.sessions WHERE expiry < %d", time()), 'main');
        return true;
    }
    
    public function isNew()
    {
        return $this->new;
    }
}

