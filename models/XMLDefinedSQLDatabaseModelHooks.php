<?php
class XMLDefinedSQLDatabaseModelHooks
{
    /**
     * @var Model
     */
    protected $model;
    protected $data;
    protected $fields;

    public function setModel($model)
    {
        $this->model = $model;
        $this->data = $model->getData();
        $fields = $this->model->getFields();
    }

    protected function getReturnData($errors)
    {
        if(count($errors)>0)
        {
            return array("errors"=>$errors,"numErrors"=>count($errors));
        }
        else
        {
            return true;
        }
    }
    
    public function setData($data)
    {
        $this->data = $data;
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    public function validate()
    {
        return true;
    }

    public function preAdd()
    {
        
    }
    
    public function postAdd($primaryKeyValue,$data)
    {
        
    }
    
    public function preUpdate($keyField, $keyValue)
    {
        
    }
    
    public function postUpdate()
    {
        
    }

    public function preValidate()
    {
        
    }

    public function postValidate($errors)
    {

    }

    public function save()
    {
        return false;
    }

    public function update($field,$value)
    {
        return false;
    }

    public function delete($field, $value)
    {
        return false;
    }

    public function preDelete($primaryKeyValue, $data)
    {
    
    }

    public function postDelete()
    {
        
    }
}
