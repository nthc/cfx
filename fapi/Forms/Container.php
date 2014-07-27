<?php
/**
 * The container class. This abstract class provides the necessary
 * basis for implementing form element containers. The container
 * is a special element which contains other form elements.
 * @ingroup Forms
 */
abstract class Container extends Element 
{
    /**
     * Prepend elements to the top of the controller.
     * @var string
     */
    const PREPEND = 'prepend';
    
    /**
     * Append elements to the bottom of the controller.
     * @var string
     */
    const APPEND = 'append';

    /**
     * The array which holds all the elements contained in this container.
     * @var array
     */
    protected $elements = array();

    /**
     * The name of the renderer currently in use.
     * @var array
     */
    protected $renderer;

    /**
     * The header function for the current renderer. This function contains the
     * name of the renderer post-fixed with "_renderer_head". The function is
     * responsible for rendering the head section of elements.
     * @var string
     */
    protected $renderer_head;

    /**
     * The footer function for the renderer currently in use. This function
     * contains the name of the renderer post-fixed with "_renderer_foot". The
     * function is responsible for rendering the foot section of elements.
     * @var string
     */
    protected $renderer_foot;

    /**
     * The element function for the renderer currently in use. This function is
     * responsible for rendering the elements themselves.
     */
    protected $renderer_element;

    /**
     * A flag to determine whether form elements are editable or not.
     * @var boolean
     */
    protected $showfields = true;

    /**
     * The callback function executed for this controller when the form which
     * contains this controller is submitted. Since the Form itself is a 
     * subclass of the Controller class, you can directly use the callback
     * of the form to handle all the data entered into the form.
     * 
     * @var callable
     * @see Controller::setCallback
     */
    protected $callback;
    
    /**
     * Data to be passed to the callback.
     * @var type 
     */
    protected $callbackData;

    /**
     * A variable which identifies an element as a container. All containers 
     * have this value set to true. Non containers won't even have this 
     * property.
     * @var bool
     */
    public $isContainer = true;
    
    /**
     * A flag which is set to true whenever a container contains values.
     * @var boolean
     */
    public $hasData = false;

    /**
     * Creates a new contaier.
     * @param type $renderer The render represents a set of functions which layout the containers of the form.
     */
    public function __construct($renderer="table")
    {
        $this->setRenderer($renderer);
    }

    /**
     * Sets the current renderer being used by the container. The renderer
     * is responsible for rendering the HTML form content. Renerers are a collection
     * of three functions which render and layout the form elements in a 
     * Container. The WYF framework ships with two renderers; the default renderer
     * and the table renderer. 
     * 
     * The default renderer renders all elements in a vertical layout. The label
     * appears on top and the element follows after which the description of the
     * field appears. This sequence is repeated for all elements in the container.
     * 
     * The table renderer renders all elements with a table. The label appears
     * on the left hand side while the field element appears on the right hand side.
     * The description appears on the bottom of the field element.
     * 
     * @param $renderer The name of the renderer being used.
     * @return Container
     */
    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;
        include_once "Renderers/$this->renderer.php";
        $this->renderer_head = $renderer."_renderer_head";
        $this->renderer_foot = $renderer."_renderer_foot";
        $this->renderer_element = $renderer."_renderer_element";
        return $this;
    }

    /**
     * Returns the renderer which is currently being used by the class.
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Method for adding an element to the Container. The elements to be added
     * to the container are passed as parameters to this function. Also the order
     * in which you want these elements to be added can be specified. For example
     * to add two TextField elements to a container.
     * 
     * @code
     * $container = new BoxContainer();
     * $container->add(
     *     new TextField('Firstname', 'firstname'),
     *     new TextField('Lastname', 'lastname')
     * );
     * @endcode
     * 
     * To specify the order in which the elements are added to the Container you
     * can add the Container::APPEND or Container::PREPEND constants to the
     * container. By default, elements are appended to the container.
     * 
     * @code
     * $container - new BoxContainer();
     * $container->add(
     *     Container::PREPEND,
     *     new TextField('Lastname', 'lastname'),
     *     new TextField('Firstname', 'firstname')
     * );
     * @endcode
     * 
     * @return Container
     */
    public function add()
    {        
        $mode = 'append';
        //Check if the element has a parent. If it doesnt then add it
        //to this container. If it does throw an exception.
        foreach(func_get_args() as $element)
        {
            if(is_string($element) && ($element == Container::PREPEND || $element == Container::APPEND))
            {
                $mode = $element;
                continue;
            }
            
            if($element->parent==null)
            {
                if($mode == Container::PREPEND)
                {
                    array_unshift ($this->elements, $element);
                }
                else if($mode == Container::APPEND)
                {
                    $this->elements[] = $element;
                }
                
                $element->setMethod($this->getMethod());
                $element->setShowField($this->getShowField());
                $element->parent = $this;
                $element->ajax = $this->ajax;
                $this->hasFile |= $element->getHasFile();
            }
            else
            {
                throw new Exception("Element added already has a parent");
            }
        }
        return $this;
    }


    /**
     * Method for removing a particular form element from the
     * container/
     *
     * @param $index The index of the element to be removed.
     * @todo Implement the method to remove an element from the Container.
     */
    public function remove($index)
    {

    }

    /**
     * This method sets the data for the fields in this container. The parameter
     * passed to this method is a structured array which has field names as keys
     * and the values as value. Since this method tries to pass the data to
     * every element it contains, any nested containers would also receive a 
     * copy of the data. In this regard, calling this method on any container
     * causes any other containers it contains to have their data set too.
     * 
     * @code
     * $container - new BoxContainer();
     * $container->add(
     *     Container::PREPEND,
     *     new TextField('Lastname', 'lastname'),
     *     new TextField('Firstname', 'firstname')
     * );  
     * 
     * $data = array(
     *     'lastname' => 'Dumor',
     *     'firstname' => 'Komla'
     * );
     * 
     * $container->setData($data);
     * @endcode
     * 
     * @param array $data The array which contains the data to assign to this container
     * @return Container The instance of the container
     */
    public function setData($data)
    {
        $this->hasData = true;
        foreach($this->elements as $element)
        {
            $element->setData($data);
        }
        return $this;
    }

    /**
     * This method returns a structured array which represents the data stored 
     * in all the fields of a given container. This method is recursive so one call to 
     * it extracts all the fields in all nested containers within a given
     * container. No matter how nested the containers are the data returned is
     * always flattened to a simple set of key value relationships. In this regard
     * it is worth noting that in cases where containers are nested, the names
     * of all the elements must always remain unique.
     * 
     * @param boolean $storable This variable is set as true only when data for 
     *      storable fields is required. A storable field is field which can be 
     *      stored in the database.
     * @return array
     */
    public function getData($storable=false)
    {
        $data = array();

        if($this->getMethod()=="POST") $sent=$_POST['is_form_sent'];
        if($this->getMethod()=="GET") $sent=$_GET['is_form_sent'];

        if($sent=="yes")
        {
            foreach($this->elements as $element)
            {
                if($storable)
                {
                    if($element->getStorable()==true) $data+=$element->getData($storable);
                }
                else
                {
                    $data+=$element->getData();
                }
            }
        }
        else
        {
            foreach($this->elements as $element)
            {
                if($element->getType()=="Container")
                {
                    $data+=$element->getData();
                }
                else
                {
                    $data+=array($element->getName(false) => $element->getValue());
                }
            }
        }
        return $data;
    }

    public function getType()
    {
        return __CLASS__;
    }

    /**
     * Render all the Elements in this container.
     * @return Container
     */
    protected function renderElements()
    {
        $renderer_head = $this->renderer_head;
        $renderer_foot = $this->renderer_foot;
        $renderer_element = $this->renderer_element;
        $ret = "";

        // Call the callback function for the container.
        if($this->onRenderCallback!="")
        {
            $data = $this->getData();
            $this->executeCallback($this->onRenderCallback, $data, $this);
        }

        $this->onRender();

        if($renderer_head!="") $ret .= $renderer_head();
        foreach($this->elements as $element)
        {
            $ret .= $renderer_element($element,$this->getShowField());
        }
        if($renderer_head!="") $ret .= $renderer_foot();
        return $ret;
    }

    /**
     * Sets whether the fields should be exposed for editing. If this
     * field is set as true then the values of the fields as retrieved
     * from the database are showed.
     * (non-PHPdoc)
     * @see Element::setShowField()
     * @return Container
     */
    public function setShowField($showfield)
    {
        Element::setShowField($showfield);
        foreach($this->getElements() as $element)
        {
            $element->setShowField($showfield);
        }
        return $this;
    }

    /**
     * Retrieve all the elements found within a cotainer.
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Returns all Elements found in this container which are subclasses of the Field class.
     * Fields found in nested Containers are alse returned when this function is
     * called.
     * 
     * @return Container
     */
    public function getFields()
    {
        $elements = $this->getElements();
        $fields = array();
        foreach($elements as $element)
        {
            if($element->getType()=="Field" || $element->getType()=="Checkbox")
            {
                $fields[] = $element;
            }
            else if($element->getType()=="Container")
            {
                foreach($element->getFields() as $field)
                {
                    $fields[] = $field;
                }
            }
        }
        return $fields;
    }

    /**
     * The render callback is called whenever the Container is rendered. The
     * render callback receives the data in the container along with the 
     * instance of the Container. The callback can either be a public static
     * method in a class or a function. This callback can be used to change
     * the contents of the fields in the Container just before it is rendered
     * to HTML.
     * 
     * @code
     * class MyContainer extends Container
     * {
     *     public function __construct()
     *     {
     *         parent::__construct();
     *         $this->setRenderCallback('MyContainer::onRender');
     *     }
     * 
     *     public static function onRender($data, &$container)
     *     {
     *         
     *     }
     * }
     * @endcode
     * 
     * @param string $onRenderCallback
     * @return Container
     */
    public function setRenderCallback($onRenderCallback)
    {
        $this->onRenderCallback = $onRenderCallback;
        return $this;
    }

    /**
     * Returns the current render callback function.
     * @return string
     */
    public function getRenderCallback()
    {
        return $this->onRenderCallback;
    }

    /**
     * Find and return a field from this container which has a particular name.
     * This function is recursively run on all nested containers so that any
     * fields stored in a nested Contaier can also be retrieved.
     * 
     * @param string $name The name of the element to search for.
     * @return Element
     * @throws Exception
     */
    public function getElementByName($name)
    {
        foreach($this->getElements() as $element)
        {
            if($element->getType()!="Container")
            {
                if($element->getName(false)==$name) return $element;
            }
            else
            {
                try
                {
                    return $element->getElementByName($name);
                }
                catch(Exception $e){}
            }
        }
        throw new Exception("No element with name $name found in array");
    }

    /**
     * Find and return a field from this container which has a particular name.
     * This function is recursively run on all nested containers so that any
     * fields stored in a nested Contaier can also be retrieved.
     * 
     * @param string $id The id of the element to search for.
     * @return Element
     * @throws Exception
     */    
    public function getElementById($id)
    {
        foreach($this->getElements() as $element)
        {
            $elementId = $element->getId();
            if($element->getType()!="Container")
            {
                if($elementId==$id) return $element;
            }
            else
            {
                if($elementId==$id) return $element;
                try
                {
                    return $element->getElementById($id);
                }
                catch(Exception $e){}
            }
        }
        throw new Exception("No element with id $id found in Container");
    }

    /**
     * Sets the callback function which should be called whenever this container
     * is successfully submitted. Since eache Container can have a callback method
     * asigned to it, multiple callbacks can be defined to handle special cases
     * where data from containers need to be treated differently. In most cases
     * however, this callback is usually assigned to the Form class which is a
     * subclass of the Container class.
     * 
     * In your callback you can perform any action for which you needed the form
     * data. This could include; logging in a user or storing some data to the
     * database. You can also validate your form items from within this callback.
     * 
     * @param $callback The callback function
     * @return Container
     */
    public function setCallback($callback,$data)
    {
        $this->callback = $callback;
        $this->callbackData = $data;
        return $this;
    }
    
    /**
     * A callback function which provides extra validation of the data on the
     * form before the callback function is executed.
     * @param type $callback
     * @param type $data
     * @return Container
     */
    public function setValidatorCallback($callback,$data=null)
    {
        $this->validatorCallback = $callback;
        $this->validatorCallbackData = $data;
        return $this;
    }

    /**
     * Execute a callback function. This method used to perform the execution
     * of all the callback functions which are within the Container.
     * 
     * @return mixed Returns whatever was returned by the callback function
     */
    protected static function executeCallback()
    {
        $args = func_get_args();
        $function = array_shift($args);
        $function = explode("::",$function);
        if(count($function)==2)
        {
            $method = new ReflectionMethod($function[0], $function[1]);
            return $method->invokeArgs(null, $args);
        }
        else if(count($function)==1)
        {
            $method = $function[0];
            if(function_exists($method))
            {
                return $method($args[0],$args[1],$args[2]);
            }
        }
    }

    /**
     * Clear any error flags which are set on any of the elements in the 
     * container.
     */
    public function clearErrors()
    {
        foreach($this->getElements() as $element)
        {
            $element->clearErrors();
        }
    }
}
