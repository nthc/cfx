<?php 
class SystemSequencesModel extends SQLDatabaseModel
{
    public $showInMenu = "false";
    public function __construct()
    {
        $this->connect();
    }
    
    public function create($name)
    {
        $this->datastore->createSequence($name);
    }
    
    public function drop($name)
    {
        $this->datastore->dropSequence($name);
    }
    
    public function nextVal($name)
    {
        return $this->datastore->getSequenceNextValue($name);     
    }
}