<?php
/**
 * 
 */
class TextContent extends ReportContent
{
    protected $text;
    public $style = array();
    
    public function __construct($text=null,$style=null)
    {
        if($style==null) $this->setStyle(); else $this->setStyle($style);
        $this->text = $text;
    }
    
    public function getType()
    {
        return "text";
    }
    
    public function setText($text)
    {
        $this->text = $text;
    }
    
    public function getText()
    {
        return $this->text;
    }
    
    public function setStyle($style=array("font"=>"Helvetica","size"=>12,"bold"=>false,"underline"=>false,"italics"=>false))
    {
        $this->style = $style;
    }
    
    public function getStyle()
    {
        return $this->style;
    }
}
