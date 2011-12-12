<?php
class SQLDatabaseModel extends Model
{
    public function __construct()
    {
        global $packageSchema;
        $this->database = (substr($this->database, 0, 1) == "."?$packageSchema: "") . $this->database;
    }
    
    protected function connect()
    {
        require SOFTWARE_HOME . "app/config.php";
        $class = new ReflectionClass($db_driver);
        $this->datastore = $class->newInstance();
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
