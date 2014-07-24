<?php

/**
 * A special container which allows you to use multiple instances of
 * a single form to collect multiple data. These different data can be stored
 * into different models.
 *
 * @author james
 * @ingroup Forms
 */
class MultiElements extends Container
{
    const MODE_CONTAINER = "container";
    const MODE_FIELD = "field";
    
    /**
     * An instance of a fapi container which is used as a template form.
     * @var Container
     */
    protected $template;

    /**
     * The total number of forms which have been rendered so far.
     * @var int
     */
    private static $numForms;

    /**
     * A unique index for this instance of the MultiForm. This number is used in
     * the ids of the outputted HTML code so that all instances of the MultiElements
     * on any given page would have different DOM level ids for javascript
     * manupulation.
     * @var int
     */
    protected $index;

    /**
     * The label for this MultiElements instance.
     * @var unknown_type
     */
    public $label;
    protected $templateName;
    protected $data = array();
    protected $referenceField;
    protected $relatedField;
    public $hasRelatedData = true;

    public function __construct()
    {
        parent::__construct();
        MultiElements::$numForms++;
        $this->index = MultiElements::$numForms;
    }

    public function validate()
    {
        $retval = true;
        foreach($this->data as $data)
        {
            foreach($data as $key => $dat)
            {
                $data[$this->templateName.".".$key."[]"] = $dat;
            }
            $this->clearErrors();
            $this->template->setData($data);
            $retval = $this->template->validate();
        }
        return $retval;
    }

    private function _retrieveData()
    {
        if($this->isFormSent())
        {
            $this->data = array();
            $fields = $this->template->getFields();
            foreach($fields as $field)
            {
                $key = str_replace(array(".","[]"),array("_",""),$field->getName());
                for($i=0; $i<count($_POST[$key])-1; $i++)
                {
                    $name = str_replace(array($this->templateName.".","[]"),array("",""),$field->getName());
                    $field->setValue($_POST[$key][$i]);
                    $this->data[$i][$name] = $field->getValue();
                }
            }
        }

        foreach($this->data as $data)
        {
            $this->template->setData($data);
        }
        
        return $this->data;
    }

    public function getData($storable=false)
    {
        $this->_retrieveData();
        return array($this->templateName => $this->data);
    }

    public function setData($data)
    {
        //$this->_retrieveData();
        $this->data = $data[$this->templateName];
    }

    public function setTemplate($template)
    {
        $this->template = $template;
        $template->addCssClass("fapi-multiform-sub");
        $buttons = new ButtonBar();
        $buttons->setId("multi-form-buttons");
        $buttons->addButton("Clear");
        $buttons->buttons[0]->addAttribute("onclick","fapiMultiFormRemove('--index--')");

        $elements = $template->getFields();
        foreach($elements as $element)
        {
            if($element->getType()=="Field")
            {
                $element->setId($element->getId()==""?$element->getName():$element->getId());
                $element->setName($template->getName().".".$element->getName()."[]");
            }

            $element->setId($element->getId()."_--index--");
        }
        $this->templateName = $template->getName();
        $template->setId("multiform-content---index--");
        $template->add($buttons);
        return $this;
    }

    public function render()
    {
        $id = "multiform-".$this->index;
        $this->setId($id);
        $attributes = $this->getAttributes();

        if($this->data==null)$this->_retrieveData();

        if($this->template != null)
        {
            $this->template->clearErrors();
            $template = $this->template->render();
            $count = 0;
            
            foreach($this->data as $index => $data)
            {
                foreach($data as $key => $dat)
                {
                    $data[$this->templateName.".".$key."[]"] = $dat;
                }

                //$this->clearErrors();
                $this->template->setData($data);
                //$retval = $this->template->validate();
                $this->template->setId("multiform-content-".$index);
                $this->template->getElementById("multi-form-buttons")->buttons[0]->addAttribute("onclick","fapiMultiFormRemove('$index')");
                $contents .= "<div id='multi-form-content-$index'>".$this->template->render()."</div>";
            }
        }

        $ret = "<div $attributes >
                <input type='hidden' id='multiform-numitems-{$this->index}' value='$count'/>
                    <div id='multiform-contents-{$this->index}'>
                    $contents
                    </div>";
        $ret .= $this->getShowField()?"<div class='fapi-multiform-bar'><span onclick='fapiMultiFormAdd({$this->index})' style='font-size:smaller;cursor:pointer'>Add New</span></div>":"";
        $ret .="</div>
                <div id='multiform-template-{$this->index}' style='display:none'>
                    $template
                </div>";
        return $ret;
    }

    public function setShowField($showField)
    {
        parent::setShowField($showField);
        $this->template->setShowField($showField);
    }
}

