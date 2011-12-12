<?php
abstract class FilteredModelController extends ModelController
{
    protected $selectionList;
    protected $filterField;
    protected $defaultValue;
    protected $filterLabel;
    protected $filterFieldModel;

    abstract protected function addListItems();

    protected function setupList()
    {
        parent::setupList();
        $this->selectionList = new SelectionListToolbarItem($this->filterLabel);
        $this->addListItems();
        if($this->filterFieldModel == null)
        {
        	$this->filterFieldModel = $this->model;
        }
        else
        {
        	$this->filterFieldModel = Model::load($this->filterFieldModel);
        }
        $this->selectionList->onchange = "updateFilter('{$this->table->name}', '{$this->filterFieldModel->database}.{$this->filterField}',this.value)";
        $this->toolbar->add($this->selectionList);
    }

    public function getContents()
    {
        $ret = parent::getContents();
        if($this->apiMode === false)
        {
            $ret .=
                "<script type='text/javascript'>
                    updateFilter('{$this->table->name}', '{$this->filterFieldModel->database}.{$this->filterField}','{$this->defaultValue}');
                    {$this->table->name}Search();
                </script>";
        }
        return $ret;
    }
}
