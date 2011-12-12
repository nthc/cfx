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
    protected $model;
    
    protected $valueField;
    
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
        
        $info = Model::resolvePath($path);
        $this->model = Model::load((substr($info["model"],0,1) == "." ? $redirectedPackage: "") . $info["model"]);
        $this->valueField=$value;
        $field = $this->model->getFields(array($value));

        $this->setLabel($field[0]["label"]);
        $this->setDescription($field[0]["description"]);
        $this->setName($info["field"]);

        $data = $this->model->get(array("fields"=>array($info["field"],$value),"sort_field"=>$value),Model::MODE_ARRAY);

        $this->addOption("","");

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
}
