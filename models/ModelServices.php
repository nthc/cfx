<?php
class ModelServices
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

    public function validator_required($name,$parameters)
    {
        if($this->data[$name]!=="")
        {
            return true;
        }
        else
        {
            return "The %field_name% field is required";
        }
    }

    public function validator_unique($name,$parameter)
    {
        $data = $this->model->getWithField($name,$this->model->escape($this->data[$name]));
        if(count($data)==0 || $this->model->datastore->checkTemp($name,$this->data[$name]))
        {
            return true;
        }
        else
        {
            return "The value of the %field_name% field must be unique.";
        }
    }

    public function validator_regexp($name,$parameter)
    {
        $ret =  preg_match($parameter[0],$this->data[$name])>0?true:"The %field_name% format is invalid";
        return $ret;
    }
}
