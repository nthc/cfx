<?php
/**
 * An implementation of a postgresql datastore. This model is used to store the 
 * postgresql data.
 *
 * @author james
 *
 */
class Postgresql extends SQLDBDataStore
{
    protected static $_conn = null;
    private static $namesSeen = array();
    private static $nesting = 0;

    public function __construct()
    {
        if(!is_resource(self::$_conn))
        {
            self::$_conn = Db::get();
        }
    }
    
    public function __wakeup()
    {
        if(!is_resource(self::$_conn))
        {
            self::$_conn = Db::get();
        }
    }

    public function beginTransaction()
    {
        if(self::$nesting == 0)
        {
            $this->query("BEGIN");
        }
        self::$nesting++;
    }

    public function endTransaction()
    {
        self::$nesting--;
        if(self::$nesting == 0)
        {
            $this->query("COMMIT");
        }
    }

    public function get($params=null,$mode=model::MODE_ASSOC, $explicit_relations=false,$resolve=true)
    {
        $fields = isset($params["fields"]) ? $params["fields"] : null;
        $conditions = isset($params["conditions"]) ? $params["conditions"] : null;
        $rows = array();
        $joined = null;
        $sorting = null;

        if($resolve)
        {
            $references = $this->referencedFields;//getReferencedFields();
        }
        else
        {
            $references = array();
        }

        $joins = "";
        //$do_join = count($references)>0?true:false;

        $fieldList = $this->getExpandedFieldList($fields,$references,$resolve);
        $field_list = $fieldList["fields"];
        $expanded_fields = $fieldList["expandedFields"];
        $do_join = $fieldList["doJoin"];

        $sortFields = isset($params["sort_field"]) ? $params["sort_field"] : null;
        if(is_array($sortFields))
        {
            if(count($sortFields) > 0)
            {
                $orderClauses = array();
                foreach($sortFields as $sortField)
                {
                    $orderClauses[] = "{$sortField["field"]} {$sortField["type"]}";
                }
                $sorting = " ORDER BY ".implode(", ", $orderClauses);
            }
        }
        else
        {
            if($sortFields != "")
            {
                $sorting = " ORDER BY $sortFields {$params["sort_type"]}";
            }
        }

        $joined_tables = array();
        foreach($references as $reference)
        {
            if(array_search($reference["table"],$joined_tables)===false)
            {
                $joins .= " LEFT JOIN {$reference["table"]} ON {$this->database}.{$reference["referencing_field"]} = {$reference["table"]}.{$reference["referenced_field"]} ";
                $joined_tables[] = $reference["table"];
            }
        }
        
        if($this->database != "")
        {
            $from = " FROM {$this->database} ";
        }

        if(get_property($params, "enumerate")===true)
        {
            $query = sprintf("SELECT ".($params["distinct"]===true?"DISTINCT":"")." COUNT(*) as \"count\" $from ").($do_join?$joins:"").($conditions!=null?" WHERE ".$conditions:"").$sorting;
        }
        else
        {
            $query = sprintf("SELECT ".(get_property($params,"distinct")===true?"DISTINCT":"")." $field_list $from ").($do_join?$joins:"").($conditions!=null?" WHERE ".$conditions:"").$sorting;
        }
        
        if(isset($params["limit"]))
        {
            $query .= " LIMIT {$params["limit"]}";
        }
        
        if(isset($params["offset"]))
        {
            $query .= " OFFSET {$params["offset"]}";    
        }
        
        $rows = $this->query($query,$mode);
        
        // Retrieve all explicitly related data
        if($explicit_relations)
        {
            foreach($this->explicitRelations as $explicitRelation)
            {
                foreach($rows as $i => $row)
                {
                    $model = Model::load((string)$explicitRelation);
                    $data = $model->get(array("conditions"=>$model->getDatabase().".".$this->getKeyField()."='".$row[$this->getKeyField()]."'"),SQLDatabaseModel::MODE_ASSOC,false,false);
                    $rows[$i][(string)$explicitRelation] = $data;
                }
            }
        }
        
        return $rows;
    }

    public function escape($string)
    {
        if(is_string($string) || is_numeric($string)) return pg_escape_string($string);
    }

    public function getSearch($searchValue,$field)
    {
        return sprintf("lower(%s::varchar) LIKE '%%%s%%'", $field, strtolower($searchValue));
        //return "position (lower('".$this->escape($searchValue)."'::varchar) in lower($field::varchar))>0";
    }

    public function concatenate($fields)
    {
        return count($fields)>1 ? "TRIM(".implode(" || ' ' || ",$fields).")":$fields[0];
    }

    public function query($query,$mode = SQLDatabaseModel::MODE_ASSOC)
    {
        //$connection = Db::getCachedInstance();
        $rows = array();
        if(SQLDBDataStore::$logQueries) SQLDBDataStore::log($query);
        if(mb_detect_encoding($query) != 'UTF-8') $query = mb_convert_encoding($query, 'UTF-8', mb_detect_encoding($query));
        SQLDBDataStore::$lastQuery = $query;
        $rows = Db::query($query, Db::$defaultDatabase, $mode);
        
        if($rows === false)
        {
            $errorMessage = pg_errormessage(Db::getCachedInstance(Db::$defaultDatabase));
            Db::query("ROLLBACK", Db::$defaultDatabase);
            throw new Exception("PGSQL Says $errorMessage query :$query");
        }
        
        self::$namesSeen = array();
        
        return $rows;
    }
    
    public function setData($data, $fields)
    {
        $this->data = $data;
        $this->formattedData = array();
        
        foreach($data as $field=>$value)
        {
            if(is_array($value))
            {
                $this->formattedData[$field] = $value;
            }
            else if($value === "" || $value === null)
            {
                $this->formattedData[$field] = "null";
            }
            else
            {
               switch($fields[$field]["type"])
               {
                  
                  case "boolean":
                      if(is_string($value))
                      {
                          $this->formattedData[$field] = ($value == 'f' || $value == 'false' || $value=='0' || $value == null) ? 'FALSE' : 'TRUE';
                      }
                      else
                      {
                          $this->formattedData[$field] = $value == true ? 'TRUE' : 'FALSE';
                      }
                      break;
                   
                  case "number":
                  case "double":
                  case "integer":
                  case "reference":
                      $this->formattedData[$field] = is_numeric($value) ? $value : $this->escape($value);
                      break;
                  
                  case "datetime":
                      $this->formattedData[$field] = $value == "" ? "null" : sprintf("'%s'", date("Y-m-d H:i:s",$value));
                      break;
                    
                  case "date":
                      $this->formattedData[$field] = 
                      $value == "" ? "null" : sprintf("'%s'", date("Y-m-d",$value));
                      break;
                      
                  case "binary":
                      $this->formattedData[$field] = 
                      $value == "" ? null : sprintf("decode('%s', 'hex')", bin2hex($value));
                      break;
                  default:
                      $this->formattedData[$field] = sprintf("'%s'", $this->escape($value));
                }
            }
        }        
    }
    
    public function defaultFormatField($field,$value)
    {
        switch($field["type"])
        {
            case "date":
            case "datetime":
                $ret = "date_part('epoch', \"timestamp\"($value))";
                break;

            default:
                $ret = $value;
        }
        return $ret;
    }

    public function formatField($field,$value,$alias = true,$functions=null,$uniqueNames=false,$excludeNumbers = false)
    {
        if($uniqueNames)
        {
            if(array_search($field["name"], self::$namesSeen) !== false)
            {
                $prepend = count(array_keys(self::$namesSeen, $field["name"]));
            }
            $aliasValue = $field["name"].$prepend;
            self::$namesSeen[] = $aliasValue;
        }
        else
        {
            $aliasValue = $field["name"];
        }
        
        switch($field["type"])
        {
            case "date":
                $ret =  "INITCAP(TO_CHAR($value,'DD/MM/YYYY'))";
                break;
                
            case "datetime":
                $ret = "INITCAP(TO_CHAR($value,'DD/MM/YYYY HH24:MI:SS'))";
                break;
            
            case "enum":
                $query = "CASE ";
                foreach($field['options'] as $val=>$option)
                {
                    $query .= "WHEN $value='$val' THEN '$option' ";
                }
                $query .= " END";
                $ret = $query;
                break;
            case "boolean":
                $ret = "CASE WHEN $value='1' THEN 'Yes' WHEN $value='0' THEN 'No' END";
                break;
            case "number":
            case "double":
                if(!$excludeNumbers === true)
                {
                    $ret = "TRIM(TO_CHAR($value,'fm999,999,999,999,999,990.90'))";
                }
                else
                {
                    $ret = $value;
                }
                break;

            case "displayReference":
                $ret = $value;
                break;

            default:
                $ret = $value;
                break;
        }

        if(is_array($functions))
        {
            $ret = $this->applySqlFunctions($ret,$functions);
        }
        
        if($alias) $ret .=" as \"$aliasValue\"";
        return $ret;
    }
    
    
    public static function getMulti($params,$mode=SQLDatabaseModel::MODE_ASSOC)
    {
        //Load all models
        $fields = array();
        $field_infos = array();
        $models = array();
        $fieldDescriptions = array();
        $headers = array();
        $fixedConditions = array();
        
        foreach($params["fields"] as $field)
        {
            $fieldreferences = explode(", ",$field); 
            if(count($fieldreferences)==1)
            {
                $fields[]=(string)$field; 
                $field_infos[] = Model::resolvePath((string)$field);
            }
            else
            {
                $fields[] = $fieldreferences;
                foreach($fieldreferences as $ref)
                {
                    $infos[] = Model::resolvePath((string)$ref); 
                }
                $field_infos[] = $infos;
            }
        }
        
        foreach($fields as $i=>$field)
        {
            if(is_array($field))
            {
                foreach($field_infos[$i] as $info)
                {
                    if(array_search($info["model"],array_keys($models))===false)
                    {
                        $models[$info["model"]] = Model::load($info["model"]);
                    }
                }
            }
            else
            {
                if(array_search($field_infos[$i]["model"],array_keys($models))===false)
                {
                    $models[$field_infos[$i]["model"]] = Model::load($field_infos[$i]["model"]);
                    if($models[$field_infos[$i]["model"]]->fixedConditions != "") {
                        $fixedConditions[] = $models[$field_infos[$i]["model"]]->fixedConditions;
                    }
                }
            }
        }

        if(count($fixedConditions) > 0)
        {
            $params["conditions"] = ($params["conditions"]==""?"":"(". $params["conditions"] . ") AND ")."(" . implode(" AND ", $fixedConditions). ")";
        }

        //Buld the query
        $query = "SELECT ";
        $fieldList = array();
        $rawFields = array();
        $functions = $params["global_functions"];
        
        foreach($fields as $i => $field)
        {
            $field_info = $field_infos[$i];
            if(is_array($field))
            {
                $concatFieldList = array();
                foreach($field_info as $info)
                {
                    $fieldData = $models[$info["model"]]->getFields(array($info["field"]));
                    $concatFieldList[] = $models[$info["model"]]->datastore->formatField($fieldData[0],$models[$info["model"]]->getDatabase().".".$info["field"],true,null,true);
                    $rawFields[] = $models[$info["model"]]->getDatabase().".".$info["field"];
                }
                $fieldList[] = $models[$info["model"]]->datastore->applySqlFunctions($models[$info["model"]]->datastore->concatenate($concatFieldList),$functions);
            }
            else
            {
                $fieldData = $models[$field_infos[$i]["model"]]->getFields(array($field_info["field"]));
                $fieldDescriptions[] = $fieldData[0];
                $headers[] = $fieldData[0]["label"];
                $fieldList[] = $models[$field_info["model"]]->datastore->formatField($fieldData[0],$models[$field_info["model"]]->getDatabase().".".$field_info["field"],true,$functions,true);
                $rawFields[] = $models[$field_info["model"]]->getDatabase().".".$field_info["field"];
            }
        }
        
        if($params['moreInfoOnly'] === true)
        {
            return array
                (
                    "data" => $data,
                    "fieldInfos" => $fieldDescriptions,
                    "headers" => $headers,
                    "rawFields" => $rawFields,
                );            
        }   
        
        $tableList = array();
        foreach($models as $model)
        {
            $tableList[] = $model->getDatabase();
        }
        
        $joinConditions = array();
        $hasDontJoin = is_array($params['dont_join']);
        
        foreach($models as $model)
        {
            foreach($models as $other_model)
            {
                // skip if the models are the same
                if($model->name == $other_model->name) continue;            
                                
                if($model->hasField($other_model->getKeyField()))
                {
                    if($hasDontJoin)
                    {
                        if(array_search("{$model->package},{$other_model->package}", $params['dont_join']) !== false) continue;
                        if(array_search("{$model->package}.{$other_model->getKeyField()},{$other_model->package}.{$other_model->getKeyField()}", $params['dont_join']) !== false) continue;
                    }
                    $joinConditions[] = "{$model->getDatabase()}.{$other_model->getKeyField()}={$other_model->getDatabase()}.{$other_model->getKeyField()}";
                }
            }
        }
        
        if($params["distinct"]===true)
        {
            $query .= " DISTINCT ";
            
        }
        
        if($params["count"] === true || $params["enumerate"] === true)
        {
            $query .= ' COUNT(*) as "count" FROM ' . implode(', ',array_unique($tableList));
        }
        else if($params["sum"] === true)
        {
            $query .= "SUM(" . implode("), SUM(", $rawFields) . ") FROM " . implode(", ", array_unique($tableList));   
        }
        else if($params["max"] === true)
        {
            $query .= "MAX(" . implode("), MAX(", $rawFields) . ") FROM " . implode(", ", array_unique($tableList));
        }
        else
        {
            $query.=($params["resolve"] === false ? implode(",",$rawFields) : implode(",",$fieldList))." FROM ".implode(",",array_unique($tableList));
        }
        
        if(count($joinConditions)>0)
        {
            $query .= " WHERE (" . implode(" AND ",$joinConditions) . ") ";
            $query.=(strlen($params["conditions"])>0?" AND (".$params["conditions"].")":"");
        }
        else
        {
            $query.=(strlen($params["conditions"])>0?" WHERE ".$params["conditions"]:"");
        }

        if(is_array($params["sort_field"]) && $params["count"] !== true)
        {
            if(count($params["sort_field"]) > 0)
            {
                $query .= " ORDER BY ";
                $orderClauses = array();
                foreach($params["sort_field"] as $sortField)
                {
                    if($sortField["field"]=="") continue;
                    $orderClauses[] = "{$sortField["field"]} {$sortField["type"]}";
                }
                $query .= implode(", ", $orderClauses);
            }
        }
        else
        {
            if($params["sort_field"] != "" && $params["count"] !== true)
            {
                $query .= " ORDER BY {$params["sort_field"]} {$params["sort_type"]}";
            }
        }

        if($params["group_field"] != "")
        {
            $query .= " GROUP BY {$params["group_field"]}";
            foreach($rawFields as $field)
            {
                if($field != $params["group_field"]) $query .= ",".$field;
            }
        };

        if(isset($params["limit"]))
        {
            $query .= " LIMIT {$params["limit"]}";
            //$query = "select * from ( select  a.*, ROWNUM autoremove_oracle_rnum from ( $query ) a where ROWNUM <= ".($params["offset"]+$params["limit"])." ) where autoremove_oracle_rnum  >= ".($params["offset"]+0);
        }
        if(isset($params['offset']))
        {
            $query .= " OFFSET {$params["offset"]}";
        }
        
        $data = $other_model->datastore->query($query,$mode);

        if($params["moreInfo"] === true)
        {
            return array
                (
                    "data" => $data,
                    "fieldInfos" => $fieldDescriptions,
                    "headers" => $headers,
                    "rawFields" => $rawFields,
                );
        }
        else
        {
            return $data;
        }
    }
    
    public function createSequence($name)
    {
        $this->query("CREATE SEQUENCE $name INCREMENT 1 MINVALUE 1 START 1 CACHE 1");        
    }
    
    public function dropSequence($name)
    {
        $this->query("DROP SEQUENCE IF EXISTS $name");
    }
    
    public function getSequenceNextValue($name)
    {
        $return = $this->query("SELECT nextval('$name') as nextval");
        return $return[0]["nextval"];
    }

    public function describe()
    {
        $fields = array();
        $databaseInfo = explode(".", $this->database);
        
        $primaryKey = $this->query(
            "select column_name from 
             information_schema.table_constraints pk 
             join information_schema.key_column_usage c on 
                c.table_name = pk.table_name and 
                c.constraint_name = pk.constraint_name and
                c.constraint_schema = pk.table_schema
             where pk.table_name = '{$databaseInfo[1]}' and pk.table_schema='{$databaseInfo[0]}'
             and constraint_type = 'PRIMARY KEY'"
        );
        
        $uniqueKeys = $this->query(
            "select column_name from 
             information_schema.table_constraints pk 
             join information_schema.key_column_usage c on 
                c.table_name = pk.table_name and 
                c.constraint_name = pk.constraint_name and
                c.constraint_schema = pk.table_schema
             where pk.table_name = '{$databaseInfo[1]}' and pk.table_schema='{$databaseInfo[0]}'
             and constraint_type = 'UNIQUE'"
        );
             
        if(count($databaseInfo) == 1)
        {
            $pgFields = $this->query("select * from information_schema.columns where table_name='{$databaseInfo[0]}'");
        }
        else
        {
            $pgFields = $this->query("select * from information_schema.columns where table_schema='{$databaseInfo[0]}' and table_name='{$databaseInfo[1]}'");
        }
                
        if(count($pgFields) == 0)
        {
            throw new Exception("Database table [{$this->database}] not found.");
        }
        
        $primaryKeyFound = false;
        
        foreach($pgFields as $index => $pgField)
        {
            switch($pgField["data_type"])
            {
                case "boolean":
                case "integer":
                    $type = $pgField["data_type"];
                    break;
                case "bigint":
                    $type = "integer";
                    break;
                    
                case "numeric":
                    $type = "double";
                    break;
                    
                case "date":
                    $type = "date";
                    break;
                
                case "timestamp":
                case "timestamp without time zone":
                case "timestamp with time zone":
                    $type = "datetime";
                    break;
            
                case "character varying":
                case "character":
                    if($pgField["character_maximum_length"]<256)
                    {
                        $type = "string";
                    }
                    else
                    {
                        $type = "text";
                    }
                    break;
                
                case "point":
                case "text":
                    $type = "text";
                    break;
                case "bytea":
                	$type = "binary";
                	break;
                default:
                    throw new Exception("Unknown postgresql data type [{$pgField["data_type"]}] for field[{$pgField["column_name"]}] in table [{$this->database}]");
            }
            
            $field = array(
                "name" => strtolower($pgField["column_name"]),
                "type" => $type,
                "required" => $pgField["is_nullable"] == "NO" ? true : false
            );
            
            if($pgField["column_name"] == $primaryKey[0]["column_name"])
            {
                $field["key"] = "primary";
                $primaryKeyFound = true;
            }
            
            foreach($uniqueKeys as $uniqueKey)
            {
                if($pgField["column_name"] == $uniqueKey["column_name"])
                {
                    $field["unique"] = true;
                }
            }

            $fields[] = $field;
        }
        
        if($primaryKeyFound === false)
        {
            $fields[0]['key'] = 'primary';
        }
        
        return $fields;
    }
}
