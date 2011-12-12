<?php
abstract class ReportContent 
{
    protected $content;
    
    public function set($content)
    {
        $this->content;
    }
    
    public function get()
    {
        return $this->content;
    }
    
    public abstract function getType();
}
?>
