<?php

/**
 * 
 */
class ORMSQLDatabaseModel extends SQLDatabaseModel
{
    public $references;
    public $types = array();
    public $options = array();
    public $values = array();
    public $validators = array();
    public $keys = array();
    public $unique = array();
    
    public function __construct($package, $name)
    {
        global $redirectedPackage;
        parent::__construct();
        $this->package = $package;
        $this->name = $name;
        $this->label = $this->label == "" ? Application::labelize($name) : $this->label;
        
        $this->connect();
        $this->datastore->database = $this->database;
        $fields = $this->datastore->describe();
        
        if($fields == null)
        {
            throw new Exception("Database table does not exists");
        }
        
        $fixedConditions = array();
        
        foreach($fields as $field)
        {
            $name = $field["name"];
            $this->fields[$name] = array(
                "name"          => $field["name"],
                "type"          => isset($this->types[$name]) ? $this->types[$name] : $field["type"],
                "label"         => Application::labelize($field["name"]),
                "validators"    => array()
            );
            
            if(isset($field["key"]))
            {
                $this->fields[$name]["key"] = $field["key"];
                $this->keyField = $field["name"];
            }
            
            if(isset($this->options[$name]))
            {
                $this->fields[$name]["type"] = "enum";
                $this->fields[$name]["options"] = $this->options[$name];
            }
            
            if(isset($this->values[$name]))
            {
                $this->fields[$name]["value"] = $this->values[$name];
                $fixedConditions[] = "{$this->database}.{$name} = '{$this->values[$name]}'";
                $this->fixedValues[$name] = $this->values[$name];
            }
            
            if(isset($this->validators[$name]))
            {
                $this->fields[$name]["validators"] = $this->validators[$name];
            }

            if(isset($this->keys[$name]))
            {
                foreach($this->fields as $tempName => $tempField)
                {
                    if($tempField["key"] == $this->keys[$name])
                        unset($this->fields[$tempName]['key']);
                }
                $this->fields[$name]["key"] = $this->keys[$name];
                $this->fields[$name]["validators"] = array();
            }

            if(array_search($name, $this->unique) !== false)
            {
                $field['unique'] = true;
            }

            if(isset($this->references[$name]))
            {
                $this->fields[$name]["type"] = "reference";
                $this->fields[$name]["reference"] = $this->references[$name]["reference"];
                $this->fields[$name]["referenceValue"] = $this->references[$name]["referenceValue"];

                $fieldInfo = model::resolvePath($this->references[$name]["reference"]);
                if($package == $fieldInfo["model"])
                {
                    $table = $this->database;
                }
                else
                {
                    $tempModel = Model::load((substr($fieldInfo["model"],0,1) == "." ? $redirectedPackage: "") . $fieldInfo["model"],$this->prefix);
                    $table = $tempModel->getDatabase();
                }

                $this->referencedFields[] = array(
                    "referencing_field" => $name,
                    "reference" => $this->references[$name]["reference"],
                    "referenced_value_field" => $this->references[$name]["referenceValue"],
                    "table" => (string) $table,
                    "referenced_field" => $fieldInfo["field"]
                );
            }

            if($field["required"])
            {
                $this->fields[$name]["validators"][] =
                array(
                    "type" => "required",
                    "parameter" => ""
                );
            }
            
            switch($field["type"])
            {
            	case "integer":
            	case "double":
            		$this->fields[$name]['validators'][] =
            		array(
            		    "type" => "numeric",
            		    "parameter" => ""
            		);
            		break;
            	case "date":
            		$this->fields[$name]['validators'][] =
            		array(
            		    "type" => "date",
            		    "parameter" => ""
            		);
            	   break;
            }

            if($field["unique"])
            {
                $this->fields[$name]["validators"][] =
                array(
                    "type" => "unique",
                    "parameter" => ""
                );
            }
        }
        
        $this->fixedConditions = implode(" AND ", $fixedConditions);
        $this->datastore->referencedFields = $this->referencedFields;
        $this->datastore->explicitRelations = $this->explicitRelations;
        $this->datastore->fields = $this->fields;
        $this->datastore->database = $this->database;
        $this->datastore->storedFields = $this->storedFields;
        $this->datastore->keyField = $this->keyField;
    }
}
