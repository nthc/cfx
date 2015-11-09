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
    public $modelName;

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
        $query .= "(?".   str_repeat(',?', count($values) - 1).")";


        $this->beginTransaction();

        $this->query($query, null, $values);

        
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
    public function update($keyField,$keyValue)
    {
        $fields = array(); 
        $bind = array();
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
                $assignments[] = "$field = ?"; 
                $bind[] = $value;
            }
        }

        $query = "SELECT * FROM {$this->database} WHERE $keyField = ?";
        $before = $this->query($query, null, array($keyValue), md5($query));
        $changes = $this->data;
        foreach($changes as $key => $value)
        {
            if($before[$key] === null && $value == '')
            {
                $changes[$key] = null;
            }
        }

        $bind[] = $keyValue;
        $query = "UPDATE {$this->database} SET ".implode(",",$assignments)." WHERE $keyField = ?";
        $this->query($query, null, $bind, md5($query));

        foreach($relatedData as $database => $data)
        {
            $model = Model::load($database);
            $model->delete($keyField, $keyValue);
            foreach($data as $row)
            {
                $row[$keyField] = $keyValue;
                $model->setData($row);
                $model->save();
            }
        }
    }

    public function delete($keyField, $keyValue = null)
    {
        if($keyValue == null)
        {
            $query = "DELETE FROM {$this->database} WHERE $keyField";
            $this->query($query);
        }
        else
        {
            $query = "DELETE FROM {$this->database} WHERE $keyField = ?";
            $this->query($query, null, array($keyValue));
        }
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
        $results = false;
        if(isset($params['conditions'])) {
            throw new Exception("Use of conditions in queries deprecated");
        }
        if(self::isSelectCacheable($params))
        {
            $results = self::executeCachedSelectQuery($params, $mode);
            if($results !== false)
            {
                if($params["moreInfo"] === true) {
                    $data = Cache::get("{$params['cache_key']}_meta");
                    $data['data'] = $results;
                    return $data;
                }
            }
        }
        
        if($results === false)
        {
            $method = new ReflectionMethod(SQLDBDataStore::$activeDriverClass, "getMulti");
            $results = $method->invokeArgs(null, [$params, $mode]);           
        }
        
        return $results;
    }
    
    private static function isSelectCacheable($params)
    {
        return count($params) > 0 && !isset($params['conditions']);
    }
    
    private static function getQueryKey($params)
    {
        if(isset($params['cache_key']))
        {
            return $params['cache_key'];
        }
        else
        {
            unset($params['bind']);
            $limit = isset($params['limit']);
            $offset = isset($params['offset']);
            unset($params['limit']);
            unset($params['offset']);
            return sha1(serialize([$params, $limit, $offset])) . "_query";
        }
    }
        
    private static function executeCachedSelectQuery(&$params, $mode)
    {
        $results = false;
        $queryKey = self::getQueryKey($params);
        if(Cache::exists($queryKey))
        {
            $query = Cache::get($queryKey);
            if(isset($params['limit'])) $params['bind'][] = $params['limit'];
            if(isset($params['offset'])) $params['bind'][] = $params['offset'];
            $results = Db::boundQuery($query, Db::$defaultDatabase, $params['bind'], $mode, $queryKey);
            $params['cache_key'] = $queryKey;
        }
        else
        {
            if(isset($params['filter']))
            {
                $params['filter'] = FilterCompiler::compile($params['filter']);
            }
            $params['cache_key'] = $queryKey;
        }        
        return $results;
    }
    
    public function get($params = null, $mode = Model::MODE_ASSOC, $explicit_relations = false, $resolve = true)
    {
        $results = false;
        if(self::isSelectCacheable($params))
        {
            $params['model_name_entropy'] = $this->modelName;
            $results = self::executeCachedSelectQuery($params, $mode);
        }
        
        if($results === false)
        {
            $results = $this->localGet($params, $mode, $explicit_relations, $resolve);
        }
        return $results;
    }
    
    public abstract function createSequence($name);
    public abstract function dropSequence($name);
    public abstract function getSequenceNextValue($name);
    
    protected abstract function localGet($params = null, $mode = Model::MODE_ASSOC, $explicit_relations = false, $resolve = true);
    protected abstract function beginTransaction();
    protected abstract function endTransaction();
    protected abstract function query($query,$mode=SQLDatabaseModel::MODE_ARRAY, $bind = null, $key = FALSE);
    public abstract function concatenate($fields);
    public abstract function formatField($field,$value,$alias = true,$functions=null,$uniqueNames=false,$excludeNumbers = false);
    public abstract function escape($string);
    public abstract function getSearch($searchValue,$field);
}
