<?php
class AttributeBox extends ReportContent
{
    public $data;
    public $style;
    
    public function __construct($data = null, $style = null)
    {
        $this->data = $data;
        $this->style = $style;
    }
    
    public function getType()
    {
        return "attributes";
    }
}