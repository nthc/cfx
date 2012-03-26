<?php
class SystemAuditTrailModel extends ORMSQLDatabaseModel
{
    const AUDIT_TYPE_ADDED_DATA = 0;
    const AUDIT_TYPE_UPDATED_DATA = 1;
    const AUDIT_TYPE_DELETED_DATA = 2;
    const AUDIT_TYPE_ROUTING = 4;
    const AUDIT_TYPE_SYSTEM = 5;
    
    private static $instance = false;
    private static $dataModel;
    
    public $database = '.audit_trail';
    private $auditTrailData;
    
    public function update()
    {
        throw new Exception('Cannot update audit trail');
    }
    
    public function delete()
    {
        throw new Exception('Cannot delete audit trail');
    }
    
    private function getInstance()
    {
        if(self::$instance === false)
        {
            self::$instance = Model::load('system.audit_trail');
            self::$dataModel = Model::load('system.audit_trail_data');
        }
        return self::$instance;
    }
    
    public static function log($params)
    {
        $model = self::getInstance();
        $params['user_id'] = $_SESSION['user_id'];
        $params['audit_date'] = time();
        $model->setData($params);
        $model->save();
    }
    
    public function preAddHook()
    {
        $this->auditTrailData = /*gzcompress(*/$this->datastore->data['data']/*)*/;
        unset($this->datastore->data['data']);
    }
    
    public function postAddHook($id, $data)
    {
        self::$dataModel->setData(
            array(
                'audit_trail_id' => $id,
                'data' => $this->auditTrailData,
            )
        );
        self::$dataModel->save();
    }
}
