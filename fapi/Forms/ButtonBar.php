<?php
/**
 * A special container for containing buttons. This container renders its buttons
 * in a list. This element can be used in cases where you want to have different
 * buttons to submit the form with each having a different submit button.
 * 
 * @ingroup Forms
 * @see Button
 */
class ButtonBar extends Container
{
    /**
     * The buttons found in this contianer.
     * @var Array
     */
    public $buttons = array();
    private $barName;
    
    /**
     * Creates a new button bar.
     * @param string $name
     */
    public function __construct($name = null)
    {
        parent::__construct();
        $this->barName  = $name;
    }
    
    /**
     * Add a submit button to the button bar. The button added is an instance
     * of the SubmitButton class.
     * 
     * @param type $label
     * @return ButtonBar
     */
    public function addSubmitButton($label)
    {
        $button = new SubmitButton($label);
        $button->addAttribute('name', $this->barName);
        $this->buttons[] = $button;
        return $this;
    }

    /**
     * Add a new button to this bar. This method creates a new instance of the
     * button class and adds it to the button bar.
     * 
     * @param $label The label for this button
     * @return ButtonBar
     */
    public function addButton($label, $onClick = null)
    {
        $button = new Button($label);
        if($onClick !== null)
        {
            $button->addAttribute('onclick', $onClick);
        }
        $this->buttons[] = $button;
        return $this;
    }

    public function render()
    {
        $ret = "";
        foreach($this->buttons as $button)
        {
            $ret .= $button->render(). " ";
        }
        return $ret;
    }
}
?>
