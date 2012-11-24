<?php
class MultiModelTable extends Table
{
    private $fields = array();
    private $itemsPerPage = 15;
    public $useAjax = false;
    protected $params;
    protected $tableData;
    protected $model;

    public function __construct($prefix = null)
    {
        parent::__construct($prefix);
    }

    public function setParams($params)
    {
        $params["limit"] = $this->itemsPerPage;
        $params["offset"] = $this->itemsPerPage * $params["page"];
        $params["url"] = Application::$prefix . "/system/api/table";
        $params["id"] = $this->name;
        $params["operations"] = $this->operations;
        $params["moreInfo"] = true;
        
        $this->params = $params;
        
        if($this->useAjax)
        {
            $params['moreInfoOnly'] = true;
        }
        
        $this->tableData = SQLDBDataStore::getMulti($params);
    }

    protected function renderHeader()
    {
        $searchFunction = $this->name."Search()";
        $table = "<table class='tapi-table' id='$this->name'>";

        //Render Headers
        $table .= "<thead><tr>";
        //$table .= "<td><input type='checkbox' onchange=\"ntentan.tapi.checkToggle('$this->name',this)\"></td>";

        foreach($this->headers as $i => $header)
        {
            $table.="<td onclick=\"ntentan.tapi.sort('".$this->name."','".$this->tableData["rawFields"][$i+1]."')\">
            $header
            </td>";
        }
        
        if($this->useInlineOperations)
        {
            $table .= "<td>Operations</td>";
        }
        $table .= "</tr>";

        //Render search fields
        $table .= "<tr id='tapi-$this->name-search' class='tapi-search-row' >";

        foreach($this->headers as $i => $header)
        {
            $table.="<td>";
            //if(count(explode(",",$this->fields[$i]))==1)
            //{
                switch($this->fields[$i+1]["type"])
                {
                    case "string":
                    case "text":
                        $text = new TextField();
                        $text->setId($this->fields[$i+1]["name"]);
                        $text->addAttribute("onkeyup",$searchFunction);
                        $table .= $text->render();
                        $name = $this->fields[$i+1]["name"];
                        // veeery dirty code @todo clean this up small
                        //$this->searchScript .= "if($('#$name').val()!='') conditions = (conditions==''?'':conditions+' AND ')+ \"lower('\" + $('#$name').val() +\"') in lower({$this->tableData["rawFields"][$i+1]}))>0\";\n";
                        $this->searchScript .= "if($('#$name').val()!='') conditions = (conditions==''?'':conditions+' AND ')+ \"lower({$this->tableData["rawFields"][$i+1]}::varchar) like '%\"+$('#$name').val().toLowerCase()+\"%'\";\n";
                        break;

                    /*case "reference":
                        $text = new TextField();
                        $text->setId($this->fields[$i]["name"]);
                        $text->addAttribute("onkeyup",$searchFunction);
                        $table .= $text->render();
                        $modelInfo = Model::resolvePath($this->fields[$i]["reference"]);
                        $model = Model::load($modelInfo["model"]);
                        $fieldName = $model->database.".".$this->fields[$i]["referenceValue"];
                        $this->searchScript .= "if($('#{$this->fields[$i]["name"]}').val()!='') condition += escape('$fieldName='+$('#{$fields[$i]["name"]}').val()+',');";
                        break;

                        $list = new ModelSearchField($fields[$i]["reference"],$fields[$i]["referenceValue"]);
                        $list->boldFirst = false;
                        $list->setId($fields[$i]["name"]);
                        $list->addAttribute("onChange",$searchFunction);
                        $table .= $list->render();
                        $modelInfo = Model::resolvePath($fields[$i]["reference"]);
                        $model = Model::load($modelInfo["model"]);
                        $fieldName = $model->database.".".$field[$i]["name"];
                        $this->searchScript .= "if($('#{$field["name"]}').val()!='') condition += escape('$fieldName='+$('#{$field["name"]}').val()+',');";
                        break;*/
                    /*case "enum":
                        $list = new SelectionList();
                        foreach($fields[$i]["options"] as $value => $label)
                        {
                            $list->addOption($label,$value);
                        }
                        $list->setId($fields[$i]["name"]);
                        $table.=$list->render();
                        break;
                    case "integer":
                    case "double":
                        $options = Element::create("SelectionList")->
                                    addOption("Equals",0)->
                                    addOption("Greater than",1)->
                                    addOption("Less than",2);
                        $text = new TextField();
                        $text->setId($fields[$i]["name"]);
                        $table .= $options->render().$text->render();
                        break;
                    case "date":
                        $date = new DateField();
                        $date->setId($fields[$i]["name"]);
                        $table .= $date->render();
                        break;
                    case "boolean":
                        $options = Element::create("SelectionList")->
                                    addOption("Yes",1)->addOption("No",0);
                        $options->setId($fields[$i]["name"]);
                        $table .= $options->render();
                        break;*/
                }
            //}
            /*else
            {
                $text = new TextField();
                $text->setId("concat_field_$i");
                $text->addAttribute("onkeyup",$searchFunction);
                $table .= $text->render();
                $name = addslashes($this->model->datastore->concatenate(explode(",",$this->fields[$i])));
                $this->searchScript .= "if($('#concat_field_$i').val()!='') condition += escape('$name='+$('#concat_field_$i').val()+',');";
            }*/
            $table .="</td>";
        }
        
        if($this->useInlineOperations)
        {
            $table .= "<td><input class='fapi-button' type='button' value='Search' onclick='$searchFunction'/></td></tr></thead>";
        }

        //Render Data
        $table .= "<tbody id='tbody'>";

        return $table;
    }

    public function render($headers = true)
    {
        global $redirectedPackage;
        $results = $this->tableData;
        $this->fields = $results["fieldInfos"];

        foreach($this->fields as $field)
        {
            if($field["type"] == "number" || $field["type"] == "double" || $field["type"] == "integer")
            {
                $this->headerParams[$field["name"]]["type"] = "number";
            }
        }

        $this->headers = $results["headers"];
        array_shift($this->headers);
        if($headers === true) $table = $this->renderHeader();
        if($this->useAjax)
        {
            $table .= "<tr>
                <td align='center' colspan='".count($this->headers)."'>
                    <img style='margin:80px' src='".Application::$prefix."/lib/tapi/images/loading-image-big.gif' />
                </td></tr>";
        }
        else
        {
            $this->data = $results["data"];
            $table .= parent::render(false);
        }

        if($headers === true) $table .= $this->renderFooter();

        if($this->useAjax)
        {
            $this->params['redirected_package'] = $redirectedPackage;
            $table .=
            "<div id='{$this->name}-operations'></div>
            <script type='text/javascript'>
                ntentan.tapi.addTable('$this->name',(".json_encode($this->params)."));
                var externalConditions = [];
                function {$this->name}Search()
                {
                    var conditions = '';
                    {$this->searchScript}
                    ntentan.tapi.tables['$this->name'].conditions = conditions;
                    if(externalConditions['$this->name'])
                    {
                        ntentan.tapi.tables['$this->name'].conditions += ((conditions != '' ?' AND ':'') + externalConditions['$this->name']);
                    }
                    ntentan.tapi.tables['$this->name'].page = 0;
                    ntentan.tapi.render(ntentan.tapi.tables['$this->name']);
                }
            </script>";
        }
        return $table;
    }

    public function renderFooter()
    {
        $table = parent::renderFooter();
        $params = $this->params;
        
        $table .= "<div id='{$this->name}Footer'>
            <ul class='table-pages'><li>
                        <a onclick=\"ntentan.tapi.switchPage('$this->name',0)\">
                            &lt;&lt; First
                        </a>
                    </li>
                    <li>
                        <a onclick=\"ntentan.tapi.switchPage('$this->name',".($params["page"]-1>=0?$params["page"]-1:"").")\">
                            &lt; Prev
                        </a>
                    </li>".
                    "<li><a onclick=\"ntentan.tapi.switchPage('$this->name',".($params["page"]+1).")\">Next &gt;</a></li>" .
                "<li> | </li>
                <li> Page <input style='font-size:small; width:50px' value = '".($params["page"]+1)."' onchange=\"ntentan.tapi.switchPage('$this->name',(this.value > 0 )?this.value-1:0)\" type='text' /></li>
            </ul>
        </div>";
        return $table;
    }
}
