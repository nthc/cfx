<?php
/**
 * \page fapi_page The Form API
 * The Form API is a simple API which makes it possible to define forms
 * in PHP and have the forms rendered in HTML. This API provides server
 * side validation. The form API is made up of a collection of classes
 * which are all based on a single element class. This API is very extensible.
 * 
 * \section how_fapi_works How the form API Works
 * The form api is orgarnised around the Element class. This class is extended
 * by two other abstract classes; Field and Container. Subclasses of the Container 
 * class are used to contain other Elements. Subclasses of the Field class
 * are used to collect the form's data. Some of the subclasses of the container
 * class are the Form, FieldSet and BoxContainer class. Some of the subclasses
 * of the Field class are the TextField, TextArea etc.
 * 
 * \section using_fapi Using the form API
 * The following listing shows a simple usage of the form api. This listing
 * creates a simple form intended to get information to be stored in an
 * address book. Embeding this code anywhere within a PHP document should cause the
 * document to render an HTML form definition.  
 * 
 * \code
 * <?php
 * $form = new Form();
 * $fieldset = new FieldSet("Address Book");
 * $form->add($fieldset);
 * 
 * //Create a field. The first parameter of the constructor is the label.
 * //The second parameter is the name and the third is a description.
 * $firstname = new TextField("Firstname","firstname","The firstname of the person.");
 * $fieldset->add($firstname);
 * 
 * $lastname = new TextField("Lastname","lastname","The lastname of the person.");
 * $fieldset->add($lastname);
 * 
 * $email = new EmailField("Email","email","The e-mail address of the person.");
 * $fieldset->add($email);
 * 
 * $address = new TextArea("Address","address","The address of the person");
 * $fieldset->add($address);
 * 
 * $form->render();
 * ?>
 * \endcode
 * 
 * \subsection intercepting_data Intercepting the Form data.
 * By default the form transmits all its data using the HTTP post method. This
 * can be changed by calling the setMethod method of the Form class.
 * \code
 * $form->setMethod("GET");
 * \endcode
 * Although either GET or POST methods are used to send the form, 
 * to intercept the data that was entered into the form, it is
 * advisable to use a call back function within your program. This is 
 * because for security reasons, the element names sent are sometimes
 * encrypted to prevent the HTML code from containing any information
 * about the internal database structure. A call back function is just
 * any regular function within your program. You pass the function name
 * as a string to the form class by calling the \c setCallback() method.
 * 
 * \code
 * $form->setCallback("my_function")
 * \endcode
 * 
 * The function passed should accept one parameter. This parameter would
 * contain a structured array whose keys are the field names, and values
 * are the values passed to the form. Adding a callback method to the 
 * above code would give the following.
 * 
 * \code
 * <?php
 * function form_callback($data)
 * {
 *     print_r($data);
 * }
 * 
 * $form = new Form();
 * $form->setCallback("form_callback");
 * $fieldset = new FieldSet("Address Book");
 * $form->add($fieldset);
 * 
 * //Create a field. The first parameter of the constructor is the label.
 * //The second parameter is the name and the third is a description.
 * $firstname = new TextField("Firstname","firstname","The firstname of the person.");
 * $fieldset->add($firstname);
 * 
 * $lastname = new TextField("Lastname","lastname","The lastname of the person.");
 * $fieldset->add($lastname);
 * 
 * $email = new EmailField("Email","email","The e-mail address of the person.");
 * $fieldset->add($email);
 * 
 * $address = new TextArea("Address","address","The address of the person");
 * $fieldset->add($address);
 * 
 * $form->render();
 * ?>
 * \endcode
 * 
 * After this interception you can do anything you want to do with the data in the callback
 * function.
 * 
 */

/**
 * The Form class is the main Container for all forms. This form class renders
 * HTML forms. This class is mostly extended to create new forms. In some rare 
 * cases it is directly used and the other
 * elements are added to it. The two examples below show how to use a form by:
 * - Extending the base Form class or.
 * - Using an instance of the base Form class.
 * 
 * 
 * @warning When extending the Form class please be sure to call the parent constructor.
 * 
 * Example login form by extending the Form class.
 * @code
 * class LoginForm extends Form
 * {
 *     parent::__construct();
 *     $this->add(
 *         new TextField("Username", "username"),
 *         new TextField("Password", "password")
 *     )
 * }
 * 
 * $form = new LoginForm();
 * echo $form->render();
 * @endcode
 *
 * Example login form from the base form api classes.
 * @code
 * $form = new Form();
 * $form->add(
 *     new TextField("Username", "username"),
 *     new TextField("Username", "username")
 * );
 * @endcode
 *
 * @ingroup Forms
 * @see Container::add
 */
class Form extends Container
{
    protected $ajaxSubmit;
    
    /**
     * The value to be printed on the submit form.
     */
    protected $submitValue;

    /**
     * Flag to show wether this form has a reset button.
     */
    protected $showClear = false;

    /**
     * The value to display on the reset button.
     */
    protected $resetValue;

    /**
     * A boolean value which shows whether to show the submit button or not.
     * @var boolean
     */
    protected $showSubmit = true;

    /**
     * Attributes attached to the submit attributes.
     * @var Attribute
     */
    public $submitAttributes;

    public $ajaxAction = "lib/fapi/ajax.php?action=save_data";

    public $successUrl;
    
    /**
     * A constructor for initializing the form. This constructor should always
     * be called even when the form class is being extended.
     * @param $method
     */
    public function __construct($method="")
    {
        parent::__construct();

        if($method=="") $method="POST";
        $this->setMethod($method);
        $this->ajax = true;
        $this->setSubmitValue("Save");
        $this->addAttribute("class","fapi-form");
    }

    /**
     * Renders the form. This method is called from the Form::render() method.
     */
    protected function renderForm()
    {
        $this->addAttribute("method",$this->getMethod());
        $this->addAttribute("id",$this->getId());
        
        if($this->getHasFile()) $this->addAttribute("enctype","multipart/form-data");

        $ret = '<form '.$this->getAttributes().'>';
        if($this->getHasFile())
        {
            $ret .= "<input type='hidden' name='MAX_FILE_SIZE' value='10485760' />";
        }
        if($this->error)
        {
            $ret .= "<div class='fapi-error'><ul>";
            foreach($this->errors as $error)
            {
                $ret .= "<li>$error</li>";
            }
            $ret .= "</ul></div>";
        }
        $ret .= $this->renderElements();

        $onclickFunction = "fapi_ajax_submit_".$this->getId()."()";
        $onclickFunction = str_replace("-","_",$onclickFunction);
        
        if($this->getShowClear())
        {
            $clearButton = '<input class="fapi-submit" type="reset" value="Clear"/>';
        }

        if($this->getShowSubmit())
        {
            $ret .= '<div id="fapi-submit-area">';
            $submitValue = $this->submitValue?('value="'.$this->submitValue.'"'):"";
            if($this->ajaxSubmit)
            {
                $ret .= sprintf('<input class="fapi-submit" type="button" %s onclick="%s"  /> %s',$submitValue,$onclickFunction,$clearButton);
            }
            else
            {
                $ret .= sprintf('<input class="fapi-submit" type="submit" %s /> %s',$submitValue,$clearButton);
            }
            $ret .= '</div>';
        }
        $id = $this->getId();
        
        if($id != '')
        {
            $ret .= "<input type='hidden' name='is_form_{$id}_sent' value='yes' />";
        }
        
        $ret .= '<input type="hidden" name="is_form_sent" value="yes" />';
        $ret .= '</form>';

        if($this->ajaxSubmit)
        {
            $elements = $this->getFields();
            $ajaxData = array();
            foreach($elements as $element)
            {
                $id = $element->getId();
                if($element->getStorable())
                {
                    $ajaxData[] = "'".urlencode($id)."='+document.getElementById('$id').".($element->getType()=="Field"?"value":"checked");
                }
                $validations = $element->getJsValidations();
                $validators .= "if(!fapiValidate('$id',$validations)) error = true;\n";
            }
            $ajaxData[] = "'fapi_dt=".urlencode($this->getDatabaseTable())."'";
            $ajaxData = addcslashes(implode("+'&'+", $ajaxData),"\\");

            $ret .=
            "<script type='text/javascript'>
            function $onclickFunction
            {
                var error = false;
                $validators
                if(error == false)
                {
                    \$.ajax({
                        type : 'POST',
                        url : '{$this->ajaxAction}',
                        data : $ajaxData
                    });
                }
            }
            </script>";
        }
        return $ret;
    }
    
    public function submittedCallback()
    {
        if($this->isFormSent())
        {
            $data = $this->getData();
            $validated = $this->validatorCallback==""?1:$this->executeCallback($this->validatorCallback,$data,$this,$this->validatorCallbackData);
            if($validated==1)
            {
                $this->executeCallback($this->callback,$data,$this,$this->callbackData);
            }
        }
    }

    public function render()
    {
        $this->onRender();
        $this->submittedCallback();
        return $this->renderForm();
    }

    /**
     * Sets the value which is displayed on the submit button.
     */
    public function setSubmitValue($submitValue)
    {
        $this->submitValue = $submitValue;
        return $this;
    }

    /**
     * Setter method for the showSubmit parameter.
     * @param boolean $showSubmit
     */
    public function setShowSubmit($showSubmit)
    {
        $this->showSubmit = $showSubmit;
        return $this;
    }

    /**
     * Getter method for the showSubmit parameter.
     */
    public function getShowSubmit()
    {
        return $this->showSubmit;
    }
    
    public function setShowClear($showClear)
    {
    	$this->showClear = $showClear;
    }
    
    public function getShowClear()
    {
    	return $this->showClear;
    }

    public function setShowField($show_field)
    {
        Container::setShowField($show_field);
        $this->setShowSubmit($show_field);
        return $this;
    }

    public function useAjax($validation=true,$submit=true)
    {
        $this->ajax = $validation;
        $this->ajaxSubmit = $submit;
    }
}
