<?php

/**
 * The form field class. This class represents a form field element.
 * Sublcasses of this class are to be used to capture information from
 * the user of the application.
 * @ingroup Forms
 */
abstract class Field extends Element
{
    
    protected $jsValidations = array();

    /**
     * A flag for setting the required state of the form. If this value
     * is set as true then the form would not be validated if there is
     * no value entered into this field.
     */
    protected $required = false;

    /**
     * The value of the form field.
     */
    protected $value;

    /**
     * The enabled state of the field.
     */
    protected $enabled;

    //! The name of a custom validation function which can be used to
    //! perform further validations on the field.
    protected $validationFunc;

    //! A validation constraint which expects that the value entered in
    //! this field is unique in the database.
    protected $unique;
    
    protected $jsOnChangeParams = array();
    
    public $isField = true;

    public static function prepareMessage($text)
    {
        return "'".addcslashes($text,"\\'\"")."'";
    }

    public function getId()
    {
        $id = parent::getId();
        if($id == "" && $this->ajax)
        {
            $id = str_replace(".","_",$this->getName());
        }
        return $id;
    }

    /**
     * The constructor for the field element.
     */
    public function __construct($name="", $value="")
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Sets the value of the field.
     *
     * @param $value The value of the field.
     */
    public function setValue($value)
    {
        if($unset)
        {
            if($this->getMethod()=="GET") unset($_GET[$this->getName()]);
            if($this->getMethod()=="POST") unset($_POST[$this->getName()]);
        }
        $this->value = $value;
        return $this;
    }

    /**
     * Get the value of the field.
     *
     * @return unknown
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getDisplayValue()
    {
        return $this->value;
    }
    
    public function setWithDisplayValue($value)
    {
        $this->value = $value;
    }

    /**
     * Sets the required status of the field.
     *
     * @param The required status of the field.
     */
    public function setRequired($required)
    {
        //$this->addAttribute("onblur","fapiCheckRequired('".$this->getId()."')");
        $this->addJsValidation
        (array(
            "func"=>"fapiCheckRequired",
            "message"=>Field::prepareMessage($this->getLabel()." is required.")
            )
        );
        $this->required = $required;
        return $this;
    }

    /**
     * Returns the required status of the field.
     *
     * @return The required status of the field.
     */
    public function getRequired()
    {
        return $this->required;
    }

    //! Sets whether the value of this field is unique in the database.
    public function setUnique($unique,$param=null,$url="lib/fapi/ajax.php",$extra=null)
    {
        $this->unique = $unique;
        return $this;
    }

    //! Returns the data held by this field. This data is returned as a
    //! key value pair. The key is the name of the field and the value
    //! represents the value of the field.
    //!
    public function getData($storable=false)
    {
        if($this->getMethod()=="POST")
        {
            //print $this->getName(false)." - ".$this->getValue()."<br />";;
            if(isset($_POST[$this->getName()])) $this->setValue($_POST[$this->getName()]);
        }
        else if($this->getMethod()=="GET")
        {
            //print $this->getName(false)." - ".$this->getValue()."<br />";;
            if(isset($_GET[$this->getName()])) $this->setValue($_GET[$this->getName()]);
        }
        else
        {
            //print $this->getLabel();
            //print $this->getName(false);
            throw new Exception("The method for this field has not been set.");
            $this->setValue("");
        }
        return array($this->getName(false) => $this->getValue());
    }

    //! Sets the data that is stored in this field.
    //! \param $data An array of fields. This method just looks through for
    //!              a field that matches it and then applies its value to
    //!              itself.
    public function setData($data)
    {
        if(is_array($data))
        {
            if(array_search($this->getName(false),array_keys($data))!==false)
            {
                $this->setValue($data[$this->getName(false)]);
            }
        }
    }

    //! Sets a custom validation function which is to be called during the
    //! validation phase. This function takes as a parameter an array which must
    //! be used to store all the individual error messages encountered during the
    //! validation phase.
    //!
    //! @param $validationFunc A string representing the name of the validation
    //! function
    //!
    public function setValidationFunc($validationFunc)
    {
        $this->validationFunc = $validationFunc;
    }

    public function validate()
    {
        //global $db;

        //Perform the required validation. Generate an error if this
        //field is empty.
        if($this->getRequired() && $this->getValue() === "" )
        {
            //throw new Exception();
            $this->error = true;
            array_push($this->errors,$this->getLabel()." is required.");
            return false;
        }

        // Call the custom validation function.
        $validationFunc = $this->validationFunc;
        if($validationFunc!="")
        {
            $this->error = !$validationFunc($this,$this->errors);
            return !$this->error;
        }
        return true;
    }

    public function getType()
    {
        return __CLASS__;
    }

    public function getCSSClasses()
    {
        $classes=parent::getCSSClasses();
        if($this->error) $classes.="fapi-error ";
        if($this->getRequired()) $classes .="required ";
        return $classes;
    }

    public function resolve($value)
    {
        $option = $this->getOptions();
        $key = array_keys($option);

        for($i=0;$i<count($option);$i++)
        {
            //print $option[$key[$i]];
            if(strtoupper($option[$key[$i]])==strtoupper($value))
            {
                Field::setValue($key[$i]);
                return;
            }
        }

        //Check the list of values
        for($i=0; $i<count($option); $i++)
        {
            if(strtoupper($key[$i])==strtoupper($value))
            {
                Field::setValue($key[$i]);
                return;
            }
        }

        //array_shift($this->options);

        $error = "Could not resolve value <b>$value</b> for the <b>".$this->getLabel()."</b> field.";

        if(count($option)>1)
        {
            $error.= "Possible values can include";
        }
        else
        {
            $error.= "There are no possible values for this field";
        }

        $error .= "<ul>";
        //print_r($options);
        foreach($option as $opt)
        {
            if($opt!="") $error .= "<li>$opt</li>";
        }
        $error .= "</ul>";
        return $error;
    }

    public function getOptions()
    {
        return array();
    }

    public function addJsValidation($validator)
    {
        $keys = array_keys($validator);
        $members = array();
        for($i=0; $i<count($validator); $i++)
        {
            $members[] = $keys[$i].":".$validator[$keys[$i]];
        }
        $this->jsValidations[] = "{".implode(",",$members)."}";
    }

    public function getJsValidations()
    {
        return "[".implode($this->jsValidations,",")."]";
    }
    
    public function setJsOnChange($params)
    {
        $this->jsOnChangeParams = $params;
        return $this;
    }
    
    public function addJsOnChangeParameter($thisValue,$element,$scope,$property,$propertyValue)
    {
        $this->jsOnChangeParams[$thisValue][] = array(
            "element"=>$element,"scope"=>$scope,"property"=>$property,"propertyValue"=>$propertyValue);
        return $this;
    }
    
    protected function getJsOnChangeScript()
    {
        if(count($this->jsOnChangeParams)==0) return;
        $id = $this->getId();
        $function = "{$id}OnChangeFunction()";
        $ret = "<script type='text/javascript'>";
        $ret .= "function $function {";
        foreach($this->jsOnChangeParams as $value =>$params)
        {
            foreach($params as $param)
            {
                $ret .= "if(document.getElementById('$id').value=='$value') {";
                $targetValue = $param["propertyValue"];
                $targetId = $param["element"]->getId().$param["scope"];
                $ret .= " document.getElementById('$targetId').{$param["property"]} = {$param["propertyValue"]};";
                $ret .= "}";
            }
        }
        $ret .= "}";
        $ret .= "</script>";
        return $ret;
    }
}
