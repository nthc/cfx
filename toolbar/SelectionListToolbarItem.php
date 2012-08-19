<?php

class SelectionListToolbarItem extends ToolbarItem {

    private $label;
    private $items = array();
    public $hasGroups = false;
    public $onchange = '';

    public function add($item, $value = null, $group = null) 
    {
        if ($group == null) 
        {
            if ($value == null)
                $this->items[] = $item;
            else
                $this->items[$value] = $item;
        }
        else 
        {
            if ($value == null)
                $this->items[$group][] = $item;
            else
                $this->items[$group][$value] = $item;
        }
        return $this;
    }

    public function __construct($label) 
    {
        $this->label = $label;
    }

    public function render() 
    {
        $ret = "<label><b>{$this->label}</b></label> <select "
                . ($this->onchange == '' ? '' : "onchange=\"{$this->onchange}\"") . ">";
        if ($this->hasGroups) 
        {
            foreach ($this->items as $group => $items) 
            {
                $ret .= "<optgroup label='$group'>";
                foreach ($items as $value => $item) 
                {
                    $ret .= "<option value='$value'>$item</option>";
                }
                $ret .= "</optgroup>";
            }
        } 
        else 
        {
            foreach ($this->items as $value => $item) 
            {
                $ret .= "<option value='$value'>$item</option>";
            }
        }
        $ret .= "</select>";
        return $ret;
    }

    public function getCssClasses() 
    {
        return array("toolbar-branchselector");
    }

}