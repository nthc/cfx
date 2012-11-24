<?php
class SystemApiController extends Controller
{
    private $format = "json";

    public function __construct()
    {
        if(substr($_REQUEST["q"],0,10) == "system/api") Application::$template = "";
        $this->format = isset($_REQUEST["__api_format"]) ? $_REQUEST["__api_format"] : $this->format;
        unset($_REQUEST["__api_format"]);
        unset($_REQUEST["q"]);

        if($_SESSION["logged_in"]==false && $_GET["q"] != "system/api/login")
        {
            print $this->format(array("success"=>false, "status"=>101, "message"=>"Not authenticated"));
            die();
        }
    }
    
    public function func($params)
    {
        $className = array_shift($params);
        $methodName = array_shift($params);
        $method = new ReflectionMethod($className, $methodName);
        $ret = $method->invokeArgs(null, $params);
        return json_encode($ret);
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
}
