<?php

/**
 * A model represents a basic data storage block. Services built around models
 * can be used to extend models to contains some form of logic which sort of
 * correspond to methods built into the models.
 *
 * @author james
 */
abstract class SQLDBDataStore extends DataStore
{
    /**
     * An instance of the SimpleXML class which is used to store the XML 
     * definition of this model.
     */
    private static $multiQueryCache;
    public $database;
    public static $activeDriver;
    public static $activeDriverClass;
    public static $logQueries = false;
    public static $logMode = "file";
    public $modelName;
    public static $lastQuery;

    public function getExpandedFieldList($fields,$references,$resolve=true,$functions=null)
    {
        if($fields == null) $fields = array_keys($this->fields);

        $expanded_fields = array();
        $r_expanded_fields = array();
        $do_join = false;

        //Go through all the fields in the system.
        foreach($fields as $field)
        {
            $subFields = explode(",",$field);
            if(count($subFields)==1)
            {
                $referred = false;
                foreach($references as $reference)
                {
                    if($reference["referencing_field"] == (string)$field)
                    {
                        $do_join = true;
                        $referred = true;
                        $r_expanded_fields[$field] = $reference["table"].".".$reference["referenced_value_field"];
                        $expanded_fields[$field] = $reference["table"].".".$reference["referenced_value_field"]." as \"{$reference["referenced_value_field"]}\"";
                        break;
                    }
                }
                if(!$referred)
                {
                    $r_expanded_fields[$field]=(count($references)>0?$this->database.".":"").(string)$field;
                    if($resolve)
                        $expanded_fields[$field]= $this->formatField($this->fields[$field],(count($references)>0?$this->database.".":"").(string)$field);
                    else
                        $expanded_fields[$field]=$this->defaultFormatField($this->fields[$field],$r_expanded_fields[$field])." as \"{$this->fields[$field]["name"]}\"";
                }
            }
            else
            {
                foreach($subFields as $subField)
                {
                    $referred = false;
                    if(!$referred)
                    {
                        $r_expanded_subFields[$subField]=(count($references)>0?$this->database.".":"").(string)$subField;
                        if($resolve)
                        {
                            $expanded_subFields[$subField]= $this->formatField($this->fields[$subField],(count($references)>0?$this->database.".":"").(string)$subField,false);
                        }
                        else
                        {
                            $expanded_subFields[$subField]=$this->defaultFormatField($this->fields[$field],$r_expanded_fields[$subField])." as \"{$this->fields[$subField]["name"]}\"";
                        }
                    }   
                }
                
                $r_expanded_fields[$field] = $this->concatenate($expanded_subFields);
                $expanded_fields[$field]=$this->concatenate($expanded_subFields);
            }
        }
        $field_list = implode(",",$expanded_fields);
        return array("fields"=>$field_list,"expandedFields"=>$r_expanded_fields,"doJoin"=>$do_join);
    }

    public function save()
    {
        $fields = array();
        $values = array();
        $relatedData = array();
        
        foreach($this->formattedData as $field => $value)
        {
            if(is_array($value))
            {
                $relatedData[$field] = $value;
            }
            else
            {
                $fields[] = $field;
                $values[] = $value;
            }
        }

        $fields = implode(",",$fields);
        $query = "INSERT INTO $this->database ($fields) VALUES ";
        $query .= "(".implode(",",$values).")";


        $this->beginTransaction();

        $this->query($query);

        
        if($this->formattedData[$this->getKeyField()]=="")
        {
            $lastval = $this->query("SELECT LASTVAL() as last");
            $keyValue = $lastval[0]["last"];
        }

        if(count($relatedData)>0)
        {
            // Save related data
            foreach($relatedData as $database => $data)
            {
                $model = Model::load($database);
                foreach($data as $row)
                {
                    $row[$this->getKeyField()] = $keyValue;
                    $model->setData($row);
                    $model->save();
                }
            }
        }
        
        $this->endTransaction();
        return $keyValue;
    }

    /**
     * 
     * @see lib/models/model#update($field, $value)
     */
    public function update($key_field,$key_value)
    {
        $fields = array(); // array_keys($this->data);
        $relatedData = array();
        $assignments = array();
        
        foreach($this->formattedData as $field => $value)
        {
            if(is_array($value))
            {
                $relatedData[$field] = $value;
            }
            else
            {
                $fields[] = $field;
                $assignments[] = "$field = ".($value === "" ? "NULL" : $value);
            }
        }

        $description = "Updated item";
        $before = $this->query("SELECT * FROM {$this->database} WHERE $key_field='$key_value'");
        $changes = $this->data;
        foreach($changes as $key => $value)
        {
            if($before[$key] === null && $value == '')
            {
                $changes[$key] = null;
            }
        }

        $query = "UPDATE {$this->database} SET ".implode(",",$assignments)." WHERE $key_field='$key_value'";
        $this->query($query);

        foreach($relatedData as $database => $data)
        {
            $model = Model::load($database);
            $model->delete($key_field, $key_value);
            foreach($data as $row)
            {
                $row[$key_field] = $key_value;
                $model->setData($row);
                $model->save();
            }
        }
    }

    public function delete($key_field, $key_value = null)
    {
        $description = 'Deleted Item';
        if($key_value == null)
        {
            $query = "DELETE FROM {$this->database} WHERE $key_field";
        }
        else
        {
            $query = "DELETE FROM {$this->database} WHERE $key_field='$key_value'";
        }
        $this->query($query);
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function getFieldNames($key=false)
    {
        return array_keys($this->fields);
    }

    public function checkTemp($field,$value,$index=0)
    {
        return $this->tempData[$index][$field] == $value;
    }

    public static function createDefaultDriver($model,$package,$path)
    {
        require "app/config.php";
        $class = new ReflectionClass($db_driver);
        return $class->newInstance($model,$package,$path);
    }
    
    public function sqlFunctionLENGTH($field)
    {
        return "LENGTH($field::varchar)";
    }
    
    public function sqlFunctionMAX($field)
    {
        return "MAX($field)";
    }

    public function sqlFunctionSUM($field)
    {
        return "SUM($field)";
    }
    
    public function sqlFunctionTO_CHAR($field)
    {
        return "TO_CHAR($field)";
    }
    
    public function applySqlFunctions($field,$functions,$index=0)
    {
        if(!isset($functions[$index])) return $field;
        $method = new ReflectionMethod(__CLASS__,"sqlFunction$functions[$index]");
        if(!isset($functions[$index+1]))
        {
            return $method->invoke($this,$field);
        }
        else
        {
            return $this->applySqlFunctions($method->invoke($this,$field),$functions,$index+1); 
        }
    }
    
    public static function getCachedMulti($params,$mode=SQLDatabaseModel::MODE_ASSOC)
    {
        $key = md5(serialize($params));
        if(!isset(SQLDBDataStore::$multiQueryCache[$key]))
        {
            SQLDBDataStore::$multiQueryCache[$key] = SQLDBDataStore::getMulti($params,$mode);
        }
        return SQLDBDataStore(SQLDBDataStore::$multiQueryCache[$key]);
    }

    /**
     * 
     * 
     * @param type $params
     * @param type $mode
     * @return type
     */
    public static function getMulti($params,$mode=SQLDatabaseModel::MODE_ASSOC)
    {
        $method = new ReflectionMethod(SQLDBDataStore::$activeDriverClass, "getMulti");
        return $method->invokeArgs(null, func_get_args());
    }
    
    public static function log($query)
    {
    	switch (SQLDBDataStore::$logMode)
    	{
    		case "print":
    			print $query . "\n";
    			break;
    		case "file":
    		    Logger::setPath("app/logs/sql.log");
    			Logger::log($query);
    			break;
    		
    	}
    }
    
    public abstract function createSequence($name);
    public abstract function dropSequence($name);
    public abstract function getSequenceNextValue($name);

    protected abstract function beginTransaction();
    protected abstract function endTransaction();
    protected abstract function query($query,$mode=SQLDatabaseModel::MODE_ARRAY);
    public abstract function concatenate($fields);
    public abstract function formatField($field,$value,$alias = true,$functions=null,$uniqueNames=false,$excludeNumbers = false);
    public abstract function escape($string);
    public abstract function getSearch($searchValue,$field);
}
