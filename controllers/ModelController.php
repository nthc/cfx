<?php
/**
 * A controller for interacting with the data in models. This controller is loaded
 * automatically when the path passed to the Controller::load method points to
 * a module which contains only a model definition. This controller provides
 * an interface through which the user can add, edit, delete and also perform
 * other operations on the data store in the model.
 *
 * Extra configuration could be provided through an app.xml file which would be
 * found in the same module path as the model that this controller is loading.
 * This XML file is used to describe what fields this controller should display
 * in the table view list. It also specifies which fields should be displayed
 * in the form.
 *
 * A custom form class could also be provided for this controller. This form
 * class should be a subclass of the Form class. The name of the file in which
 * this class is found should be modelnameForm.php (where modelname represents
 * the actual name of the model). For exampld of your model is called users then
 * the custom form that this controller can pick up should be called usersForm.
 *
 * @ingroup Controllers
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 * 
 */
class ModelController extends Controller
{
    /**
     * An instance of the model that this controller is linked to.
     * @var Model
     */
    protected $model;

    /**
     * The name of the model that this controller is linked to.
     * @var string
     */
    public $modelName;

    /**
     * The URL path through which this controller's model can be accessed.
     * @var string
     */
    public $urlPath;

    /**
     * The local pathon the computer through which this controllers model can be
     * accessed.
     * @var string
     */
    protected $localPath;

    /**
     * An instance of the template engine.
     * @todo Take this variable out so that the output is handled by a third party;
     * @var TemplateEngine
     */
    private $t;

    /**
     * An instance of the Table class that is stored in here for the purpose
     * of displaying and also manipulating the model's data.
     * @var Table
     */
    protected $table;

    /**
     * An instance of the simple xml object that is used to represent the app.xml
     * file which contains extra directives for the ModelController.
     * @var SimpleXMLObject
     */
    private $app;

    /**
     * An instance of the Toolbar class. This toolbar is put on top of the list
     * which is used to display the model.
     * @var Toolbar
     */
    protected $toolbar;
    
    /**
     * The controller action to be performed.
     * @var string
     */
    protected $action;
    
    /**
     * Conditions which should be applied to the query used in generating the
     * list of items in the model.
     * @var string
     */
    public $listConditions;
    
    /**
     * An array which contains a list of the names of all the fields in the model
     * which are used by this controller.
     * @var array
     */
    public $fieldNames = array();
    
    /**
     * The name of the callback method which should be called after any of the 
     * forms are submitted. This method is the heart of the model controller and it
     * determines how the data is routed around the controller. The method
     * pointed to must be a static method and it should be defined as follows.
     * 
     * @code
     * public static function callback($data,&$form,$c,$redirect=true,&$id=null)
     * {
     * }
     * @endcode
     * 
     * @var string
     * @see ModelController::callback()
     */
    protected $callbackMethod = "ModelController::callback";
    
    /**
     * An array which shows which of the fields of the model should be displayed
     * on the list view provided by the ModelController.
     * @var array
     */
    public $listFields = array();
    
    /**
     * A prefix to be used for the permission.
     * @var string
     */
    protected $permissionPrefix;
    
    /**
     * Set to true whenever the model controller is operating in API mode.
     * @var boolean
     */
    protected $apiMode = false;
    
    /**
     * Should this model controller show the add operation.
     * @var boolean
     */
    protected $hasAddOperation = true;
    
    /**
     * Should this model controller show the edit operation.
     * @var boolean
     */
    protected $hasEditOperation = true;
    
    /**
     * Should this model controller show the delete operation.
     * @var boolean
     */
    protected $hasDeleteOperation = true;
    
    protected $forceAddOperation = false;
    protected $forceEditOperation = false;
    protected $forceDeleteOperation = false;
    
    protected $historyModels = array();
    
    protected $urlBase;
    
    
    /**
     * Constructor for the ModelController.
     * @param $model An instance of the Model class which represents the model
     *               to be used.
     */
    public function __construct($model = "")
    {
        global $redirectedPackage;
        $this->modelName = ($this->modelName == "" ? $model : $this->modelName);
        $this->permissionPrefix = str_replace(".", "_", $this->modelName);
        $this->model = Model::load($this->modelName);
        $this->name = $this->model->name;
        $this->t = $t;
        $this->path = $path;
        $this->urlBase = $this->urlBase == '' ? ($redirectedPackage != '' ? "$redirectedPackage" : '') . $this->modelName : $this->urlBase;
        $this->urlPath = Application::$prefix."/".str_replace(".","/",$this->urlBase);
        $this->localPath = "app/modules/".str_replace(".","/",$this->urlBase);
        
        if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' || $_REQUEST["__api_mode"] == "yes")
        {
            Application::$template = "";
            $this->apiMode = true;
            unset($_REQUEST["__api_mode"]);
            unset($_REQUEST["q"]);
        }
        else
        {
            $this->label = $this->model->label;
            $this->description = $this->model->description;
            Application::setTitle($this->label);
            $this->toolbar = new Toolbar();
            $this->table = new MultiModelTable(Application::$prefix."/".str_replace(".","/",$this->urlBase)."/");
            $this->table->useAjax = true;
        }        
        $this->_showInMenu = $this->model->showInMenu=="false"?false:true;
        
        if(file_exists($this->localPath."/app.xml"))
        {
            $this->app = simplexml_load_file($this->localPath."/app.xml");
        }
    }
    
    /**
     * Sets up the list that is shown by default when the Model controller is
     * used. This list normall has the toolbar on top and the table below.
     * This method performs checks to ensure that the user has permissions
     * to access a particular operation before it renders the operation.
     */
    protected function setupList()
    {
        if($this->hasAddOperation)
        {
            if(User::getPermission($this->permissionPrefix . "_can_add") || $this->forceAddOperation)
            {
                $this->toolbar->addLinkButton("New",$this->name . "/add");
            }
        }

        if(User::getPermission($this->permissionPrefix."_can_export"))
        {
            $exportButton = new MenuButton("Export");
            $exportButton->addMenuItem("PDF", "#","wyf.openWindow('".$this->urlPath."/export/pdf')");
            $exportButton->addMenuItem("Data", "#","wyf.openWindow('".$this->urlPath."/export/csv')");
            $exportButton->addMenuItem("Template", "#","wyf.openWindow('".$this->urlPath."/export/csv/template')");
            $exportButton->addMenuItem("HTML", "#","wyf.openWindow('".$this->urlPath."/export/html')");
            $exportButton->addMenuItem("Excel", "#","wyf.openWindow('".$this->urlPath."/export/xls')");
            $this->toolbar->add($exportButton);//addLinkButton("Export",$this->urlPath."/export");
        }

        if(User::getPermission($this->permissionPrefix."_can_import"))
        {
            $this->toolbar->addLinkButton("Import",$this->urlPath."/import");
        }
        
        $this->toolbar->addLinkButton("Search","#")->linkAttributes="onclick=\"ntentan.tapi.showSearchArea('{$this->table->name}')\"";
    
        if($this->hasEditOperation)
        {
            if(User::getPermission($this->permissionPrefix."_can_edit") || $this->forceEditOperation)
            {
                $this->table->addOperation("edit","Edit");
            }
        }
        
        if($this->hasDeleteOperation)
        {
            if(User::getPermission($this->permissionPrefix."_can_delete") || $this->forceDeleteOperation)
            {
                $this->table->addOperation("delete","Delete","javascript:ntentan.confirmRedirect('Are you sure you want to delete','{$this->urlPath}/%path%/%key%')");
            }
        }

        if(User::getPermission($this->permissionPrefix."_can_view"))
        {
            $this->table->addOperation("view","View");
        }
        
        if(User::getPermission($this->permissionPrefix."_can_audit"))
        {
            $this->table->addOperation("audit","History");
        }          
    }

    /**
     * Default controller action. This is the default action which is executed
     * when no action is specified for a given call.
     * @see lib/controllers/Controller::getContents()
     */
    public function getContents()
    {
        if(count($this->listFields) > 0)
        {
            $fieldNames = $this->listFields;
        }
        else if($this->app != null)
        {
            $fieldNames = $this->app->xpath("/app:app/app:list/app:field");
            $concatenatedLabels = $this->app->xpath("/app:app/app:list/app:field/@label");
        }
        else
        {
            $fieldNames = array();
            $fields = $this->model->getFields();

            foreach($fields as $i => $field)
            {
                if($field["reference"] == "")
                {
                    $fieldNames[$i] = $this->model->package.".".$field["name"];
                }
                else
                {
                    $modelInfo = Model::resolvePath($field["reference"]);
                    $fieldNames[$i] = $modelInfo["model"] . "." . $field["referenceValue"];
                }
            }
        }
        
        foreach($fieldNames as $i => $fieldName)
        {
            $fieldNames[$i] = substr((string)$fieldName, 0, 1) == "." ? $this->redirectedPackage . (string)$fieldName : (string)$fieldName;
        }
        
        if(count($this->fieldNames)>0) $fieldNames = $this->fieldNames;
        
        if($this->apiMode === false)
        {
            $this->setupList();
            $params["fields"] = $fieldNames;
            $params["page"] = 0;
            $params["sort_field"] =
            array(
                array(
                    "field" =>  $this->model->database . "." . $this->model->getKeyField(),
                    "type"  =>  "DESC"
                )
            );
            $this->table->setParams($params);
            $return = '<div id="table-wrapper">' . $this->toolbar->render().$this->table->render() . '</div>';
        } 
        else
        {
            $params["fields"] = $fieldNames;
            $params["page"] = 0;
            $params["sort_field"] = 
            array(
                array(
                    "field" =>  $this->model->database . "." . $this->model->getKeyField(),
                    "type"  =>  "DESC"
                )
            );
            $return = json_encode(SQLDBDataStore::getMulti($params));
        }
        return $return;
    }

    /**
     * Returns the form that this controller uses to manipulate the data stored
     * in its model. As stated earlier the form is either automatically generated
     * or it is loaded from an existing file which is located in the same
     * directory as the model and bears the model's name.
     *
     * @return Form
     */
    protected function getForm()
    {
        // Load a local form if it exists.
        if($this->redirected)
        {
            $formName = $this->redirectedPackageName . Application::camelize($this->mainRedirectedPackage) . "Form";
            $formPath = $this->redirectPath . "/" . str_replace(".", "/", $this->mainRedirectedPackage) . "/" . $formName . ".php";
        }
        else
        {
            $formName = Application::camelize($this->model->package) . "Form";
            $formPath = $this->localPath . "/" . $formName . ".php";
        }
        
        if(is_file($formPath))
        {
            include_once $formPath;
            $form = new $formName();
        }
        else if (is_file($this->localPath."/".$this->name."Form.php"))
        {
            include_once $this->localPath."/".$this->name."Form.php";
            $formclass = $this->name."Form";
            $form = new $formclass();
            $form->setModel($this->model);
        }
        else
        {
            // Generate a form automatically
            if($this->app == null)
            {
                $fieldNames = array();
                $fields = $this->model->getFields();
                array_shift($fields);
            }
            else
            {
                $fieldNames = $this->app->xpath("/app:app/app:form/app:field");
                if(count($fieldNames) > 0)
                {
                    $fields = $this->model->getFields($fieldNames);
                }
                else
                {
                    $fieldNames = array();
                    $fields = $this->model->getFields();
                    array_shift($fields);
                }
            }

            $form = new Form();
            $form->setModel($this->model);
            $names = array_keys($fields);

            for($i=0; $i<count($fields); $i++)
            {
                $field = $fields[$names[$i]];
                if($fieldNames[$i]["renderer"]=="")
                {
                    if($field["reference"]=="")
                    {
                        switch($field["type"])
                        {
                            case "boolean":
                                $element = new Checkbox($field["label"],$field["name"],$field["description"],1);
                                break;

                            case "enum":
                                $element = new SelectionList($field["label"],$field["name"]);
                                foreach($field["options"] as $value => $option)
                                {
                                    $element->addOption($option, $value."");
                                }
                                break;

                            case "date":
                            case "datetime":
                                $element = new DateField($field["label"], $field["name"]);
                                break;

                            case "integer":
                            case "double":
                                $element = new TextField($field["label"],$field["name"],$field["description"]);
                                $element->setAsNumeric();
                                break;

                            case "textarea":
                                $element = new TextArea($field["label"],$field["name"],$field["description"]);
                                break;

                            default:
                                $element = new TextField($field["label"],$field["name"],$field["description"]);
                                break;
                        }
                    }
                    else
                    {
                        $element = new ModelField($field["reference"],$field["referenceValue"]);
                    }

                    foreach($field["validators"] as $validator)
                    {
                        switch($validator["type"])
                        {
                            case "required":
                                $element->setRequired(true);
                                break;
                            case "unique":
                                $element->setUnique(true);
                                break;
                            case "regexp":
                                $element->setRegexp((string)$validator["parameter"]);
                                break;
                        }
                    }
                }
                else
                {
                    $renderer = (string)$fieldNames[$i]["renderer"];
                    $element = new $renderer();
                }
                $form->add($element);
            }

            $form->addAttribute("style","width:50%");
            $form->useAjax(true, false);
        }
        return $form;
    }

    /**
     * Controller action method for adding new items to the model database.
     * @return String
     */
    public function add()
    {
        if($this->apiMode === true)
        {
            $return = $this->model->setData($_REQUEST);
            if($return === true)
            {
                $id = $this->model->save();
                return json_encode(array("success"=>true, "data"=>$id));
            }
            else
            {
                return json_encode(array("success"=>false, "data"=>$return));                
            }
        }
        else
        {
            $form = $this->getForm();
            $this->label = "New ".$this->label;
            $form->setCallback($this->callbackMethod,
                array(
                    "action"=>"add",
                    "instance"=>$this,
                    "success_message"=>"Added new ".$this->model->name,
                    "form"=>$form
                )
            );
            return $form->render();
        }
    }

    /**
     * The callback used by the form class. This callback is only called when
     * the add or edit controller actions are performed.
     * 
     * @param array $data The data from the form
     * @param Form $form an instance of the form
     * @param mixed $c Specific data from the form, this normally includes an instance of the controller
     * @param boolean $redirect If true the controller redirects the page after execution
     * @see ModelController::$callbackFunction
     * @return boolean
     */
    public static function callback($data,&$form,$c,$redirect=true,&$id=null)
    {   
        switch($c["action"])
        {
        case "add":
            $return = $c["instance"]->model->setData($data);
            if($return===true)
            {
                $id = $c["instance"]->model->save();
                User::log($c["success_message"],$data);
                if($redirect)
                {
                    Application::redirect($c["instance"]->urlPath."?notification=".urlencode($c["success_message"]));
                }
                else
                {
                    return true;
                }
            }
            else
            {
                $fields = array_keys($return["errors"]);
                foreach($fields as $field)
                {
                    foreach($return["errors"][$field] as $error)
                    {
                        $element = $c["form"]->getElementByName($field);
                        $element->addError(str_replace("%field_name%",$element->getLabel(),$error));
                    }
                }
            }
            break;

        case "edit":
            $return = $c["instance"]->model->setData($data,$c["key_field"],$c["key_value"]);
            if($return===true)
            {
                $c["instance"]->model->update($c["key_field"],$c["key_value"]);
                User::log($c["success_message"],$data);
                if($redirect)
                {
                    Application::redirect($c["instance"]->urlPath."?notification=".urlencode($c["success_message"]));
                }
                else
                {
                    return true;
                }
            }
            else
            {
                $fields = array_keys($return["errors"]);
                foreach($fields as $field)
                {
                    foreach($return["errors"][$field] as $error)
                    {
                        $element = $c["form"]->getElementByName($field);
                        $element->addError(str_replace("%field_name%",$element->getLabel(),$error));
                    }
                }
            }
            break;
        }
    }

    protected function getModelData($id)
    {
        $data = $this->model->get(
            array(
                "conditions"=>$this->model->getKeyField()."='$id'"
            ),
            SQLDatabaseModel::MODE_ASSOC,
            true,
            false
        );
        return $data[0];
    }

    /**
     * Action method for editing items already in the database.
     * @param $params array An array of parameters that the system uses.
     * @return string
     */
    public function edit($params)
    {
    	if(!User::getPermission($this->permissionPrefix."_can_edit")) return;
        $form = $this->getForm();
        $form->setData($this->getModelData($params[0]), $this->model->getKeyField(), $params[0]);
        $this->label = "Edit ".$this->label;
        $form->setCallback(
            $this->callbackMethod,
            array(
                "action"=>"edit",
                "instance"=>$this,
                "success_message"=>"Edited ".$this->model->name,
                "key_field"=>$this->model->getKeyField(),
                "key_value"=>$params[0],
                "form"=>$form
            )
        );
        return $form->render(); //ModelController::frameText(400,$form->render());
    }

    /**
     * Display the items already in the database for detailed viewing.
     * @param $params An array of parameters that the system uses.
     * @return string
     */
    public function view($params)
    {
        $form = $this->getForm();
        $form->setShowField(false);
        $data = $this->model->get(array("conditions"=>$this->model->getKeyField()."='".$params[0]."'"),SQLDatabaseModel::MODE_ASSOC,true,false);
        $form->setData($data[0]);
        $this->label = "View ".$this->label;
        return $form->render(); //ModelController::frameText(400,$form->render());
    }

    /**
     * Export the data in the model into a particular format. Formats depend on
     * the formats available in the reports api.
     * @param $params
     * @return unknown_type
     * @see Report
     */
    public function export($params)
    {
    	switch($params[0])
        {
            case "pdf":
                $report = new PDFReport();
                break;
                
            case "html":
                $report = new HTMLReport();
                $report->htmlHeaders = true;
                break;
                
            case "csv":
                if($params[1]=="")
                {
                    $report = new CSVReport();
                    $this->model->datastore->dateFormat = 2;
                }
                else if($params[1]=="template")
                {
                    $report = new CSVReport();
                    $table = new TableContent($this->model->getLabels(),array());
                    $report->add($table);
                    $report->output();
                }
                break;
                
            case "xls":
                $report = new XLSReport();
                break;
        }
        
        $title = new TextContent($this->label);
        $title->style["size"] = 12;
        $title->style["bold"] = true;

        $headers = $this->model->getLabels();

        $fieldNames = $this->model->getFieldNames();
        array_shift($fieldNames);
        $data = $this->model->get(array("fields"=>$fieldNames));
        //$data = $this->model->formatData();
        $table = new TableContent($headers,$data);
        $table->style["decoration"] = true;

        $report->add($title,$table);
        $report->output();
    }

    /**
     * Provides all the necessary forms needed to start an update.
     * @param $params
     * @return unknown_type
     */
    public function import($params)
    {
        $data = array();
        $form = new Form();
        $form->
        add(
            Element::create("FileUploadField","File","file","Select the file you want to upload.")->
                setScript(Application::$prefix."/lib/controllers/import.php?model=$this->modelName")->
                setJsExpression("wyf.showUploadedData(callback_data)"),
            Element::create("Checkbox","Break on errors","break_on_errors","","1")->setValue("1")
        );
        $form->setRenderer("default");
        $form->addAttribute("style","width:400px");
        $form->setShowSubmit(false);

        $data["form"] = $form->render();
        return array
        (
            "template"=>"file:".getcwd()."/lib/controllers/import.tpl",
            "data"=>$data
        );
    }

    /**
     * Delete a particular item from the model.
     * @param $params
     * @return unknown_type
     */
    public function delete($params)
    {
    	if(User::getPermission($this->permissionPrefix."_can_delete"))
    	{
            $data = $this->model->getWithField($this->model->getKeyField(),$params[0]);
            $this->model->delete($this->model->getKeyField(),$params[0]);
            User::log("Deleted " . $this->model->name, $data[0]);
            Application::redirect("{$this->urlPath}?notification=Successfully+deleted+".strtolower($this->label));
    	}
    }

    public function audit($params)
    {
        $table = new MultiModelTable(null);
        if(count($this->historyModels) > 0)
        {
            $models = implode("', '", $this->historyModels);
        }
        else
        {
            $models = $this->modelName;
        }
        
        $table->setParams(
            array(
                'fields' => array(
                    'system.audit_trail.audit_trail_id',
                    'system.audit_trail.audit_date',
                    'system.audit_trail.description',
                    'system.users.user_name'
                ),
                'conditions' => "item_id = '{$params[0]}' AND item_type in ('$models')",
                'sort_field' => 'audit_trail_id DESC'
            )
        );
        $table->useAjax = true;
        return $table->render();
        
    }

    /**
     * Return a standard set of permissions which allows people within certain
     * roles to access only parts of this model controller.
     *
     * @see lib/controllers/Controller#getPermissions()
     * @return Array
     */
    public function getPermissions()
    {
        return array
        (
            array("label"=>"Can add",    "name"=> $this->permissionPrefix . "_can_add"),
            array("label"=>"Can edit",   "name"=> $this->permissionPrefix . "_can_edit"),
            array("label"=>"Can delete", "name"=> $this->permissionPrefix . "_can_delete"),
            array("label"=>"Can view",   "name"=> $this->permissionPrefix . "_can_view"),
            array("label"=>"Can export", "name"=> $this->permissionPrefix . "_can_export"),
            array("label"=>"Can import", "name"=> $this->permissionPrefix . "_can_import")
        );
    }
}
