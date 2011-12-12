<?php
class TableContent extends ReportContent
{
    protected $headers;
    protected $data;
    public $style;
    public $data_params = null;
    
    public function __construct($headers, $data, $data_params=null)
    {
        $this->headers = $headers;
        if(isset($data_params["ignore"]))
        {
            foreach($data_params["ignore"] as $ignore)
            {
                array_splice($this->headers,$ignore,1);
                array_splice($data_params["type"],$ignore,1);
                array_splice($data_params["total"],$ignore,1);
            }
        }
        $this->style["decoration"] = true;
        $this->data_params = $data_params;
        $this->setData($data);
        if(is_array($data_params["widths"]))
        {
            if(count($data_params["widths"])==0)
            {
                $this->data_params["widths"] = $this->getTableWidths();
            }
        }
        else
        {
            $this->data_params["widths"] = $this->getTableWidths();
        }
    }

    public function getTableWidths()
    {
        $widths = array();
        if($this->headers != null)
        {
            foreach($this->headers as $i=>$header)
            {
                $lines = explode("\n",$header);
                foreach($lines as $line)
                {
                    $widths[$i] = strlen($line) > $widths[$i] ? strlen($line) : $widths[$i];
                }
            }
        }
        
        if($this->data != null)
        {
            foreach($this->data as $row)
            {
                $i = 0;
                if(!is_array($row)) continue;
                foreach($row as $column)
                {
                    $widths[$i] = strlen($column) > $widths[$i] ? strlen($column) : $widths[$i];
                    $i++;
                }
            }
        }
        
        $totals = $this->getTotals();
        
        if(count($totals) > 0)
        {
            foreach($totals as $i => $column)
            {
                $column = Common::currency($column);
                $widths[$i] = strlen($column) > $widths[$i] ? strlen($column) : $widths[$i];
            }            
        }
        return $widths;
    }
    
    public function getTotals()
    {
        $totals = array();
        if(!is_array($this->data)) return $totals;
        foreach($this->data as $fields)
        {
            $i = 0;
            if(!is_array($fields)) return $totals;
            foreach($fields as $field)
            {
                if($this->data_params["total"][$i])
                {
                    $totals[$i] = bcadd($totals[$i], Common::round(str_replace(",","",$field),2));
                }
                $i++;
            }
        }
        for($i = 0; $i < count($fields); $i++)
        {
            $totals[$i] = is_numeric($totals[$i]) ? $totals[$i] : null;
        }
        return $totals;    
    }
    
    public function getHeaders()
    {
        return $this->headers;
    }
    
    public function setData($data)
    {
        if(isset($this->data_params["ignore"]))
        {
            foreach($data as $key=>$row)
            {
                foreach($this->data_params["ignore"] as $ignore)
                {
                    array_splice($row, $ignore, 1);
                }
                $data[$key]=$row;
            }
        }
        $this->data = $data;
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    public function getType()
    {
        return "table";
    }
}
?>
