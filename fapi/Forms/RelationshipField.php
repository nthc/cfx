<?php
/**
 * Used to limit selection options when there exists a relationship between two
 * different models. It normally displays two selection lists. The first 
 * selection list contains values which drives the second selection list. The
 * values in the second selection list is stored.
 * 
 * An example use case for this could be the relationship between banks and
 * their branches. The first relationship field would display a list of all banks
 * and the second list would be updated with the bank branches once the bank
 * selection changes. When submitted, the value for the selected branch is 
 * submitted.
 * 
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 * @ingroup Forms
 */
class RelationshipField extends Field
{
    protected $mainSelectionList;
    protected $subSelectionList;
    protected $subModelPathInfo;
    protected $mainModel;
    protected $subModel;
    protected $mainModelField;
    protected $subModelField;

    /**
     * Creates a new relationship field.
     * @param unknown_type $label The label for the field.
     * @param unknown_type $name The name for the field.
     * @param unknown_type $mainModelPath The path of the main model.
     * @param unknown_type $subModelPath The path of the sub model.
     */
    public function __construct($label, $name, $mainModelPath, $subModelPath)
    {
        $this->setLabel($label);
        $this->mainSelectionList = new SelectionList();
        $this->subSelectionList = new SelectionList();
        $this->setName($name);
        $this->mainModelField = $mainModelPath;
        $this->subModelField = $subModelPath;

        //$subSelectionList = new SelectionList();
        $mainModelPathInfo = Model::resolvePath($mainModelPath);
        $this->subModelPathInfo = Model::resolvePath($subModelPath);
        $this->mainModel = Model::load($mainModelPathInfo["model"]);
        $this->subModel = Model::load($this->subModelPathInfo["model"]);
        $info = $this->mainModel->get(array("fields"=>array($mainModelPathInfo["field"],$this->mainModel->getKeyField()),"sort_field"=>$mainModelPathInfo["field"]),Model::MODE_ARRAY);
        foreach($info as $inf)
        {
            $this->mainSelectionList->addOption($inf[0], $inf[1]);
        }
    }

    public function setValue($value)
    {
        parent::setValue($value);
        if($value=="") return;
        $mainValue = $this->subModel->get(array("fields"=>array($this->mainModel->getKeyField()),"conditions"=>"{$this->subModel->getKeyField()}={$value}"),Model::MODE_ARRAY,false,false);
        $this->mainSelectionList->setValue($mainValue[0][0]);
        $subValues = $this->subModel->get(array("fields"=>array($this->subModel->getKeyField(),$this->subModelPathInfo["field"]),"conditions"=>"{$this->subModel->getKeyField()}={$value}"),Model::MODE_ARRAY,false,false);
        foreach($subValues as $subValue)
        {
            $this->subSelectionList->addOption($subValue[1], $subValue[0]);
        }
        $this->subSelectionList->setValue($value);
    }
    
    public function setName($name)
    {
        parent::setName($name);        
        $this->mainSelectionList->setId($name."_main");
        $this->subSelectionList->setName($name); 
        return $this;
    }
    
    public function setWithDisplayValue($value) 
    {
        $parts = explode("//", $value);
        $mainId = $this->mainModel->getKeyField();
        
        $mainField = Model::resolvePath($this->mainModelField);
        $mainField = $mainField['field'];
        
        $subField = Model::resolvePath($this->subModelField);
        $subField = $subField['field'];
        
        $possibleMainItem = reset($this->mainModel->getWithField2("trim($mainField)", trim($parts[0])));
        if($possibleMainItem === false)
        {
            parent::setValue(null);
            return;
        }
        
        $possibleSubItem = reset($this->subModel->get(
            array(
                    'conditions' => "$mainId = $possibleMainItem[$mainId] and trim($subField) = '" . trim($this->mainModel->escape($parts[1])) . "'"
                )
            , Model::MODE_ASSOC, false, false
        ));
        
        parent::setValue($possibleSubItem[$this->getName()]);
    }

    public function getDisplayValue()
    {
        $value = $this->getValue();
        if($value=="") return;
        
        $data = reset(SQLDBDataStore::getMulti(
            array(
                'fields' => array(
                    $this->mainModelField,
                    $this->subModelField
                ),
                'conditions' => "{$this->name} = '{$value}'"
            )
        ));
        
        return array_shift($data) ." // ". array_shift($data);
    }

    public function render()
    {
        $this->mainSelectionList->addAttribute("onchange","fapi_change_{$this->name}()");        
        $object = array
        (
            "model"=>$this->subModel->package,
            "format"=>"json",
            "fields"=>array($this->subModelPathInfo["field"],$this->subModel->getKeyField()),
            "sortField"=>$this->subModelPathInfo["field"],
            "sort"=>"DESC"
            
        );

        $path = Application::$prefix."/system/api/query";
        $params = "object=".urlencode(base64_encode(serialize($object)))."&";
        $params .= "conditions=".urlencode("{$this->subModel->getDatabase()}.{$this->mainModel->getKeyField()}==");
        
        return $this->mainSelectionList->render().
               "<br/>".
                $this->subSelectionList->render()
                ."<script type='text/javascript'>
                    function fapi_change_{$this->name}()
                    {
                        document.getElementById('{$this->name}').innerHTML='<option></option>';
                        $.ajax({
                            type:'GET',
                            url:'$path',
                            dataType:'json',
                            data:'$params'+escape(document.getElementById('{$this->name}_main').value)+',',
                            success:function(responses)
                            {
                                var list = document.getElementById('{$this->name}');
                                var i;
                                var n = list.length;
                                
                                for(i = 0; i < n; i++)
                                {
                                    list.remove(0);
                                }
                                
                                
                                try
                                {
                                    list.add(new Option('',''), null);
                                }
                                catch(e)
                                {
                                    list.add(new Option('',''));
                                }
                                
                                for(i = 0; i < responses.length; i++)
                                {
                                    try
                                    {
                                        list.add(new Option(responses[i].{$this->subModelPathInfo["field"]}, responses[i].{$this->subModel->getKeyField()}),null);
                                    }
                                    catch(e)
                                    {
                                        list.add(new Option(responses[i].{$this->subModelPathInfo["field"]}, responses[i].{$this->subModel->getKeyField()}));
                                    }
                                }   
                            }
                        });
                    }
                  </script>";
    }
}