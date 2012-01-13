<?php
class SQLDatabaseModel extends Model
{    
    public function __construct()
    {
        global $packageSchema;
        $this->database = (substr($this->database, 0, 1) == "."?$packageSchema: "") . $this->database;
    }
    
    public static function getDatastoreInstance() 
    {
        require SOFTWARE_HOME . "app/config.php";
        $class = new ReflectionClass($db_driver);
        return $class->newInstance();
    }
    
    protected function connect()
    {
        $this->datastore = self::getDatastoreInstance();
        $this->datastore->modelName = $this->package;
    }
    
    public function getDatabase()
    {
        return $this->datastore->getDatabase();
    }
    
    public function getSearch($searchValue,$field)
    {
        return $this->datastore->getSearch($searchValue,$field);
    }
}
