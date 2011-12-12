<?php
/**
 * A special container for containing buttons.
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

    /**
     * Add a new button to this bar. This method creates a new instance of the
     * button class and adds it to the button bar.
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
        if(Element::getShowfield())
        {
            foreach($this->buttons as $button)
            {
                $ret .= $button->render(). " ";
            }
        }
        return $ret;
    }
}
?>
