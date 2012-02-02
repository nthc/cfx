<?php
session_start();
error_reporting(E_ALL ^ E_NOTICE);
set_include_path(get_include_path() . PATH_SEPARATOR . "../../");

date_default_timezone_set("Africa/Accra");

require "lib/coreutils.php";
require "../../app/config.php";
require "../../app/includes.php";

add_include_path("lib");
add_include_path("lib/models");
add_include_path("lib/models/datastores");
add_include_path("lib/models/datastores/databases/oracle");
add_include_path("lib/models/datastores/databases/postgresql");
add_include_path("lib/cache");

Cache::init($cache_method);

define('CACHE_PREFIX', "../../");
define('CACHE_MODELS', $cache_models);

$object = unserialize(base64_decode($_REQUEST["object"]));

//Application::$packagesPath = "../../";

$model = Model::load($object["model"]);

if(isset($_REQUEST["conditions"]))
{
    $conditions = explode(",",$_REQUEST["conditions"]);
    array_pop($conditions);
    //array_shift($conditions);
    foreach($conditions as $i => $condition)
    {
        if(substr_count($condition,"=="))
        {
            $parts = explode("==",$condition);
            $conditions[$i] = $parts[0]."=".$parts[1];
        }
        else
        {
            $parts = explode("=",$condition);
            $conditions[$i] = $model->getSearch($parts[1],$parts[0]);//"instr(lower({$parts[0]}),lower('".$model->escape($parts[1])."'))>0";//$parts[0] ." in '".$model->escape($parts[1])."'";
        }
    }
    $condition_opr = isset($_REQUEST["conditions_opr"])?$_REQUEST["conditions_opr"]:"AND";
    $conditions = implode(" $condition_opr ",$conditions);
}

$params = array(
    "fields"=>$object["fields"],
    "sort_field"=>isset($_REQUEST["sort"])?$_REQUEST["sort"]:$object["sortField"],
    "sort_type"=>isset($_REQUEST["sort_type"])?$_REQUEST["sort_type"]:"ASC",
    "limit"=>$object["limit"],
    "offset"=>$_REQUEST["offset"],
    "conditions"=>"($conditions) " . ($object['and_conditions'] != '' ? " AND ({$object['and_conditions']})" : '')
);

//$data = $model->formatData();

switch($_REQUEST["action"])
{
    case "delete":
        $ids = json_decode($_REQUEST["params"]);
        foreach($ids as $id)
        {
            $data = $model->getWithField($model->getKeyField(),$id);
            $model->delete($model->getKeyField("primary"),$id);
            User::log("Deleted ".$model->name,$data[0]);            
        }
        break;
}

switch($object["format"])
{
    case "tbody":
        include "lib/tapi/Table.php";
        include "lib/tapi/ModelTable.php";
        $table = new ModelTable($prefix."/".str_replace(".","/",$object["model"])."/");        
        print json_encode(array("tbody"=>$table->render(false),"footer"=>$table->renderFooter()));
        break;
    
    case "json":
        $data = $model->get($params);
        print json_encode($data);
        break;
}
