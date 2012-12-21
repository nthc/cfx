<?php
/**
 * A special field for displaying data from models for selection.
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 */
class ModelField extends SelectionList
{
    /**
     * @var Model
     */
    private $model;
    private $valueField;
    private $info;
    
    protected $conditions;


    /**
     * Creates a new ModelField. The example below creates a ModelField which
     * lists client accounts for selection.
     * 
     * @code
     * $clients = new ModelField("brokerage.setup.clients.client_id", "account");
     * @endcode
     * @param $path The full path to the field in the module which is to be returned by this field.
     * @param $value The name of the field from the model whose value should be displayed in the list.
     */
    public function __construct($path,$value)
    {
        global $redirectedPackage;
        
        $this->info = Model::resolvePath($path);
        $this->model = Model::load((substr($this->info["model"],0,1) == "." ? $redirectedPackage: "") . $this->info["model"]);
        $this->valueField = $value;
        $field = $this->model->getFields(array($value));

        $this->setLabel($field[0]["label"]);
        $this->setDescription($field[0]["description"]);
        $this->setName($this->info["field"]);
        
        $params = array(
            "fields" => array($this->info["field"],$this->valueField),
            "sort_field" => $this->valueField,
        );
        
        if($this->conditions != '')
        {
            $params['conditions'] = $this->conditions;
        }           
        
        $data = $this->model->get(
            $params,
            Model::MODE_ARRAY
        );

        foreach($data as $datum)
        {
            if($datum[1] == "")
            {
                $this->addOption($datum[0]);
            }
            else
            {
                $this->addOption($datum[1],$datum[0]);
            }
        }         
    }
    
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;
        return $this;
    }
}
