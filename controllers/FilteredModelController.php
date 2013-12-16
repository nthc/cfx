<?php
/*
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
 * A subclass of the ModelController class which provides all the features
 * of the ModelController class with an added filter which allows the user
 * to filter the contents of the view in realtime. 
 */
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
        $this->selectionList->onchange = "wyf.updateFilter('{$this->table->name}', '{$this->filterFieldModel->database}.{$this->filterField}', this.value)";
        $this->toolbar->add($this->selectionList);
    }

    public function getContents()
    {
        $ret = parent::getContents();
        if($this->apiMode === false)
        {
            $ret .=
                "<script type='text/javascript'>
                    wyf.updateFilter('{$this->table->name}', '{$this->filterFieldModel->database}.{$this->filterField}', '{$this->defaultValue}');
                    {$this->table->name}Search();
                </script>";
        }
        return $ret;
    }
}
