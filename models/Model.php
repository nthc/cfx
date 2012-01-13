<?php
/**
 * A model represents an abstract data storing entity. Models are used to access
 * data.
 *  
 * @author james
 */
abstract class Model implements ArrayAccess
{
    const MODE_ASSOC = "assoc";
    const MODE_ARRAY = "array";
    
    const TRANSACTION_MODE_ADD = "add";
    const TRANSACTION_MODE_EDIT = "edit";

    /**
     * @todo rename this to hooks
     * @var unknown_type
     */
    //protected $services;

    public $name;
    public $prefix;
    public $package;
    public $database;
    public $label;
    public $description;
    public $showInMenu;
    
    public $queryResolve = true;
    public $queryExplicitRelations = false;
    public $queryMode = Model::MODE_ASSOC;
    
    public $storedFields;
    public $referencedFields = array();
    private $runValidations = true;
    public $fixedConditions;
    public $fixedValues = array();
    public $explicitRelations = array();
    public $keyField;
    public $assumedTransactionMode;

    /**
     *
     * @var Array
     */
    protected $fields;
    
    /**
     * 
     * @var DataStore
     */
    public $datastore;
    private static $instances = array();
    
    public static function getDatastoreInstance()
    {
        if(count(Model::$instances) > 0)
        {
            return reset(Model::$instances)->datastore;
        }
        else
        {
            return SQLDatabaseModel::getDatastoreInstance();
        }
    }

    /**
     * 
     * @param $model
     * @param $serviceClass
     * @return Model
     */
    public static function load($model, $path=null, $cached=true)
    {
        global $redirectedPackage;
        $modelName = (substr($model,0,1)=="." ? $redirectedPackage:"") . $model;
        
        if(!isset(Model::$instances[$modelName]))
        {

            if($cached && CACHE_MODELS)
            {
                if(!Cache::exists("model_$modelName"))
                {
                    Model::$instances[$modelName] = Cache::add("model_$modelName", Model::_load($model, $path));
                }
                else
                {
                    add_include_path(Cache::get("model_path_$modelName"), false);
                    Model::$instances[$modelName] = Cache::get("model_$modelName");
                }
            }
            else
            {
                Model::$instances[$modelName] = Model::_load($model, $path);
            }
        }
        
        return Model::$instances[$modelName];
    }
    
    private static function _load($model, $path)
    {
        global $packageSchema;
        global $redirectedPackage;
        
        $model = (substr($model,0,1)=="." ? $redirectedPackage:"") . $model;
        $model_path = SOFTWARE_HOME . ($path==null?Application::$packagesPath:$path)."app/modules/".str_replace(".","/",$model)."/";
        $modelClassName = Application::camelize($model) . "Model";
        add_include_path($model_path, false);
        $array = explode(".", $model);
        $model_name = array_pop($array);

        if(file_exists("$model_path/model.xml"))
        {
            if(CACHE_MODELS) Cache::add("model_path_$model", $model_path);
            $instance = XMLDefinedSQLDatabaseModel::create($model_path,$model_name,$model,$path);
            $instance->postInitHook();
        }
        else if(file_exists("$model_path/$modelClassName.php"))
        {
            if(CACHE_MODELS) Cache::add("model_path_$model", $model_path);
            $instance = new $modelClassName($model, $model_name);
            $instance->postInitHook();
        }
        else
        {
            $modelPathArray = explode(".", $model);
            $baseModelPath = SOFTWARE_HOME . ($path==null?Application::$packagesPath:$path)."app/modules/"; 
            foreach($modelPathArray as $index => $path)
            {
                $baseModelPath = $baseModelPath . "$path/";
                if(file_exists($baseModelPath . "package_redirect.php"))
                {
                    include $baseModelPath . "package_redirect.php";
                    $modelPathArray = array_slice($modelPathArray, $index + 1);
                    $modelClassName = $package_name . Application::camelize(implode(".", $modelPathArray)) . "Model";
                    $modelIncludePath = SOFTWARE_HOME . "app/lib/" . $redirect_path . "/" . implode("/" , $modelPathArray);
                    $packageSchema = $package_schema;
                    $redirectedPackage = $redirectedPackage == "" ? $package_path : $redirectedPackage;
                    add_include_path($modelIncludePath, false);
                    $instance = new $modelClassName($model, $model_name);
                    $instance->postInitHook();
                    if(CACHE_MODELS) Cache::add("model_path_$model", $modelIncludePath);
                }
            }
            if($instance == null)
            {
                throw new Exception("Failed to load Model [$model]", $code);
            }
        }
        return $instance;
    } 
    
    public function escape($text)
    {
        return $this->datastore->escape($text);
    }

    public static function resolvePath($path)
    {
        $path_array = explode(".",$path);
        $field_name = array_pop($path_array);
        $model_name = implode(".",$path_array);
        return array("model"=>$model_name, "field"=>$field_name);
    }

    public function getLabels($fields = null, $key = false)
    {
        $labels = array();
        if($fields==null)
        {
            foreach($this->fields as $field)
            {
                $labels[] = $field["label"];
            }
            if(!$key) array_shift($labels);
        }
        else
        {
            foreach($fields as $header_field)
            {
                if(array_key_exists((string)$header_field,$this->fields))
                {
                    $labels[] = $this->fields[(string)$header_field]["label"];
                }
                else
                {
                    $labels[] = "Concatenated Field";
                }
            }
        }
        return $labels;
    }

    public function getData()
    {
        return $this->datastore->data;
    }
    
    public function setData($data,$primary_key_field=null,$primary_key_value=null)
    {
        $this->datastore->data = $data;
        
        $primary_key_field = $primary_key_field == "" ? $this->getKeyField() : $primary_key_field;
        $primary_key_value = $primary_key_value == "" ? $data[$primary_key_field] : $primary_key_value;

        if($primary_key_field!="" && $primary_key_value !="") 
        {
            $this->datastore->tempData = $this->getWithField($primary_key_field,$primary_key_value);
            $this->assumedTransactionMode = Model::TRANSACTION_MODE_EDIT;
        } 
        else 
        {
            $this->assumedTransactionMode = Model::TRANSACTION_MODE_ADD;
        }
        
        return $this->validate();
    }
    
    public function setResolvableData($data,$primary_key_field=null,$primary_key_value=null)
    {
        $errors = array();
        foreach($data as $key => $value)
        {
            switch($this->fields[$key]["type"])
            {
            case "date":
                if($value != "")
                {
                    $data[$key] = Common::stringToTime($value);
                    if($data[$key]===false) $errors[$key][] = "Invalid Date Format";
                }
                break;

            case "datetime":
                if($value != "")
                {
                    $data[$key] = Common::stringToTime($value, true);
                    if($data[$key]===false) $errors[$key][] = "Invalid Date Format";
                }
                break;

            case "enum":
                if($data[$key]!="")
                {
                    $data[$key] = array_search(trim($value),$this->fields[$key]["options"]);
                    if($data[$key]===false)
                    {
                        $errors[$key][] = "Invalid Value '<b>$value</b>'<br/>Possible values may include <ul><li>'".implode("'</li><li>'",$this->fields[$key]["options"])."'</li></ul>";
                    }
                    $data[$key] = (string) $data[$key];
                }
                break;
            case "boolean":
                $data[$key] = $value == "Yes" ? "1" : "0";
                break;
            case "reference":

                if($data[$key]!="")
                {
                    $modelInfo = Model::resolvePath($this->fields[$key]["reference"]);
                    $model = Model::load($modelInfo["model"]);
                    $row = $model->get(array("fields"=>array($modelInfo["field"]),"conditions"=>"TRIM(UPPER({$this->fields[$key]["referenceValue"]}))=TRIM(UPPER('{$data[$key]}'))"));
                    if(isset($row[0][$modelInfo["field"]]))
                    {
                        $data[$key] = $row[0][$modelInfo["field"]];
                    }
                    else
                    {
                        $errors[$key][]="Invalid Value";
                    }
                }
                break;
            }
        }
                
        if(count($errors)==0)
        {
            return $this->setData($data,$primary_key_field,$primary_key_value);
        }
        else
        {
            return array("errors"=>$errors);
        }
    }

    public function validate()
    {
        $fields = $this->getFields();
        $numErrors = 0;
        
        if(array_search("user_id", array_keys($this->fields)) &&  $this->datastore->data["user_id"] == "")
        {
            $this->datastore->data["user_id"] = $_SESSION["user_id"];
        }

        foreach($this->fixedValues as $field => $value)
        {
        	$this->datastore->data[$field] = $value;
        }

        $errors = $this->preValidateHook();
        $numErrors = count($errors);

        if($this->runValidations)
        {
            foreach($fields as $field)
            {
                if(!isset($errors[$field["name"]])) $errors[$field["name"]] = array();
                if($field["key"] == "primary") continue;
                foreach($field["validators"] as $validator)
                {
                    $method = new ReflectionMethod(__CLASS__, "validator".ucwords($validator["type"]));
                    $ret = $method->invokeArgs($this, array($field["name"],$validator["parameter"]));
                    if($ret !== true)
                    {
                        $errors[$field["name"]][] = $ret;
                        $numErrors++;
                    }
                }
            }
        }
        
        $this->postValidateHook($errors);
        if($numErrors>0)
        {
            return array("errors"=>$errors,"numErrors"=>$numErrors);
        }
        else
        {
            return true;
        }
    }

    public function getFields($fieldList=null, $displayFields = false)
    {
        if($fieldList == null)
        {
            return $this->fields;
        }
        else
        {
            $fields=array();
            foreach($fieldList as $field)
            {
                $fields[] = $this->fields[(string)$field];
            }
            return $fields;
        }
    }
    
    public function hasField($fieldName)
    {
        return array_search($fieldName,array_keys($this->fields))===false?false:true;
    }

    public function getKeyField($type="primary")
    {
        foreach($this->fields as $name => $field)
        {
            if($field["key"]==$type) return $name;
        }
    }

    public function save()
    {
        $this->datastore->beginTransaction();
        
        $this->preAddHook();
        
        if(array_search("entry_date", array_keys($this->fields)) && $this->datastore->data["entry_date"] == "")
        {
            $this->datastore->data["entry_date"] = time();
        }
                        
        $this->datastore->setData($this->datastore->data, $this->fields);
        $ret = $this->saveImplementation();
        $this->postAddHook($ret, $this->getData());
        $this->datastore->endTransaction();
        $this->postCommitHook($ret, $this->getData());
        
        return $ret;
    }
    
    protected function saveImplementation()
    {
        return $this->datastore->save();
    }

    public function getFieldNames($key=false)
    {
        return array_keys($this->fields);
    }
    
    public function get($params=null,$mode="",$explicit_relations="",$resolve="")
    {
        if($this->fixedConditions != "")
        {
            $params["conditions"] = "(" . ($params["conditions"]==""?"":$params["conditions"] . ") AND ("). $this->fixedConditions . ")";
        }
        
        if(is_string($params["fields"]))
        {
        	$params["fields"] = explode(",", $params["fields"]);
        }

        $data = $this->datastore->get(
            $params,
            $mode === "" ? $this->queryMode : $mode,
            $explicit_relations === "" ? $this->queryExplicitRelations : $explicit_relations,
            $resolve === "" ? $this->queryResolve : $resolve
        );
        
        return $data;
    }

    public function update($field,$value)
    {
        $this->datastore->beginTransaction();
        $this->preUpdateHook($field, $value);
        $this->datastore->setData($this->datastore->data, $this->fields);
        $this->updateImplementation($field, $value);
        $this->postUpdateHook();
        $this->datastore->endTransaction();
    }
    
    protected function updateImplementation($field, $value)
    {
        $this->datastore->update($field,$value);        
    }
    
    public function delete($key_field,$key_value=null)
    {
        $this->datastore->beginTransaction();
        $this->preDeleteHook($key_field, $key_value);
        $this->deleteImplementation($key_field, $key_value);
        $this->postDeleteHook();
        $this->datastore->endTransaction();
    }
    
    protected function deleteImplementation($key_field, $key_value)
    {
        $this->datastore->delete($key_field,$key_value);
    }

    public static function getModels($path="app/modules")
    {
        $prefix = "app/modules";
        $d = dir($path);
        $list = array();

        // Go through every file in the module directory
        while (false !== ($entry = $d->read()))
        {
            // Ignore certain directories
            if($entry!="." && $entry!=".." && is_dir("$path/$entry"))
            {
                // Extract the path, load the controller and test weather this
                // role has the rights to access this controller.

                $url_path = substr(Application::$prefix,0,strlen(Application::$prefix)-1).substr("$path/$entry",strlen($prefix));
                $module_path = explode("/",substr(substr("$path/$entry",strlen($prefix)),1));
                $module = Controller::load($module_path, false);
                $list = $module->name;
                //$children = $this->generateMenus($role_id,"$path/$entry");
            }
        }
        array_multisort($list,SORT_ASC);
        return $list;
    }
    
    public function offsetGet($offset)
    {
        $data = $this->datastore->get(array("conditions"=>$this->database . "." . $this->getKeyField()."='$offset'"),$this->queryMode,$this->queryExplicitRelations, $this->queryResolve);
        return $data;
    }

    public function offsetSet($offset,$value)
    {

    }

    public function offsetExists($offset)
    {

    }

    public function offsetUnset($offset)
    {

    }    
    
    public function getWithField($field,$value)
    {
        return $this->get(array("conditions"=>"$field='$value'"),SQLDatabaseModel::MODE_ASSOC,false,false);
    }
    
    public function getWithField2($field, $value)
    {
        return $this->get(
            array("conditions"=>"$field='$value'"),
            $this->queryMode,
            $this->queryExplicitRelations,
            $this->queryResolve
       );
    }
    
    protected function preAddHook()
    {

    }

    protected function postAddHook($primaryKeyValue,$data)
    {

    }

    protected function preUpdateHook($field, $value)
    {

    }

    protected function postUpdateHook()
    {

    }

    protected function preValidateHook()
    {
        return array();
    }

    protected function postValidateHook($errors)
    {
        
    }

    protected function preDeleteHook($keyField, $keyValue)
    {
        
    }

    protected function postDeleteHook()
    {
        
    }
    
    public function postInitHook()
    {
        
    }
    
    public function postCommitHook($primaryKeyValue, $data)
    {
    	
    }

    public function validatorRequired($name,$parameters)
    {
        if((string)$this->datastore->data[$name]!=="")
        {
            return true;
        }
        else
        {
            return "The %field_name% field is required";
        }
    }
    
    public function validatorDate($name,$parameter)
    {
        if($this->datastore->data[$name] === false)
        {
            return "Invalid date format";
        }
        else
        {
            return true;
        }
    }

    public function validatorUnique($name,$parameter)
    {
        if($this->datastore->data[$name] == '' || $this->datastore->data[$name] === null) return true;
        $data = $this->getWithField($name,$this->escape($this->datastore->data[$name]));
        if(count($data)==0 || $this->datastore->checkTemp($name,$this->datastore->data[$name]))
        {
            return true;
        }
        else
        {
            return "The value of the %field_name% field must be unique.";
        }
    }

    public function validatorNumeric($name,$parameters)
    {
        if(is_numeric($this->datastore->data[$name]) || $this->datastore->data[$name] === '' || $this->datastore->data[$name] === null)
        {
            return true;
        }
        else
        {
            return "The %field_name% format is invalid";
        }
    }

    public static function getResultSum($results,$field)
    {
        $total = 0;
        foreach($results as $result )
        {
            $total += $result[$field];
        }
        return $total;
    }

    public function validatorRegexp($name,$parameter)
    {
        $ret =  preg_match($parameter,$this->datastore->data[$name])>0?true:"The %field_name% format is invalid";
        return $ret;
    }
    
    public function setQueryResolve($queryResolve)
    {
        $this->queryResolve = $queryResolve;
        return $this;
    }
    
    public function setQueryExplicitRelations($queryExplicitRelations)
    {
        $this->queryExplicitRelations = $queryExplicitRelations;
        return $this;
    }
}
