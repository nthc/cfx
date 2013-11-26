<?php
class TableContent extends ReportContent
{
    protected $headers;
    protected $data;
    public $style;
    
    public $data_params = null;
    private $totals = array();
    
    public function __construct($headers, $data, $data_params=null)
    {
        // Define the default style for the tables
        if(defined('DEFAULT_TABLE_STYLE'))
        {
            $this->style = json_decode(DEFAULT_TABLE_STYLE, true);
        }
        else 
        {
            $this->style = array(
                'header:border' => array(200,200,200),
                'header:background' => array(200,200,200),
                'header:text' => array(255,255,255),
                'body:background' => array(255,255,255),
                'body:stripe' => array(250, 250, 250),
                'body:border' => array(200, 200, 200),
                'body:text' => array(0,0,0)
            );                        
        }
        
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
                $column = number_format($column, 2, '.', ',');
                $widths[$i] = strlen($column) > $widths[$i] ? strlen($column) : $widths[$i];
            }            
        }
        return $widths;
    }
    
    public function setTotals($totals)
    {
        $this->totals = $totals;
    }
    
    public function getTotals()
    {
        if(count($this->totals) > 0)
        {
            return $this->totals;
        }
        $totals = array();
        if(!is_array($this->data)) return $totals;
        foreach($this->data as $fields)
        {
            $i = 0;
            if(!is_array($fields)) continue; //return $totals;
            foreach($fields as $field)
            {
                if($this->data_params["total"][$i])
                {
                    $field = str_replace(array(",", ' '),"",$field);
                    
                    switch($this->data_params['type'][$i])
                    {
                        case 'double':
                            $field = round($field, 2);
                            break;
                        case 'number':
                            $field = round($field, 0);
                            break;
                    }
                    
                    $totals[$i] += $field;
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
    
    public function setDataTypes($types)
    {
    	$this->data_params['type'] = $types;
    }
    
    public function setTotalsFields($total)
    {
    	$this->data_params['total'] = $total;
    	$this->style['autoTotalsBox'] = true;
    }
    
    public function setWidths($widths)
    {
        $this->data_params['widths'] = $widths;
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
