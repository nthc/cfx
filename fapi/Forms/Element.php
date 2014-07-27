<?php
/**
 * The form element class. An element can be anything from the form
 * itself to the objects that are put in the form. Provides an
 * abstract render class that is used to output the HTML associated
 * with this form element. All visible elements of the form must be
 * subclasses of the element class.
 * 
 * @package wyf.forms
 *
 */
abstract class Element
{
    
    const SCOPE_ELEMENT = "";
    const SCOPE_WRAPPER = "_wrapper";

    protected $ajax = true;

    /**
     * The id of the form useful for CSS styling and DOM access.
     */
    protected $id;

    /**
     * The label of the form element.
     */
    protected $label;

    /**
     * The description of the form element.
     */
    protected $description;

    /**
     * The method by which the form should be submitted.
     */
    protected $method = "POST";

    /**
     * An array of all the CSS classes associated with this element.
     */ 
    protected $classes = array();

    /**
     * An array of all HTML attributes. These attributes are stored as
     * objects of the Attribute class. The attributes stored in this value
     * represent the attributes of the fields HTML object itself.
     * 
     * @var array
     * @see Attribute
     */
    protected $attributes = array();
    
    /**
     * An array of all HTML attributes. These attributes are stored as
     * objects of the Attribute class. The attributes stored in this value
     * represent the attributes of the fields of the wrapper of the HTML object.
     * 
     * @var array
     * @see Attribute
     */
    protected $wrapperAttributes = array();

    //! An array of all error messages associated with this element.
    //! Error messages are setup during validation, when any element
    //! fails its validation test.
    protected $errors = array();

    //! A boolean value which is set to true whenever there is an error
    //! assiciated with the class in one way or the other.
    protected $error;

    //! A boolean value which is set to true if the form elements are
    //! to be made available for editing. If this property is set to false
    //! the form element shows only the value associated with this field
    //! in cases where the data has been collected from the database.
    protected $showfield = true;

    //! The parent element which contains this element.
    protected $parent = null;

    //! A value which determines whether this field is to be used in
    //! constructing database queries
    protected $storable = true;

    /**
     * The name of the form field. This is what is to be outputed as
     * the HTML name attribute of the field. If name encryption is
     * enabled the outputed name to HTML is mangled by the encryption
     * algorithm. However internally the Field may still be referenced
     * bu the unmangled name.
     */
    protected $name;

    //! A value which determines whether this element contains file data;
    protected $hasFile = false;

    public function __construct($label="", $description="", $id="")
    {
        $this->setLabel($label);
        $this->setDescription($description);
        $this->setId($id);
    }

    /**
     * Public accessor for setting the ID of the element.
     * @return Element
     */
    public function setId($id)
    {
        $this->id = str_replace(".","_",$id);
        $this->addAttribute("id",$this->id);
        return $this;
    }

    /**
     * Public accessor for getting the Id of the element.
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Public accessor for setting the name property of the field.
     *
     * @param  $name The name to assign to the form element.
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Public accessor for getting the name property of the field.
     *
     * @return The name of the form field.
     */
    public function getName($encrypt=true)
    {
        return $this->name;
    }

    //! Sets the label which is attached to this element.
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    //! Gets the label which is attached to this element.
    public function getLabel()
    {
        return $this->label;
    }

    //! Gets the description which is attached to this element. The description
    //! is normally displayed under the element when rendering HTML.
    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Sets the method for the form.
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    /**
     * Gets the method being used by the form. The method could be either
     * "GET" or "POST".
     */
    public function getMethod()
    {
        return $this->method;
    }

    //! Returns all the arrays associated with this document.
    public function getErrors()
    {
        return $this->errors;
    }

    // Returns the error flag for this element.
    public function hasError()
    {
        return $this->error;
    }

    public function getType()
    {
        return __CLASS__;
    }

    /**
     * Renders the form element by outputing the HTML associated with
     * the element. This method is abstract and it is implemented by
     * all the other classes which inherit the Element class.
     */
    abstract public function render();

    //! Returns an array of all the CSS classes associated with this
    //! element.
    public function getCSSClasses()
    {
        $ret = "";
        foreach($this->classes as $class)
        {
            $ret .= $class." ";
        }
        return $ret;
    }

    //! Adds a css class to this element.
    public function addCSSClass($class)
    {
        array_unshift($this->classes, $class);
        return $this;
    }

    public function resetCssClasses()
    {
        $this->classes = array();
        return $this;
    }

    //! Adds an attribute object to the internal attribute array of the
    //! element.
    //! \see Attribute
    public function addAttributeObject($attribute)
    {
        $this->attributes[] = $attribute;
        return $this;
    }

    //! Adds an attribute to the list of attributes of this element.
    //! This method internally creates a new Attribute object and appends
    //! it to the list of attributes.
    //! \see Attribute
    public function addAttribute($attribute,$value,$scope = Element::SCOPE_ELEMENT)
    {
        // Force the setting of the attribute.
        if($scope==Element::SCOPE_ELEMENT)
        {
            foreach($this->attributes as $attribute_obj)
            {
                if($attribute_obj->getAttribute()==$attribute)
                {
                    $attribute_obj->setValue($value);
                    return $this;
                }
            }
            $attribute = new Attribute($attribute, $value);
            $this->attributes[] = $attribute;
            //$this->addAttributeObject($attribute);
        }
        else if($scope == Element::SCOPE_WRAPPER)
        {
            foreach($this->wrapperAttributes as $attribute_obj)
            {
                if($attribute_obj->getAttribute()==$attribute)
                {
                    $attribute_obj->setValue($value);
                    return $this;
                }
            }
            $attribute = new Attribute($attribute, $value);
            $this->wrapperAttributes[] = $attribute;
        }
        return $this;
    }

    public function removeAttribute($attribute)
    {
        $i=0;
        foreach($this->attributes as $attribute_obj)
        {
            if($attribute_obj->getAttribute()==$attribute)
            {
                array_splice($this->attributes,$i,1);
            }
            $i++;
        }
    }

    //! Sets the value for a particular attribute.
    public function setAttribute($attribute,$value)
    {
        foreach($this->attributes as $attrib)
        {
            if($attrib->getAttribute()==$attribute)
            {
                $attrib->setValue($value);
            }
        }
        return $this;
    }

    //! Returns an HTML representation of all the attributes. This method
    //! is normally called when rendering the HTML for the element.
    public function getAttributes($scope=Element::SCOPE_ELEMENT)
    {
        switch($scope)
        {
            case Element::SCOPE_ELEMENT: $attributes = $this->attributes; break;
            case Element::SCOPE_WRAPPER: $attributes = $this->wrapperAttributes; break;
        }
        $ret = "";
        foreach($attributes as $attribute)
        {
            $ret .= $attribute->getHTML()." ";
        }
        return $ret;
    }

    //! Sets whether the field should be shown or hidden.
    //! \see $showfield
    public function setShowField($showfield)
    {
        $this->showfield = $showfield;
        return $this;
    }

    //! Gets the value of the $showfield property.
    public function getShowField()
    {
        return $this->showfield;
    }

    public function getForeing()
    {
        return false;
    }

    public function hasOptions()
    {
        return false;
    }

    public function setStorable($storable)
    {
        $this->storable = $storable;
    }

    public function getStorable()
    {
        return $this->storable;
    }

    public function getHasFile()
    {
        return $this->hasFile;
    }

    public function addError($error)
    {
        $this->error = true;
        if(is_array($error))
        {
            foreach($error as $message)
            {
                $this->errors[] = $message;
            }    
        }
        else
        {
            $this->errors[] = $error;
        }
    }

    public function clearErrors()
    {
        $this->error = false;
        $this->errors = array();
    }

    /**
     * Factory method for creating elements.
     * @return Element
     */
    public static function create()
    {
        $args = func_get_args();
        $element = array_shift($args);
        if(!class_exists($element)) throw new Exception("Class requested doesn't exist");
        $element = new ReflectionClass($element);
        return $element->newInstanceArgs($args==null?array():$args);
    }

    public function onRender()
    {
        
    }
    
    public function hasLabel()
    {
        return false;
    }
    

    public function isFormSent()
    {
        if($this->getMethod()=="POST") $sent=$_POST['is_form_sent'];
        if($this->getMethod()=="GET") $sent=$_GET['is_form_sent'];
        if($sent=="yes") return true; else return false;
    }    
}
