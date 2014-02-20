<?php
/**
 * A special form element which allows the user to enter arbitrary HTML code
 * in a form.
 * @author ekow
 * @ingroup Forms
 */
class HTMLBox extends Container
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
    
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }
    
    public function render()
    {
        return $this->content;
    }
    
    public function hasLabel()
    {
    	return true;
    }
}