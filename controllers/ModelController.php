<?php
/*
 * WYF Framework
 * Copyright (c) 2011 James Ekow Abaka Ainooson
*
* Permission is hereby granted, free of charge, to any person obtaining
* a copy of this software and associated documentation files (the
* "Software"), to deal in the Software without restriction, including
* without limitation the rights to use, copy, modify, merge, publish,
* distribute, sublicense, and/or sell copies of the Software, and to
* permit persons to whom the Software is furnished to do so, subject to
* the following conditions:
*
* The above copyright notice and this permission notice shall be
* included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
* EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
* NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
* LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
* OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
* WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*
*/

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
 * In cases where extra functionality and operations are to be added, the
 * ModelController class could be extended.
 *
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
    
    /**
     * Enforce the add operation. This overrides the permissions on the system.
     * 
     * @var boolean
     */
    protected $forceAddOperation = false;
    protected $forceEditOperation = false;
    protected $forceDeleteOperation = false;
    
    protected $historyModels = array();
    
    protected $urlBase;
    
    private static $pendingErrors;
    
    
    /**
     * Constructor for the ModelController.
     * @param $model An instance of the Model class which represents the model
     *               to be used.
     */
    public function __construct($model = "")
    {
        global $redirectedPackage;
        $this->modelName = ($this->modelName == "" ? $model : $this->modelName);
        $this->model = Model::load($this->modelName);
        $this->name = $this->model->name;
        $this->urlBase = $this->urlBase == '' ? ($redirectedPackage != '' ? "$redirectedPackage" : '') . $this->modelName : $this->urlBase;
        $this->urlPath = Application::$prefix."/".str_replace(".","/",$this->urlBase);
        if($this->modelName[0] == '.') {
            $this->permissionPrefix = str_replace(".", "_", $redirectedPackage);
        } 
        $this->permissionPrefix .= str_replace(".", "_", $this->modelName);
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
        
        $this->_showInMenu = $this->model->showInMenu === "false" ? false : true;
        
        if(file_exists($this->localPath."/app.xml"))
        {
            $this->app = simplexml_load_file($this->localPath."/app.xml");
        }
        
        if($_SESSION['read_only'] === true)
        {
            $this->hasAddOperation = false;
            $this->hasEditOperation = false;
            $this->hasDeleteOperation = false;
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
        
        $this->toolbar->addLinkButton("Search","#")->linkAttributes="onclick=\"wyf.tapi.showSearchArea('{$this->table->name}')\"";
    
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
                $this->table->addOperation("delete","Delete","javascript:wyf.confirmRedirect('Are you sure you want to delete','{$this->urlPath}/%path%/%key%')");
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
        
        if(User::getPermission($this->permissionPrefix."_can_view_notes"))
        {
            $this->table->addOperation("notes","Notes");
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
            $keyField = $this->model->getKeyField();
            $fieldNames[$keyField] = "{$this->model->package}.{$keyField}";
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
    
    private function createDefaultForm()
    {
        // Generate a form automatically
        $fieldNames = array();
        $fields = $this->model->getFields();

        $form = new Form();
        $names = array_keys($fields);

        for($i=0; $i<count($fields); $i++)
        {
            $field = $fields[$names[$i]];
            if($field['key'] == 'primary') continue;

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
        return $form;
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
        }
        else
        {
            $form = $this->createDefaultForm();
        }
        return $form;
    }
    
    /**
     * Controller action method for adding new items to the model database.
     * @return String
     */
    public function add()
    {
    	if(!User::getPermission($this->permissionPrefix."_can_add")) return;
    	if(!$this->hasAddOperation) return;
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
    public static function callback($data, $form, $c, $redirect=true, &$id=null)
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
                        try{
                            $element = $c["form"]->getElementByName($field);
                        }
                        catch(Exception $e)
                        {
                            $element = $c["form"]->getElementById(str_replace(".", "_", $field));
                        }
                        $element->addError(str_replace("%field_name%",$element->getLabel(),$error));
                    }
                }
                
                foreach($return['errors'] as $fieldName => $error)
                {
                    $form->addError(str_replace("%field_name%",str_replace('_', ' ', $fieldName),$error));
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
                self::$pendingErrors = $return['errors'];
                foreach($fields as $field)
                {
                    foreach($return["errors"][$field] as $error)
                    {
                        $element = $c["form"]->getElementByName($field);
                        $element->addError(str_replace("%field_name%",$element->getLabel(),$error));
                    }
                }
                foreach($return['errors'] as $fieldName => $error)
                {
                    $form->addError(str_replace("%field_name%",str_replace('_', ' ', $fieldName),$error));
                }
            }
            break;
        }
    }

    protected function getModelData($id)
    {
        $data = $this->model->get(
            array(
                "filter" => $this->model->getKeyField()." = ?",
                "bind" => [$id]
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
    	if(!$this->hasEditOperation) return;
        
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
        return $form->render();
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
        $data = $this->model->get(
            array(
                "filter"=>$this->model->getKeyField()." = ?",
                "bind" => [$params[0]]
            ),
            SQLDatabaseModel::MODE_ASSOC,
            true,
            false
        );
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
        $fields = $this->getForm()->getFields();
        $fieldNames = array();
        $headers = array();
        foreach($fields as $field)
        {
            $fieldNames[] = $field->getName();
            $headers[] = $field->getLabel();
        }
        
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
                    $report->setDownloadFileName("{$this->model->name}.csv");
                    $this->model->datastore->dateFormat = 2;
                }
                else if($params[1]=="template")
                {
                    $report = new CSVReport();
                    $report->setDownloadFileName("{$this->model->name}_template.csv");
                    $table = new TableContent($headers, array());
                    $report->add($table);
                    $report->output();
                    die();
                }
                break;
                
            case "xls":
                $report = new XLSReport();
                break;
        }
        
        $title = new TextContent($this->label);
        $title->style["size"] = 12;
        $title->style["bold"] = true;
        
        $this->model->setQueryResolve(false);
        $data = $this->model->get(array("fields"=>$fieldNames));
        
        foreach($data as $j => $row)
        {
            for($i = 0; $i < count($row); $i++)
            {
                $fields[$i]->setValue($row[$fieldNames[$i]]);
                $data[$j][$fieldNames[$i]] = strip_tags($fields[$i]->getDisplayValue());
            }
        }
        
        $table = new TableContent($headers,$data);
        $table->style["decoration"] = true;

        $report->add($title,$table);
        $report->output();
        die();
    }
    
    private function doImport()
    {
        $uploadfile = "app/temp/" . uniqid() . "_data";
        $cleared = move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);                
        if (!$cleared) die("Failed to upload file");
            
        $file = fopen($uploadfile,"r");
        $headers = fgetcsv($file);
        $model = $this->model;
        $formFields = $this->getForm()->getFields();
        $fileFields = array();
        
        foreach($formFields as $field)
        {
            $index = array_search($field->getLabel(), $headers);
            if($index !== false)
            {
                $fileFields[] = $field;
            }
        }
        
        $primary_key = $model->getKeyField();
        $secondary_key = $model->getKeyField("secondary");
        $tertiary_key = $model->getKeyField("tertiary");
        $hasErrors = false;
        
        if($secondary_key == null)
        {
            print "<div id='information'><h4>Warning</h4>  This model has no secondary keys so imported data may overlap</div>";
        }

        $out = "<table class='data-table'>";
        $out .= "<thead><tr><td>Save Status</td><td>".implode("</td><td>",$headers)."</td></tr></thead>";
        $out .= "<tbody>";
        $line = 1;
        $status = "<h3>Successfully Imported</h3>";
        
        $model->datastore->beginTransaction();

        while(!feof($file))
        {
            $data = fgetcsv($file);
            $model_data = array();
            $errors = array();
            $hasValues = false;
            
            if(!is_array($data)) continue;

            foreach($data as $i => $value)
            {
                if(trim($value) !== '') $hasValues = true;
                $formFields[$i]->setWithDisplayValue($value);
                $display_data[$fileFields[$i]->getName()] = $value;
                $model_data[$fileFields[$i]->getName()] = $formFields[$i]->getValue();
            }
            
            if(!$hasValues) continue;
            
            if($secondary_key!=null && $model_data[$secondary_key] != '')
            {
                $temp_data = $model->getWithField($secondary_key,$model_data[$secondary_key]);
                if(count($temp_data)>0) 
                {
                    if($tertiary_key != "")
                    {
                        $model_data[$primary_key] = $temp_data[0][$primary_key];
                        $model_data[$tertiary_key] = $temp_data[0][$tertiary_key];
                    }
                                        
                    $validated = $model->setData($model_data,$primary_key,$temp_data[0][$primary_key]);
                    if($validated===true) $model->update($primary_key,$temp_data[0][$primary_key]);
                    $saveStatus = 'Updated';
                }
                else
                {
                    $validated = $model->setData($model_data);
                    if($validated===true) $model->save();
                    $saveStatus = 'Added';
                }
            }
            else
            {
                $validated = $model->setData($model_data);
                if($validated===true) $model->save();
                $saveStatus = 'Added';
            }

            if($validated===true)
            {
                $out .= "<tr><td>$saveStatus</td><td>".implode("</td><td>",$display_data)."</td></tr>";
            }
            else
            {
                $out .= "<tr style='border:1px solid red'><td>Error</td>";
                foreach($display_data as $field=>$value)
                {
                    $out .= "<td>$value";
                    if(count($validated["errors"][$field])>0)
                    {
                        $out .= "<div class='fapi-error'><ul>";
                        foreach($validated["errors"][$field] as $error)
                        {
                            $error = str_replace("%field_name%",$fieldInfo[$field]["label"],$error);
                            $out .= "<li>$error</li>";
                            if($cli) echo "*** Error on line $line ! [$field] $error ($value)\n";
                        }
                        $out .= "</ul></div>";
                    }
                    $out .= "</td>";
                }
                $out .= "</tr>";
                $hasErrors = true;
                $status = "<h3>Errors Importing Data</h3><div class='error'>Errors on line $line</div>";
                if($_POST["break_on_errors"]=="1") break;
            }
            $line++;
        }
        $out .= "</tbody>";
        $out .= "</table>";
        
        if(!$hasErrors) $model->datastore->endTransaction();

        if($cli)
        {
            message($status, $cli, null, false);
        }
        else
        {
            print "$status<div style='overflow:auto; height:400px; border:1px solid #909090'>$out</div>";
        }
        die();
    }

    /**
     * Provides all the necessary forms needed to start an update.
     * @param $params
     * @return unknown_type
     */
    public function import($params)
    {
        if($params[0] == 'execute') 
        {
            $this->doImport();
            die();
        }           
        
        $data = array();
        $form = new Form();
        $form->
        add(
            Element::create("FileUploadField","File","file","Select the file you want to upload.")->
                setScript($this->urlPath . "/import/execute")->
                setJsExpression("wyf.showUploadedData(callback_data)"),
            Element::create("Checkbox","Break on errors","break_on_errors","","1")->setValue("1")
        );
        $form->setRenderer("default");
        $form->addAttribute("style","width:400px");
        $form->setShowSubmit(false);

        $data["form"] = $form->render();
        return $this->arbitraryTemplate(Application::getWyfHome('controllers/import.tpl'), $data);
    }

    /**
     * Delete a particular item from the model.
     * @param $params
     * @return unknown_type
     */
    public function delete($params)
    {
    	if(!$this->hasDeleteOperation) return;        
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
                    'system.users.user_name',
                    'system.users.first_name',
                    'system.users.last_name',
                    'system.users.other_names'
                ),
                'filter' => "item_id = ? AND item_type in (?)",
                'bind' => [$params[0],$models],
                'sort_field' => 'audit_trail_id DESC'
            )
        );
        $table->useAjax = true;
        return $table->render();
        
    }
    
    public function notes($params)
    {
        $noteAttachments = Model::load('system.note_attachments');
        
        if($params[1] == 'delete')
        {
            $model = Model::load('system.notes');
            $model->delete('note_id', $params[2]);
            Application::redirect("{$this->path}/notes/{$params[0]}");
        }
            
        if(isset($_POST['is_form_sent']))
        {
            $model = Model::load('system.notes');
            $model->datastore->beginTransaction();
            $data = array(
                'note' => $_POST['note'],
                'note_time' => time(),
                'item_id' => $params[0],
                'user_id' => $_SESSION['user_id'],
                'item_type' => $this->model->package
            );
            $model->setData($data);
            $id = $model->save();
            
            
            for($i = 1; $i < 5; $i++)
            {
                $file = $_FILES["attachment_$i"];
                if($file['error'] == 0)
                {
                    $noteAttachments->setData(array(
                        'note_id' => $id,
                        'description' => $file['name'],
                        'object_id' => PgFileStore::addFile($file['tmp_name']),
                    ));
                    $noteAttachments->save();
                }
            }            
            $model->datastore->endTransaction();
            
            Application::redirect("{$this->urlPath}/notes/{$params[0]}");
        }
        
        $notes = SQLDBDataStore::getMulti(
            array(
                'fields' => array(
                    'system.notes.note_id',
                    'system.notes.note',
                    'system.notes.note_time',
                    'system.users.first_name',
                    'system.users.last_name'
                ),
                'filter' => 'item_type = ? and item_id = ?',
                'bind' => [$this->model->package,$params[0]]
            )
        );
        
        foreach($notes as $i => $note)
        {
            $attachments = $noteAttachments->getWithField2('note_id', $note['note_id']);
            foreach($attachments as $j => $attachment)
            {
                $attachments[$j]['path'] = PgFileStore::getFilePath($attachment['object_id'], $attachment['description']);
            }
            $notes[$i]['attachments'] = $attachments;
        }
        
        $this->label = "Notes on item";
        $form = Element::create('Form')->add(
            Element::create('TextArea', 'Note', 'note'), 
            Element::create('FieldSet', 'Add Attachments')->add(
                Element::create('UploadField', 'Attachment', 'attachment_1'),
                Element::create('UploadField', 'Attachment', 'attachment_2'),
                Element::create('UploadField', 'Attachment', 'attachment_3'),
                Element::create('UploadField', 'Attachment', 'attachment_4')
            )->setId('attachments')->setCollapsible(true)
        )->setRenderer('default');
        
        return $this->arbitraryTemplate(
            Application::getWyfHome('controllers/notes.tpl'), 
            array(
                'form' => $form->render(),
                'notes' => $notes,
                'route' => $this->path,
                'id' => $params[0]
            )
        );
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
            array("label"=>"Can import", "name"=> $this->permissionPrefix . "_can_import"),
            array("label"=>"Can view audit trail", "name"=> $this->permissionPrefix . "_can_audit"),
            array("label"=>"Can view notes", "name"=> $this->permissionPrefix . "_can_view_notes"),
            array("label"=>"Can create notes", "name"=> $this->permissionPrefix . "_can_create_notes"),
        );
    }
}

