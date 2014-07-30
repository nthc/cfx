<?php
/**
 * An abstract datastore class. This class is used as the basis for writing
 * datastores through which the WYF framework can query for data. It exposes
 * basic methods that allow models to perform CRUD operations and also describe
 * themselves. Any datastore written for the WYF framework must extend this
 * class and implement its methods. This class is mainly used internally. To be
 * safe, developers should try to work through the methods and fields exposed
 * by the Model class.
 */
abstract class DataStore
{
    /**
     * The name of the field in this model which acts as the primary key.
     * @var string
     */
    public $keyField;
    
    /**
     * A sructured array which contains information about all the fields available
     * in this datastore.
     * @var array
     */
    public $fields;
    
    /**
     * Temporal data which is currently being held in the class. This data could
     * represent data yet to be stored into the database or data which has just
     * being retrieved and is about to be updated or deleted.
     * 
     * @var array
     */
    public $data;
    
    /**
     * Formatted data represents data which has been stylized for easy human
     * reading. Formatted data were a bad idea in the first place. They are however
     * so engraved in the framework that they can't be gotten rid of. The safest
     * thing for any developer to do now would be to ignore this and rather try
     * to use the template engine to handle styling of output.
     * 
     * @var array
     */
    public $formattedData;
    
    /**
     * Temporary data that is usually swapped from the Datastore::$data field.
     * This is usually done during updates so changes could be tracked for
     * purposes of validation.
     * 
     * @var array
     */
    public $tempData;
    
    /**
     * Fields from this model that are referenced from other models. You can
     * look at this in the light of foreign keys.
     * 
     * @var array
     */
    public $referencedFields;
    
    /**
     * A list of models whose datastores are indirectly linked to this datastore.
     * In every real database there are bound to be lots of tables which satisfy
     * this relation. However, the intention of this property is to hold only those
     * that we care about.
     * 
     * @var array
     */
    public $explicitRelations;
    
    /**
     * An array of conditions that are fixed for this instance of the datastore.
     * @var array
     */
    public $fixedConditions;
    
    /**
     * Fields whose data can be stored in this database.
     * @var array
     */
    public $storedFields;
    public $dateFormat = 1;
    
    /**
     * Retrieves data from the datastore. Implementations of this method return
     * an array whicch contain the data.
     */
    public abstract function get($params=null,$mode=Model::MODE_ASSOC,$explicit_relations=false,$resolve=true);
    
    /**
     * Saves the data stored temporarily in the datastore object. 
     */
    public abstract function save();
    
    /**
     * Update the data in the database with the data stored temporarily in the
     * datastore object.
     */
    public abstract function update($field,$value);
    
    /**
     * Delete data from the datastore.
     */
    public abstract function delete($field,$value=null);
    
    /**
     * Describe the tables and the details under the datastore.
     */
    public abstract function describe();
    
    public function getKeyField($type="primary")
    {
        foreach($this->fields as $name => $field)
        {
            if($field["key"]==$type) return $name;
        }
    }

}
