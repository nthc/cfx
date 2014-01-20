<?php

require_once "PDFDocument.php";

/**
 * 
 * @author james
 */
class PDFReport extends Report
{   
    public $header;
    public $footer;
    public $orientation;
    public $paper;
    public $showPageNumbers = true;
    public $showFooter = true;
    public $resetPageNumbers;
    
    /**
     * 
     * Enter description here ...
     * @var PDFDocument
     */
    protected $pdf;
    
    public function __construct($orientation=PDFReport::ORIENTATION_LANDSCAPE,$paper="A4")
    {
        $this->orientation = $orientation;
        $this->paper = $paper;
    }

    public function setOptions($options)
    {
        $this->orientation = isset($options["orientation"])?$options["orientation"]:$this->orientation;
        $this->paper = isset($options["paper"])?$options["paper"]:$this->paper;
        $this->showPageNumbers = isset($options["showPageNumbers"])?$options["showPageNumbers"]:$this->showPageNumbers;
    }
    
    protected function pdfPreCustomize()
    {
    	
    }
    
    protected function pdfPostCustomize()
    {
    	
    }

    public function output($file = null)
    {
    	ob_clean();
        $this->pdf = new PDFDocument($this->orientation,$this->paper);
        $this->pdfPreCustomize();
        $this->pdf->showPageNumbers = $this->showPageNumbers;
        $this->pdf->showFooter = $this->showFooter;

        foreach($this->contents as $key => $content)
        {
            if($content == null) continue;
            if(is_string($content))
            {
                switch($content)
                {
                    case "NEW_PAGE":
                        $this->pdf->AddPage();
                        break;
                    case "RESET_PAGE_NUMBERS":
                        $this->pdf->resetPageNumbers();
                        break;
                }
                continue;
            }
            
            switch($content->getType())
            {
            case "text":
                if(isset($content->style["color"]))
                {                    
                    $this->pdf->SetTextColor(
                        $content->style["color"][0],
                        $content->style["color"][1],
                        $content->style["color"][2]
                    );
                } else {
                    $this->pdf->SetTextColor(0, 0, 0);
                }
                
                $this->pdf->SetFont(
                    $content->style["font"],
                    
                    ($content->style["bold"]?"B":"").
                    ($content->style["underline"]?"U":"").
                    ($content->style["italics"]?"I":""),
                    
                    $content->style["size"]
                );
                if(isset($content->style["top_margin"])) $this->pdf->Ln($content->style["top_margin"]);

                if($content->style["flow"])
                {
                    $this->pdf->WriteHTML(
                        $content->style['line_height'] == '' ? 
                            $content->style["size"] * 0.353 + 1 : 
                            $content->style['line_height'], 
                        $content->getText()
                    );
                    $this->pdf->Ln();
                }
                else
                {
                    $this->pdf->SetFillColor(180, 200, 180);
                    $this->pdf->Cell(0,isset($content->style["height"])?$content->style["height"]:$content->style["size"]*0.353+1,$content->getText(),0,0,$content->style["align"],$content->style["fill"]);
                    $this->pdf->Ln();
                }

                if(isset($content->style["bottom_margin"])) $this->pdf->Ln($content->style["bottom_margin"]);
                break;
                
            case "attributes":
                $this->pdf->attributeBox($content->data, $content->style);
                break;

            case "table":
                if($content->style["totalsBox"]==true)
                {
                    $this->pdf->totalsBox($content->getData(),$content->data_params);
                }
                else if($content->style["autoTotalsBox"])
                {
                    $this->pdf->table($content->getHeaders(),$content->getData(),$content->style,$content->data_params);
                    $totals = $content->getTotals();
                    $totals[0] = "Totals";
                    $this->pdf->totalsBox($totals,$content->data_params);
                }
                else
                {
                    $this->pdf->table($content->getHeaders(),$content->getData(),$content->style,$content->data_params);
                    $this->contents[$key] = $content;
                }
                break;
            case "image":
                $this->pdf->image($content->image, null, null, $content->width, $content->height);
                break;  
            
            case "logo":
                $this->pdf->image($content->image,null,null,8,8);
                $this->pdf->sety($this->pdf->getY() - 8);
                $this->pdf->SetFont("Times","B","18");
                $this->pdf->cell(9);$this->pdf->cell(0,8,$content->title);
                
                $this->pdf->SetFont("Arial",null,7);
                foreach($content->address as $address)
                {
                    $this->pdf->setx(($this->pdf->GetStringWidth($address)+10) * -1);
                    $this->pdf->cell(0,3,$address);
                    $this->pdf->Ln();
                }
                
                $this->pdf->Ln(5);
            }
        }
        $this->pdfPostCustomize();
        
        if($file == null)
        {
            $this->pdf->Output();
            die();
        }
        else
        {
            $this->pdf->Output($file);
        }
    }
}
