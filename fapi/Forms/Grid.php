<?php
class Grid extends Field
{
    private $columns = array();
    private $columnNames = array();
    private $rawColumnNames = array();
    private $columnIds = array();
    private $hash;
    private $numRows = 500;
    private $totals;

    public function __construct($label, $name)
    {
        $this->setLabel($label);
        $this->setName($name);
        $this->hash = str_replace(".", "_", $name);
    }
    
    public function setNumRows($numRows)
    {
        $this->numRows = $numRows;
        return $this;
    }
	
    public function getData()
    {
        $nameLenght = strlen($this->name);
        if($this->isFormSent())
        {
            $this->data = array();
            foreach($_POST as $field => $value)
            {
                if(is_array($value))
                {
                    foreach($this->columnNames as $i => $columnName)
                    {
                        if($field == $columnName)
                        {
                            $field = substr($field, $nameLenght - strlen($field) + 1);
                            foreach($value as $j => $data)
                            {
                                $this->data[$j][$field] = $data;
                            }
                        }
                    }
                }
            }
            foreach($this->data as $i => $data)
            {
                $pass = false;
                foreach($data as $field => $value)
                {
                    if($value != "")
                    {
                        $pass = true;
                        break;
                    }
                }
                if(!$pass)
                {
                    unset($this->data[$i]);
                }
            }
            return array($this->getName() => $this->data);
        }
        else
        {
            return parent::getData();
        }
    }

    public function render()
    {
        $data = $this->getData();
        
        // Header Table
        $ret = "<table id='{$this->hash}' class='fapi-grid-header-table' width='100%'>";
        $ret .= "<tr><td id='header-0'></td>";
        $script = "$('#header-0').width($('#gauge-0').width());";
        $gauge = "<tr><td id='gauge-0'></td>";
        foreach($this->columns as $column)
        {
            $ret .= "<td class='fapi-grid-header'  id='{$column->getName()}-header'>" . $column->getLabel() . "</td>";
            $gauge .= "<td id='{$column->getName()}-gauge'></td>";
            $script .= "$('#{$column->getName()}-header').width($('#{$column->getName()}-gauge').width());";
        }
        $gauge .= "</tr>";
        $ret .= "</tr></table>";

        $ret .= "<div class='fapi-grid'><table width='100%'><tbody>$gauge";
        for($i = 0; $i < $this->numRows; $i++)
        {
            $ret .= "<tr><td  class='fapi-grid-header'><center>" . ($i + 1) . "</center></td>";
            foreach($this->columns as $j => $column)
            {
                if(isset($data[$this->name][$i][$column->getName()]))
                {
                    $column->setValue($data[$this->name][$i][$column->getName()]);
                }
                else
                {
                    $column->setValue("");
                }
                $column->setId(str_replace("%%index%%", $i, $this->columnIds[$j]));
                $column->setName($this->columnNames[$j] . "[]");
                $ret .= "<td>" . $column->render() . "</td>";
                $column->setName($this->rawColumnNames[$j]);
            }
            $ret .= "</tr>";
        }
        $ret .= "</tbody></table></div>";
        
        // Footer Table
        $ret .= "<table id='{$this->hash}' class='fapi-grid-footer-table' width='100%'>";
        $ret .= "<tr><td id='footer-0'></td>";
        $script .= "$('#footer-0').width($('#gauge-0').width());";
        $gauge = "<tr><td id='gauge-0'></td>";
        foreach($this->columns as $column)
        {
            $ret .= "<td class='fapi-grid-footer'  id='{$column->getName()}-footer'></td>";
            $gauge .= "<td id='{$column->getName()}-gauge'></td>";
            $script .= "$('#{$column->getName()}-footer').width($('#{$column->getName()}-gauge').width());";
        }
        $gauge .= "</tr>";

        $ret .= "</tr></table>";
        
        $ret .= "<script type='text/javascript'>"
                . "$script"
                . "var totals = JSON.parse('".json_encode($this->totals)."');"
                . "var numRows = {$this->numRows};"
                . "function computeTotals(){"
                    . "for(column in totals){"
                        . "if(totals[column]){"
                            . "var total = 0;"
                            . "for(var i = 0; i < numRows; i++){"
                                . "var number = $('#' + column + i).val();"
                                . "if(!isNaN(parseFloat(number))) total += parseFloat(number);"
                            . "}"
                            . "$('#'+column+'-footer').html(total);"
                        . "}"
                    . "}"
                . "}"
                . "</script>";
        return $ret;
    }

    public function addColumn($column, $totals = false)
    {
        $this->totals[$column->getName()] = $totals;
        if($totals)
        {
            $column->addAttribute("onchange", "computeTotals()");
        }
        $this->rawColumnNames[] = $column->getName();
        $this->columnNames[] = str_replace(".","_",$this->getName()) . "_" . $column->getName();
        $this->columnIds[] = $column->getId() . "%%index%%";
        $this->columns[] = $column;
        return $this;
    }
}
