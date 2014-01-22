<?php
class PgFileStore extends postgresql 
{
    private static $model = null;

    private static function getModel() 
    {
        if (PgFileStore::$model == null) 
        {
            PgFileStore::$model = Model::load("system.binary_objects");
        }
        return PgFileStore::$model;
    }

    public static function addFile($path) 
    {
        return PgFileStore::addData(file_get_contents($path));
    }

    public static function addData($data) 
    {
        $model = PgFileStore::getModel();
        $model->setData(
            array(
                "data" => $data
            )
        );
        $id = $model->save();
        return $id;
    }

    public static function getData($oid) 
    {
        $model = PgFileStore::getModel();
        $data = $model->getWithField("object_id", $oid);
        return pg_unescape_bytea($data[0]["data"]);
    }

    public static function getFile($oid) 
    {
        $model = PgFileStore::getModel();
        $data = $model->getWithField("object_id", $oid);
        $file = '/tmp/' + uniqid();
        file_put_contents($file, pg_unescape_bytea($data[0]["data"]));
        $fd = fopen($file, 'r');
        return $fd;
    }

    public static function getFilePath($oid, $postfix = "picture.jpg") 
    {
        $file = 'app/temp/' . $oid . "_$postfix";
        $model = PgFileStore::getModel();
        $data = $model->getWithField("object_id", $oid);
        file_put_contents($file, pg_unescape_bytea($data[0]["data"]));
        return $file;
    }

    public static function deleteFile($oid) 
    {
        $model = PgFileStore::getModel();
        $model->delete("object_id", $oid);
    }
}
