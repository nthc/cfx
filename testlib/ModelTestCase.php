<?php

abstract class ModelTestCase extends BaseTestCase
{

    protected $model;
    protected $modelName;
    protected $datasetPath;
    protected $addTestData;
    protected $addExpectedData;
    protected $editTestData;
    protected $editExpectedData;
    protected $deleteTestId;
    protected $deleteExpectedData;
    private $addTestTables;
    private $addTestFilters;
    private $editTestTables;
    private $editTestFilters;
    private $deleteTestTables;
    private $deleteTestFilters;

    protected function getDataSet()
    {
        if ($this->datasetPath == NULL)
            throw new Exception("No dataset specified");
        $dataset = new PHPUnit_Extensions_Database_DataSet_XmlDataSet($this->datasetPath);
        return $dataset;
    }

    protected function setUp()
    {
        parent::setUp();
        $this->model = Model::load($this->modelName);

        $this->addTestTables = array();
        $this->addTestFilters = array();
        foreach ($this->addExpectedData["tables"] as $table) {
            $this->addTestTables[] = $table["name"];
            $this->addTestFilters[$table["name"]] = $table["filters"];
        }

        $this->deleteTestTables = array();
        $this->deleteTestFilters = array();
        foreach ($this->deleteExpectedData["tables"] as $table) {
            $this->deleteTestTables[] = $table["name"];
            $this->deleteTestFilters[$table["name"]] = $table["filters"];
        }
    }

    protected function getTables()
    {
        return $this->tables;
    }

    protected function getFilters()
    {
        return $this->filters;
    }

    private function runTableTests($expectedDataset, $tables, $filters)
    {
        $expectedDataset = new PHPUnit_Extensions_Database_DataSet_XmlDataSet($expectedDataset);

        $actualDataset = new PHPUnit_Extensions_Database_DataSet_DataSetFilter(
                $this->getConnection()->createDataSet($tables), $filters
        );

        foreach ($tables as $table) {
            $this->assertTablesEqual(
                    $expectedDataset->getTable($table), $actualDataset->getTable($table)
            );
        }
    }

    public function testAdd()
    {
        foreach ($this->addTestData as $testData) {
            $this->model->setData($testData);
            $this->model->save();
        }

        $this->runTableTests($this->addExpectedData["dataset"], $this->addTestTables, $this->addTestFilters);
    }

    public function testEdit()
    {
        
    }

    public function testDelete()
    {
        foreach ($this->deleteTestId as $id) {
            $this->model->delete($this->model->getKeyField(), $id);
        }
        $this->runTableTests($this->deleteExpectedData["dataset"], $this->deleteTestTables, $this->deleteTestFilters);
    }

}
