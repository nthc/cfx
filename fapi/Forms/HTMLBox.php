<?php
/**
 * A special form element which allows the user to enter arbitrary HTML code
 * in a form.
 * @author ekow
 * @ingroup Forms
 */
class HTMLBox extends Element
{
    public $content;
    
    /**
     * Creates a new HTML box
     * @code
     * $box = new HTMLBox("<h1>Hello World!</h1>");
     * @endcode
     * @param string $content
     */
    public function __construct($content)
    {
        $this->content  = $content;    
    }
    
    public function render()
    {
        return $this->content;
    }
    
    public function getData()
    {
        return array();
    }
    
    public function validate()
    {
        
    }
    
    public function hasLabel()
    {
    	return true;
    }
}