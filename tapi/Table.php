<?php
class Table
{
    protected $data = array();
    protected $headers = array();
    protected $cellRenderers = array();
    protected $operations = array();
    protected $prefix;
    public $name = "defaultTable";
    public $headerParams;
    
    public function __construct($prefix,$headers=null, $data=null, $operations=null,$headerParams=null)
    {
        if(class_exists("Application")) Application::addStyleSheet("css/tapi.css");
        $this->prefix = $prefix;
        $this->headers = $headers;
        $this->data = $data;
        $this->operations = $operations;
    }
    
    public function setOperations($operations)
    {
        $this->operations = $operations;
    }
    
    public function addOperation($link,$label=null,$action=null)
    {
        $this->operations[] = 
        array
        (
            "link"=>$link,
            "label"=>$label==null?$link:$label,
            "action"=>$action==null?$this->prefix.$link."/%key%":$action
        );
    }
    
    protected function renderHeader()
    {
        $table = "<table class='tapi-table'>";
         
        //Render Headers
        $table .= "<thead><tr><td>";
        $table .= "<input type='checkbox' onchange=\"ntentan.tapi.notify('$this->name','0',this)\"></td><td>";
        $table .= implode("</td><td>",$this->headers);
        $table .= "</td><td align='right'>Operations</td></tr></thead>";
         
        //Render Data
        $table .= "<tbody id='tbody'>";
        return $table;
    }
    
    protected function renderFooter()
    {
        $table .= "</tbody>";
        $table .= "</table>";
        return $table;
    }
    
    public function render($renderHeaders=true)
    {
        if($renderHeaders) $table = $this->renderHeader();
        
        foreach($this->data as $row)
        {
            $key = array_shift($row);
            $table .= "<tr>";
            $table .= "<td><input type='checkbox' class='$this->name-checkbox' value='$key' ></td>";
            
            foreach($row as $name=>$value)
            {
                $params="";
                if($this->headerParams[$name]["type"]=="number")
                {
                    $params = "align='right'";
                }
                $table .= "<td $params >$value</td>";
            }
            
            $table .= "<td align='right'>";
            if($this->operations!=null)
            {
                foreach($this->operations as $operation)
                {
                    $table .=
                        sprintf(
                            '<a class="tapi-icon tapi-iaction tapi-i'.$operation['link'].' tapi-operation tapi-operation-%s" href="%s">%s</a>',
                            $operation["link"],
                            str_replace(array("%key%","%path%"),array($key,$this->prefix.$operation["link"]),$operation["action"]),
                            $operation["label"]);
                }
            }
            $table .= "</td></tr>";
        }
        
        if($renderHeaders) $table .= $this->renderFooter();
        
        return $table;
    }
}

