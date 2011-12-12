<?php
abstract class Report
{
    const CONTENT_TEXT = "text";
    const CONTENT_TABLE = "table";
    const ORIENTATION_PORTRAIT = "P";
    const ORIENTATION_LANDSCAPE = "L";
    const PAPER_A4 = "A4";
    const PAPER_A5 = "A5";
    const PAPER_LETTER = "letter";
    
    protected $contents = array();
    public $title;
    public $description;
    private $pageInitialized = true;
    public $logo;
    public $label;
    public $filterSummary;
    
    public abstract function output($file = null);
    
    public function add()
    {
        $this->contents = array_merge($this->contents,func_get_args());
        return $this;
    }

    public function addPage($repeatLogos = false, $forced = false)
    {
        if(!$this->pageInitialized || $forced)
        {
            $this->contents[] = "NEW_PAGE";
            if($repeatLogos)
            {
                if($this->logo != null) $this->add($this->logo);
                if($this->label != null) $this->add($this->label);
                if($this->filterSummary != null) $this->add($this->filterSummary);
            }
        }
        else
        {
            $this->pageInitialized = false;
        }
    }

    public function resetPageNumbers()
    {
        $this->contents[] = "RESET_PAGE_NUMBERS";
    }
}

