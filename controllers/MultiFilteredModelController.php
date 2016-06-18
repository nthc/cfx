<?php

abstract class MultiFilteredModelController extends ModelController
{
    protected $selectionLists = array();

    abstract protected function retrieveSelectionLists();
    
    protected function setupList()
    {
        parent::setupList();
        $this->selectionLists = $this->retrieveSelectionLists();
        
        foreach ($this->selectionLists as $list)
        {
            $selectionList = Element::create("SelectionListToolbarItem", "{$list['filter_label']}");
            $this->addListItems($selectionList, $list);
            
            $this->filterFieldModel = $this->model;
            $selectionList->onchange = "wyf.updateFilter('{$this->table->name}', '{$this->filterFieldModel->database}.{$list['filter_field']}', this.value)";
            $this->toolbar->add($selectionList);
        }
    }

    public function getContents()
    {
        $ret = parent::getContents();
        foreach ($this->selectionLists as $list)
        {
            if($this->apiMode === false)
            {
                $ret .= "<script type='text/javascript'>
                    wyf.updateFilter('{$this->table->name}', '{$this->filterFieldModel->database}.{$list['filter_field']}', '{$list['default_value']}');
                    {$this->table->name}Search();
                </script>";
            }
        }
        return $ret;
    }
    
    protected function addListItems($selectionList, $list)
    {
        $selectionList->hasGroups = $list['has_groups'];
        
        foreach($list['list'] as $option)
        {
            $selectionList->add($option['item'], $option['value'], $option['group']);
        }
    }
}
