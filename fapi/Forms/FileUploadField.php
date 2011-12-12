<?php
/**
 * 
 * @author James Ainooson
 * @ingroup Forms
 */
class FileUploadField extends Field
{
    protected $destinationFile;
    protected $defaultFile;
    protected $destinationDirectory;
    protected $actualDirectory;
    protected $script;
    protected $jsExpression;
    protected $showAfterExecution = "false";

    public function __construct($label="",$name="",$description="",$value="",$destinationFile="")
    {
        Field::__construct($name,$value);
        Element::__construct($label, $description);
        $this->addAttribute("type","file");
        $this->hasFile = true;
    }
    
    public function setScript($script)
    {
        $this->script = $script;
        return $this;
    }
    
    public function setJsExpression($expression, $showAfterExecution = "false")
    {
        $this->jsExpression = $expression;
        $this->showAfterExecution = $showAfterExecution;
        return $this;
    }

    public function render()
    {
        $this->setAttribute("id",$this->getId());
        $this->addAttribute("name",$this->getName());
        $attributes = $this->getAttributes();
        $ret .= "<input $attributes type='file' class='fapi-fileupload' onchange=\"fapiStartUpload(this,this.form,'{$this->script}','{$this->jsExpression}',{$this->showAfterExecution})\" />";
        return $ret;
    }
}

