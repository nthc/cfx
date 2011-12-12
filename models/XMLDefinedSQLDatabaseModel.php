<?php

/**
 * Description of XMLDefinedSQLDatabaseModel
 *
 * @author james
 */
class XMLDefinedSQLDatabaseModel extends SQLDatabaseModel
{
    /**
     *
     * @var XMLDefinedSQLDatabaseModelHooks
     */
    protected $hooks;
    private $xml;
    private $xmlString;

    //private static $instances = array();

    public static function create($model_path,$model_name,$model_package,$path_prefix)
    {
        $hooksFile = $model_path.$model_name."Hooks.php";
        if(file_exists($hooksFile))
        {
            add_include_path($model_path);
            $hooksClass = $model_name."Hooks";
        }
        else
        {
            $hooksClass = "XMLDefinedSQLDatabaseModelHooks";
        }
        return new XMLDefinedSQLDatabaseModel($model_path."model.xml", $model_package, $path_prefix,$hooksClass);
    }
    
    public function __sleep()
    {
        $this->xmlString = $this->xml->asXml();
        return array(
            "prefix",
            "package",
            "xmlString",
            "database",
            "label",
            "name",
            "showInMenu",
            "fields",
            "explicitRelations",
            "fixedConditions",
            "referencedFields",
            "datastore",
            "hooks"
        );
    }
    
    /*public function __wakeup()
    {
        //$this->xml = simplexml_load_string($this->xmlString);
    }*/

    public function __construct($model,$package,$path,$hooksClass)
    {
        if(is_file($model))
        {
             $this->prefix = $path;
             $this->package = $package;
             $this->xml = simplexml_load_file($model);
             $this->database = (string)$this->xml["database"];
             $this->label = (string)$this->xml["label"];
             $this->name = (string)$this->xml["name"];
             $this->showInMenu = (string)$this->xml["showInMenu"];
             $description = $this->xml->xpath("/model:model/model:description");
             $this->connect();
             if(count($description) > 0)
             {
                 $this->description = (string)$description[0];
             }
             

            // Get a list of all the fields from the model into an array
            $this->fields = array();
            $field_xml = $this->xml->xpath("/model:model/model:fields/model:field");//[@type!='displayReference']");
            $this->explicitRelations = $this->xml->xpath("/model:model/model:explicitRelations/model:model");
            foreach($this->explicitRelations as $key=>$explicitRelation)
            {
                $this->explicitRelations = (string)$explicitRelation;
            }
            $fixedConditions = array();

            foreach($field_xml as $field)
            {
                $description = $field->xpath("model:description");
                $validatorsXML = $field->xpath("model:validator");
                $validators = array();
                $optionsXML = $field->xpath("model:options/model:option");
                $options = array();

                foreach($validatorsXML as $validator)
                {
                    $validators[] = array("type"=>(string)$validator["type"],"parameter"=>(string)$validator);
                }
                foreach($optionsXML as $option)
                {
                    //$options[] = array("value"=>(string)$option["value"],"label"=>(string)$option);
                    $options[(string)$option["value"]] = (string)$option;
                }

                $fieldInfo =
                array
                (
                    "name"=>(string)$field["name"],
                    "type"=>(string)$field["type"],
                    "label"=>(string)$field["label"],
                    "reference"=>(string)$field["reference"],
                    "referenceValue"=>$this->datastore->concatenate(explode(",", (string)$field["referenceValue"])),
                    "key"=>(string)$field["key"],
                    "description"=>isset($description[0]) ? (string)$description[0] : "",
                    "validators"=>$validators,
                    "options"=>$options
                );
                
                if($fieldInfo["key"] == "primary")
                {
                    $this->keyField = $fieldInfo["name"];
                }

                if(isset($field["value"]))
                {
                    $fieldInfo["value"] = (string)$field["value"];
                    $fixedConditions[] = "{$this->database}.{$field["name"]} = '{$field["value"]}'";
                    $this->fixedValues[(string)$field["name"]] = $field["value"];
                }

                $this->fields[(string)$field["name"]] = $fieldInfo;

                if($field["type"]!="displayReference")
                {
                    $this->storedFields[(string)$field["name"]] = $fieldInfo;
                }
            }
            $this->fixedConditions = implode(" AND ", $fixedConditions);
        }
        else
        {
            throw new Exception("Could not load XML defined model from $model!");

        }

        $references = $this->getXpathArray("/model:model/model:fields/model:field/@reference");
        $fields = $this->getXpathArray("/model:model/model:fields/model:field[@reference!='']/@name");
        $values = $this->getXpathArray("/model:model/model:fields/model:field[@reference!='']/@referenceValue");
        $return = array();

        for($i = 0; $i < count($references); $i++)
        {
            $reference = array();
            $reference["referencing_field"] = $fields[$i];
            $reference["reference"] = $references[$i];
            $reference["referenced_value_field"] = $this->datastore->concatenate(explode(",",$values[$i]));

            $fieldInfo = model::resolvePath($reference["reference"]);
            $tempModel = model::load($fieldInfo["model"],$this->prefix);
            $table = $tempModel->getDatabase();
            $reference["table"] = (string)$table;
            $reference["referenced_field"] = $fieldInfo["field"];
            $return[] = $reference;
        }

        $this->referencedFields = $return;
        $this->datastore->referencedFields = $this->referencedFields;
        $this->datastore->explicitRelations = $this->explicitRelations;
        $this->datastore->fields = $this->fields;
        $this->datastore->database = $this->database;
        $this->datastore->storedFields = $this->storedFields;
        $this->datastore->keyField = $this->keyField;

        $this->hooks = new $hooksClass;
        $this->hooks->setModel($this);

        //parent::__construct();
    }

    private function getXpathArray($xpath)
    {
        $elements_xml = $this->xml->xpath($xpath);
        $elements = array();
        if($elements_xml!=null)
        {
            foreach($elements_xml as $element)
            {
                $elements[] = "".$element[0];
            }
        }
        return $elements;
    }

    public function save()
    {
        $ret = $this->hooks->save();
        if($ret === false)
        {
            return parent::save();
        }
        else
        {
            return $ret;
        }
    }

    public function update($field, $value)
    {
        $ret = $this->hooks->update($field, $value);
        if($ret === false)
        {
            parent::update($field, $value);
        }
    }

    public function delete($field, $value = null)
    {
        $ret = $this->hooks->delete($field, $value);
        if($ret === false)
        {
            parent::delete($field, $value);
        }
    }

    public function preAddHook()
    {
        $this->hooks->setData($this->getData());
        $this->hooks->preAdd();
        $this->setData($this->hooks->getData());
    }

    public function postAddHook($primaryKeyValue,$data)
    {
        $this->hooks->postAdd($primaryKeyValue,$data);
        $this->setData($this->hooks->getData());
    }
    
    public function preValidateHook()
    {
        $this->hooks->setData($this->getData());
        return $this->hooks->preValidate();
    }

    public function postValidateHook($errors)
    {
        $this->hooks->setData($this->getData());
        return $this->hooks->postValidate($errors);
    }

    public function preDeleteHook($keyField, $keyValue)
    {
        $this->hooks->preDelete($keyField, $keyValue);
    }

    public function postDeleteHook()
    {
        $this->hooks->postDelete();
    }

    public function preUpdateHook($keyField, $keyValue)
    {
        $this->hooks->setData($this->getData());
        $this->hooks->preUpdate($keyField, $keyValue);
        $this->setData($this->hooks->getData());
    }

    public function validate()
    {
        $this->hooks->setData($this->getData());
        $ret = Model::validate();
        if($ret===true)
        {
            $ret = $this->hooks->validate();
        }
        return $ret;
    }
}
