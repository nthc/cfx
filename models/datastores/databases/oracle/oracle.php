<?php
/**
 * An implementation of an oracle datastore. This model is used to store the 
 * data in oracle databases.
 *
 * @author james
 */
class oracle extends SQLDBDataStore
{
    protected static $_conn = null;
    protected static $_s_conn = null;
    protected static $mode = OCI_COMMIT_ON_SUCCESS;
    protected static $externalTransaction = false;
    public static $log = false;
    private static $namesSeen = array();

    public function __construct()//$model="",$package="",$prefix="")
    {
        if(oracle::$_conn==null)
        {
            require "app/config.php";
            $db = "$db_host/$db_name";
            oracle::$_conn = oci_connect($db_user, $db_password, $db);        
        }
    }

    public function beginTransaction($external = true)
    {
        if($external)
        {
            oracle::$externalTransaction = $external;
            oracle::$mode = OCI_DEFAULT;
        }
        else
        {
            oracle::$mode = OCI_DEFAULT;
        }
    }

    public function endTransaction($external = true)
    {
        if($external && oracle::$externalTransaction)
        {
            oci_commit(oracle::$_conn);
            oracle::$mode = OCI_COMMIT_ON_SUCCESS;
            oracle::$externalTransaction = false;
        }
        else if(!$external && !oracle::$externalTransaction)
        {
            oci_commit(oracle::$_conn);
            oracle::$mode = OCI_COMMIT_ON_SUCCESS;
        }
    }

    public function get($params=null,$mode=model::MODE_ASSOC, $explicit_relations=false,$resolve=true)
    {
        $fields = isset($params["fields"]) ? $params["fields"] : null;
        $conditions = isset($params["conditions"]) ? $params["conditions"] : null;
        $rows = array();
        $joined = null;
        $sorting = null;

            // Get information about all referenced models and pull out all
            // the required information as well as build up the join parts
            // of the query.
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

            if(get_property($params, "enumerate")===true)
            {
                $query = sprintf("SELECT ".($params["distinct"]===true?"DISTINCT":"")." COUNT(*) as \"count\" FROM %s ",$this->database).($do_join?$joins:"").($conditions!=null?" WHERE ".$conditions:"").$sorting;
            }
            else
            {
                $query = sprintf("SELECT ".(get_property($params,"distinct")===true?"DISTINCT":"")." $field_list FROM %s ",$this->database).($do_join?$joins:"").($conditions!=null?" WHERE ".$conditions:"").$sorting;
                if(isset($params["limit"]))
                {
                    //$query = "SELECT * FROM ( $query ) where rownum <= ".($params["offset"]+$params["limit"])." and rownum >= ".($params["offset"]+0);
                    $query = "select * from ( select  a.*, ROWNUM autoremove_oracle_rnum from ( $query ) a where ROWNUM <= ".($params["offset"]+$params["limit"])." ) where autoremove_oracle_rnum  >= ".($params["offset"]+0);

                }
            }

            //print $query;
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
        return str_replace("'","''",$string);
    }

    public function getSearch($searchValue,$field)
    {
        return "instr(lower($field),lower('".$this->escape($searchValue)."'))>0";
    }

    public function concatenate($fields)
    {
        return count($fields)>1 ? "TRIM(".implode(" || ' ' || ",$fields).")":$fields[0];
    }

    public function query($query,$mode = SQLDatabaseModel::MODE_ASSOC)
    {
        oracle::$namesSeen = array();
        $rows = array();
        //print $query . "<br/><hr/>";
        $stmt = oci_parse(oracle::$_conn, $query);

        if($stmt===false)
        {
            throw new Exception("Invalid Query - $query");
        }
        if(oci_execute($stmt, oracle::$mode)===false)
        {
            throw new Exception("Invalid Query - $query");
        }

        if(oci_num_rows($stmt)==0)
        {
            switch($mode)
            {
                case SQLDatabaseModel::MODE_ASSOC:
                    $o_mode = OCI_ASSOC;
                    break;
                case SQLDatabaseModel::MODE_ARRAY:
                    $o_mode = OCI_NUM;
                    break;
            }

            while ($row = @oci_fetch_array($stmt,$o_mode + OCI_RETURN_NULLS))
            {
                unset($row["AUTOREMOVE_ORACLE_RNUM"]);
                $rows[] = $row;
            }
        }

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
            else
            {
               switch($fields[$field]["type"])
               {
                  case "datetime":
                      $this->formattedData[$field] = 
                          $value == "" ? "''" : 
                          sprintf("to_date('%s', 'yyyy-mm-dd hh24:mi:ss')",date("Y-m-d H:i:s",$value));
                      break;
                    
                  case "date":
                      $this->formattedData[$field] = 
                          $value == "" ? "''" :
                          sprintf(
                              "to_date('%s', 'yyyy/mm/dd')",
                              date("Y/m/d",$value)
                          );
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
                /*$ret = "CASE WHEN $value='' THEN NULL ELSE ($value - to_date('01-JAN-1970','DD-MON-YYYY')) * (86400) END";
                break;*/

            case "datetime":
                $ret = "CASE WHEN $value='' THEN NULL ELSE ($value - to_date('01-JAN-1970','DD-MON-YYYY')) * (86400) END";
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
            if(array_search($field["name"], oracle::$namesSeen) !== false)
            {
                $prepend = count(array_keys(oracle::$namesSeen, $field["name"]));
            }
            $aliasValue = $field["name"].$prepend;
            oracle::$namesSeen[] = $aliasValue;
        }
        else
        {
            $aliasValue = $field["name"];
        }

        switch($field["type"])
        {
            case "date":
                //if($this->dateFormat == 1)
                //{
                //    $ret =  "INITCAP(TO_CHAR($value,'DDTH MONTH YYYY'))";
                //}
                //else
                //{
                $ret =  "INITCAP(TO_CHAR($value,'DD/MM/YYYY'))";
                //}
                break;
                
            case "datetime":
                //if($this->dateFormat == 1)
                //{
                //    $ret = "INITCAP(TO_CHAR($value,'DDTH MONTH YYYY HH:MI:SS AM'))";
                //}
                //else if($this->dateFormat == 2)
                //{
                    $ret = "INITCAP(TO_CHAR($value,'DD/MM/YYYY HH:MI:SS'))";
                //}
                
                break;
            
            case "enum":
                //var_dump($field["options"]);
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
            $fieldreferences = explode(",",$field); 
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
                    $concatFieldList[] = $models[$info["model"]]->datastore->formatField($fieldData[0],$models[$info["model"]]->getDatabase().".".$info["field"],false,null,true);
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

        $tableList = array();
        foreach($models as $model)
        {
            $tableList[] = $model->getDatabase();
        }
        
        $joinConditions = array();
        foreach($models as $model)
        {
            foreach($models as $other_model)
            {
                if($model->name == $other_model->name) continue;
                if($model->hasField($other_model->getKeyField()))
                {
                    $joinConditions[] = "{$model->getDatabase()}.{$other_model->getKeyField()}={$other_model->getDatabase()}.{$other_model->getKeyField()}";
                }
            }
        }
        
        if($params["count"] === true)
        {
            $query .= ' COUNT(*) as "count" FROM ' . implode(', ',array_unique($tableList));
        }
        else if($params["sum"] === true)
        {
            $query .= "SUM(" . implode("), SUM(", $rawFields) . ") FROM " . implode(", ", array_unique($tableList));   
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

        if(is_array($params["sort_field"]))
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
            if($params["sort_field"] != "")
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
            $query = "select * from ( select  a.*, ROWNUM autoremove_oracle_rnum from ( $query ) a where ROWNUM <= ".($params["offset"]+$params["limit"])." ) where autoremove_oracle_rnum  >= ".($params["offset"]+0);
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

    public function describe()
    {
        $fields = array();
        $primaryKey = $this->query("select column_name from all_cons_columns join all_constraints USING(constraint_name) where all_constraints.table_name=UPPER('{$this->database}') and constraint_type='P'");
        $oracleFields = $this->query("SELECT * FROM user_tab_cols WHERE table_name =UPPER('{$this->database}')");
        foreach($oracleFields as $oracleField)
        {
            if($oracleField["DATA_TYPE"]=="NUMBER" && $oracleField["DATA_PRECISION"]=="1")
            {
                $type = "boolean";
            }
            else if($oracleField["DATA_TYPE"]=="NUMBER")
            {
                $type = "double";
            }
            else if($oracleField["DATA_TYPE"]=="DATE")
            {
                $type = "date";
            }
            else if($oracleField["DATA_TYPE"]=="VARCHAR2")
            {
                if($oracleField["DATA_LENGTH"]<256)
                {
                    $type = "string";
                }
                else
                {
                    $type = "text";
                }
            }
            else
            {
                throw new Exception("Unknown oracle data type");
            }
            
            $fields[] = array(
                "name" => strtolower($oracleField["COLUMN_NAME"]),
                "type" => $type,
                "required" => $oracleField["NULLABLE"] == "N" ? true : false
            );
            
            if($oracleField["COLUMN_NAME"] == $primaryKey[0]["COLUMN_NAME"])
            {
                $fields[count($fields)-1]["type"] = "integer";
                $fields[count($fields)-1]["key"] = "primary";
                $tempField = $fields[0];
                $fields[0] = $fields[count($fields)-1];
                $fields[count($fields)-1] = $tempField;
            }
            else
            {
                $fields[0]["key"] = "primary";
            }            
        }     
        return $fields;
    }
}
