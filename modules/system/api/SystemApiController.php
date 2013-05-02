<?php
class SystemApiController extends Controller
{
    private $format = "json";

    public function __construct()
    {
        ini_set('html_errors', 'Off');
        if(substr($_REQUEST["q"],0,10) == "system/api") Application::$template = "";
        $this->format = isset($_REQUEST["__api_format"]) ? $_REQUEST["__api_format"] : $this->format;
        unset($_REQUEST["__api_format"]);
        unset($_REQUEST["q"]);
        
        if(isset($_REQUEST['__api_key']) && isset($_REQUEST['__api_signature']))
        {
            foreach($_POST as $key => $value)
            {
                $aggregatedKey .= $key . substr($_POST[$key], 0, 15);
            }
            
            foreach($_GET as $key => $value)
            {
                if($key == '__api_key' || $key == '__api_signature' || $key == '__api_format' || $key == '__api_session_id' || $key == 'q')
                {
                    continue;
                }
                $aggregatedKey .= $key . substr($_GET[$key], 0, 15);
            }
            try{
                @$apiKey = reset(Model::load('system.api_keys')->setQueryResolve(false)->getWithField2('key', $_REQUEST['__api_key']));
                if($apiKey['active'] == 't')
                {
                    $signature = sha1($aggregatedKey . $apiKey['secret']);
                    if($signature == $_GET['__api_signature'])
                    {
                        $_SESSION['logged_in'] = true;
                        $_SESSION['user_id'] = $apiKey['user_id'];
                    }
                }
            }
            catch(Exception $e)
            {
                print $this->format(array("success"=>false,  "message"=>$e->getMessage()));
                die();                
            }
        }

        if($_SESSION["logged_in"]==false && $_GET["q"] != "api/login")
        {
            print $this->format(array("success"=>false, "status"=>101, "message"=>"Not authenticated"));
            die();
        }
    }
    
    /**
     * 
     * @param type $params
     * @return type
     * @deprecated since version 1.1
     */
    public function func($params)
    {
        $className = array_shift($params);
        $methodName = array_shift($params);
        $method = new ReflectionMethod($className, $methodName);
        $ret = $method->invokeArgs(null, $params);
        return json_encode($ret);
    }
    
    public function call_function($params)
    {
        $className = array_shift($params);
        $methodName = array_shift($params);
        try{
            $method = new ReflectionMethod($className, $methodName);
            $ret = $method->invokeArgs(null, $params);
            return json_encode(
                array('success' => true, 'response' => $ret)
            );
        }
        catch(Exception $e)
        {
            return json_encode(
                array('success' => false, 'message' => $e->getMessage())
            );
        }
    }
    
    public function getContents()
    {

    }

    public function rest($params)
    {
        if(is_numeric(end($params)))
        {
            $id = array_pop($params);
        }
        $model = Model::load(implode('.', $params));
        switch($_SERVER['REQUEST_METHOD'])
        {
            case 'GET':
                $conditions = array();
                if(isset($_GET['conditions']))
                {
                    $conditions[] = $_GET['conditions'];
                }
                if($id != '')
                {
                    $conditions[] = $model->getKeyField() . "='$id'";
                }

                $data = $model->get(
                    array(
                        'conditions' => implode(" AND ", $conditions)
                    ),
                    Model::MODE_ASSOC,
                    true,
                    false
                );
                break;
            
            // Create a new entry
            case 'POST':
                $validate = $model->setData(
                    $_POST
                );
                if($validate === true)
                {
                    $id = $model->save();
                    $data = array(
                        'success'   =>  true,
                        'id'        =>  $id
                    );
                }
                else
                {
                    $data = array(
                        'success'   =>  false,
                        'errors'    =>  $validate
                    );
                }
                break;

            // Update an existing entry
            case 'PUT':
                $validate = $model->setData(
                    $_POST
                );
                if($validate === true)
                {
                    $model->update($model->getKeyField(), $id);
                    $data = array(
                        'success'   =>  true,
                        'id'        =>  $id
                    );
                }
                else
                {
                    $data = array(
                        'success'   =>  false,
                        'errors'    =>  $validate
                    );
                }
                break;

            case 'DELETE':
                $model->delete($model->getKeyField(), $id);
                $data = array(
                    'success'   =>  true,
                    'id'        =>  $id
                );
                break;
        }

        header('Content-Type: text/javascript');
        return json_encode($data);
    }

    public function number_words()
    {
        return json_encode(
            array(
                "number"=>$_REQUEST['number'],
                "words"=>Common::convert_number($_REQUEST['number'])
            )
        );
    }

    public function logout()
    {
        User::log("Logged out through API");
        $_SESSION = array();
        return $this->format(array("success"=>true, "message"=>"Logged out", "status"=>100));
        die();
    }

    private function success($message)
    {
        return $this->format(array("success"=>true, "message"=>$message, "status"=>100));
    }

    public function get_multi()
    {
        $params = json_decode($_REQUEST["params"], true);
        $data = SQLDBDataStore::getMulti($params);
        return $this->format(array("success"=>true, "data"=>$data, "status"=>100));
    }

    public function table()
    {
        global $redirectedPackage;
        $params = json_decode($_REQUEST["params"], true);
        $redirectedPackage = $params['redirected_package'];
        
        $modelTable = new MultiModelTable($prefix);
        $modelTable->setOperations($params["operations"]);
        $modelTable->setParams($params);
        $table = $modelTable->render(false);
        $footer = $modelTable->renderFooter();
        $operations = $modelTable->getOperations();
        echo json_encode(array("tbody"=>$table, "footer"=>$footer, "operations" => $operations));
        die();
    }

    public function put($params)
    {
        $model = implode(".", $params);
        $model = Model::load($model);
        $validate = $model->setData($_REQUEST);
        if($validate === true)
        {
            $model->save();
            return $this->success("Data aved");
        }
        else
        {
            return $this->format(
                array(
                    "success"=>false,
                    "status"=>104,
                    "message"=>"Error saving Data",
                    "errors"=>$validate,
                )
            );
        }
    }

    public function login()
    {
        if(preg_match("/(0100)(?<user_id>[0-9]*)/", $_REQUEST["username"], $matches) > 0)
        {
            $conditions = "user_id='{$matches['user_id']}'";
        }
        else
        {
            $conditions = "user_name='{$_REQUEST['username']}'";
        }
        
        $user = Model::load("system.users");
        $userData = $user->get(
            array(
                "fields"     => null,
                "conditions" => $conditions
            ), Model::MODE_ASSOC, false, false);

        /* Verify the password of the user or check if the user is logging in
         * for the first time.
         */
        if ($userData[0]["password"] == md5($_REQUEST["password"]) || $userData[0]["user_status"] == 2 )
        {
            switch ($userData[0]["user_status"])
            {
                case "0":
                    $return = array(
                        "success" => false,
                        "status" => 101,
                        "message" => "Account is inactive please contact system administrator",
                    );
                    break;

                case "1":
                    $return = array(
                        "success" => true,
                        "status" => 100,
                        "message" => "Logged in.",
                        'user_id' => $userData[0]['user_id'],
                        "session_id" => session_id(),
                    );
                    $_SESSION["logged_in"] = true;
                    $_SESSION["user_id"] = $userData[0]["user_id"];
                    $_SESSION["user_name"] = $userData[0]["user_name"];
                    $_SESSION["user_firstname"] = $userData[0]["first_name"];
                    $_SESSION["user_lastname"] = $userData[0]["last_name"];
                    $_SESSION["role_id"] = $userData[0]["role_id"];
                    User::log("Logged in through API");
                    break;

                case "2":
                    $return = array(
                        "success" => false,
                        "status" => 102,
                        "message" => "New account. Please log in through the web interface to setup password.",
                    );
                    break;
            }
        }
        else
        {
            $return = array(
                "success" => false,
                "status" => 101,
                "message" => "Invalid username or password",
            );
        }

        return $this->format($return);

    }

    private function xmlEncode($data, $wellFormed = false)
    {
        foreach($data as $key => $value)
        {
            if(is_array($value))
            {
                $xml .= $this->xmlEncode($value);
            }
            else
            {
                $key = htmlspecialchars($key);
                $xml .= "<$key>" . htmlspecialchars($value) . "</$key>";
            }
        }
        return $wellFormed ? "<?xml version='1.0' encoding='UTF-8'?><response>$xml</response>" : $xml;
    }

    private function format($data)
    {
        switch(strtolower($this->format))
        {
            case "json":
                header("Content-type: text/javascript");
                return json_encode($data);

            default:
                header("Content-type: text/xml");
                return $this->xmlEncode($data, true);
        }
    }
    
    public function query()
    {
        global $redirectedPackage;
        global $packageSchema;
        
        $object = unserialize(base64_decode($_REQUEST["object"]));
        
        $redirectedPackage = $object['redirected_package'];
        $packageSchema = $object['package_schema'];

        $model = Model::load($object["model"]);

        if(isset($_REQUEST["conditions"]))
        {
            $conditions = explode(",",$_REQUEST["conditions"]);
            array_pop($conditions);
            //array_shift($conditions);
            foreach($conditions as $i => $condition)
            {
                if(substr_count($condition,"=="))
                {
                    $parts = explode("==",$condition);
                    $conditions[$i] = $parts[0]."=".$parts[1];
                }
                else
                {
                    $parts = explode("=",$condition);
                    $conditions[$i] = $model->getSearch($parts[1],$parts[0]);//"instr(lower({$parts[0]}),lower('".$model->escape($parts[1])."'))>0";//$parts[0] ." in '".$model->escape($parts[1])."'";
                }
            }
            $condition_opr = isset($_REQUEST["conditions_opr"])?$_REQUEST["conditions_opr"]:"AND";
            $conditions = implode(" $condition_opr ",$conditions);
        }

        $params = array(
            "fields"=>$object["fields"],
            "sort_field"=>isset($_REQUEST["sort"])?$_REQUEST["sort"]:$object["sortField"],
            "sort_type"=>isset($_REQUEST["sort_type"])?$_REQUEST["sort_type"]:"ASC",
            "limit"=>$object["limit"],
            "offset"=>$_REQUEST["offset"],
            "conditions"=>"($conditions) " . ($object['and_conditions'] != '' ? " AND ({$object['and_conditions']})" : '')
               . ($_REQUEST['and_conditions'] != '' ? " AND ({$_REQUEST['and_conditions']})" : '')
        );

        //$data = $model->formatData();

        switch($_REQUEST["action"])
        {
            case "delete":
                $ids = json_decode($_REQUEST["params"]);
                foreach($ids as $id)
                {
                    $data = $model->getWithField($model->getKeyField(),$id);
                    $model->delete($model->getKeyField("primary"),$id);
                    User::log("Deleted ".$model->name,$data[0]);            
                }
                break;
        }

        switch($object["format"])
        {
            case "tbody":
                include "lib/tapi/Table.php";
                include "lib/tapi/ModelTable.php";
                $table = new ModelTable($prefix."/".str_replace(".","/",$object["model"])."/");        
                print json_encode(array("tbody"=>$table->render(false),"footer"=>$table->renderFooter()));
                break;

            case "json":
                $data = $model->get($params);
                print json_encode($data);
                break;
        }        
    }    
}
