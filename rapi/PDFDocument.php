<?php
class PDFDocument extends FPDF
{
    protected $processingTable;
    protected $tableWidths;
    protected $tableHeaders;
    public $header;
    public $footer;
    public $style;
    public $twidth;
    public $showPageNumbers = true;
    public $showFooter = true;
    private $pageNumberOffset;
    private $headerHeight;
    public $xOffset;
    public $yOffset;

    const ORIENTATION_PORTRAIT = "P";
    const ORIENTATION_LANDSCAPE = "L";

    public function __construct($orientation="L",$paper="A4")
    {
        parent::__construct($orientation,"mm",$paper);

        if(is_string($paper))
        {
            switch($paper)
            {
            	case "A3":
            		if($orientation == "L")
            		$this->twidth = 400;
            		else
            		$this->twidth = 277;
            		break;
                case "A4":
                    if($orientation=="L")
                    $this->twidth = 277;
                    else
                    $this->twidth = 190;
                    break;

                case "A5":
                    if($orientation=="L")
                    $this->twidth = 190;
                    else
                    $this->twidth = 128;
                    break;
            }
        }
        elseif(is_array($paper))
        {
            $this->twidth = $paper[1]-20;
        }

        $this->AddPage();
        $this->SetFont('Helvetica', null, 8);
        $this->SetAutoPageBreak(true, 15);
    }

    public function getTableWidths($headers=array(),$data=array())
    {
        $widths = array();
        foreach($headers as $i=>$header)
        {
            $lines = explode("\n",$header);
            foreach($lines as $line)
            {
                $widths[$i] = strlen($line)/4 > $widths[$i] ? strlen($line)/4 : $widths[$i];
            }
            $widths[$i] = $widths[$i];
        }
        foreach($data as $row)
        {
            $i = 0;
            foreach($row as $column)
            {
                $widths[$i] = strlen($column) > $widths[$i] ? strlen($column) : $widths[$i];
                $i++;
            }
        }

        return $widths;
    }

    public function Header()
    {
        $this->SetFont('Helvetica','I',8);
        if(strlen(trim($header))>0)
        {
            $this->Cell(0,0,$header,0,0,'L');
            $this->Ln(5);
        }

        if($this->processingTable) $this->tableHeader();
    }

    public function Footer()
    {
        if($this->showFooter === true)
        {
            $this->SetY(-15);
            $this->SetFont('Helvetica','I',6);
            if($this->showPageNumbers === true)
            {
                $this->Cell(40,10,'Page '.($this->PageNo() - $this->pageNumberOffset),0,0);
            }
            $this->Cell(0,10,"Generated on ".date("jS F, Y @ g:i:s A")." by ".$_SESSION["user_lastname"]." ".$_SESSION["user_firstname"],0,0,'R');
            $this->Ln();
        }
    }

    public function resetPageNumbers()
    {
        $this->pageNumberOffset = $this->PageNo() - 1;
    }
    
    public function attributeBox($data, $style)
    {
        $fontSize = $style["size"] == null ? 10 : $style["size"];
        $this->SetFont("Helvetica","B",$fontSize);
        $maxWidth = 0;
        $maxValWidth = 0;
        foreach($data as $dat)
        {
            $width = $this->GetStringWidth($dat[0]); 
            if($width > $maxWidth)
            {
                $maxWidth = $width;
            }
            
            $width = $this->GetStringWidth($dat[1]); 
            if($width > $maxValWidth)
            {
                $maxValWidth = $width;
            }
        }
    
        $maxWidth+=10;
                
        foreach($data as $dat)
        {
            $this->Cell($maxWidth,$fontSize *1.4*0.353,$dat[0]);
            $this->SetFont("Helvetica","",$fontSize);
            $this->Cell($maxValWidth,$fontSize *1.4*0.353,$dat[1], 0, 0, $style['align']);
            $this->SetFont("Helvetica","B",$fontSize);
            $this->Ln();
        }
        
    }

    protected function tableHeader()
    {
        $fill = false;
        $borders = 0;
        if($this->style["decoration"]===true)
        {
            switch($this->style["heading"])
            {
                case "PLAIN":
                    $this->SetFillColor(255,255,255);
                    $this->SetTextColor(0,0,0);
                    $this->SetDrawColor(102,128,102);
                    $fill = true;
                    $borders = 1;
                    $headingStyle = 'B';
                    break;
                    
                default:
                    $this->SetFillColor(102,128,102);
                    $this->SetTextColor(255,255,255);
                    $this->SetDrawColor(102,128,102);
                    $fill = true;
                    $borders = 1;
                    $headingStyle = 'B';
            }
        }

        //$this->SetFont('Helvetica',$headingStyle,8);
        $this->SetFont
        (    isset($this->style["font"])?$this->style["font"]:"Helvetica",
            "B", //($this->style["bold"]?"B":"").($this->style["underline"]?"U":"").($this->style["italics"]?"I":""),
            isset($this->style["font_size"])?$this->style["font_size"]:8
        );

        foreach($this->tableHeaders as $i => $tableHeader)//for($i=0;$i<count($this->tableHeaders);$i++)
        {
            $this->WrapCell(
            $this->tableWidths[$i],$this->headerHeight * $this->style["font_size"] * 0.353 + 3 , $this->tableHeaders[$i],$borders,0,'L',$fill);
        }

        $this->Ln();
    }

    public function totalsBox($totals,$params)
    {
        $this->SetFont
        (    isset($this->style["font"])?$this->style["font"]:"Helvetica",
        ($this->style["bold"]?"B":"B").($this->style["underline"]?"U":"").($this->style["italics"]?"I":""),
        isset($this->style["font_size"])?$this->style["font_size"]:8
        );
                
        $arrayWidth = $this->twidth * (isset($this->style["width"])?$this->style["width"]:1);

        $max = array_sum($params["widths"]);
        foreach($params["widths"] as $i=>$width)
        {
            $params["widths"][$i] =$params["widths"][$i] / $max;
        }
            
        foreach($params["widths"] as $i=>$width)
        {
            $params["widths"][$i] = $params["widths"][$i] * $arrayWidth;
        }
        
        $this->SetDrawColor(204,255,204);
        for($i=0;$i<count($params["widths"]);$i++)
        {
            if(isset($totals[$i]) && $i!=0)
            {
                $totals[$i] = str_replace(",", "", $totals[$i]);
                if($params['type'][$i] == 'double')
                {
                    $totals[$i] = number_format($totals[$i], 2,".",",");
                }
                elseif ($params['type'][$i] == 'integer' || $params['type'][$i] == 'number')
                {
                    $totals[$i] = number_format($totals[$i], 0,".",",");
                }
                else
                {
                    
                }
            }
            else
            {
                $borders = 0;
            }

            $this->Cell($params["widths"][$i],$this->style["cell_height"],$totals[$i],$borders,0,$i==0?'L':'R');
        }
        $this->Ln();

        if($this->style["decoration"]===true)
        {
            $this->SetDrawColor(102,128,102);
            $this->Cell(array_sum($params["widths"]),0,'','T');
            $this->Ln(0.4);
            $this->Cell(array_sum($params["widths"]),0,'','T');
            $this->Ln();
        }
    }

    public function table($headers,$data,$style=null,&$params=null)
    {
        $this->style = $style!=null?$style:$this->style;
        $this->style["font_size"] = $this->style["font_size"] == "" ? 8 : $this->style["font_size"];

        foreach($headers as $key=>$header)
        {
            $header = str_replace("\\n", "\n", $header);
            $headers[$key] = $header;
            $lines = explode("\n",$header);
            $this->headerHeight = count($lines)>$this->headerHeight?count($lines):$this->headerHeight;
        }

        if(isset($params["widths"]))
        {
            $widths = $params["widths"];
        }
        else
        {
            $widths = $this->getTableWidths($headers,$data);
            $params["widths"] = $widths;
        }

        //array($widths);

        $max = array_sum($params["widths"]);
        foreach($params["widths"] as $i=>$width)
        {
            $widths[$i] = $widths[$i] / $max;
        }
        $arrayWidth = $this->twidth * (isset($this->style["width"])?$this->style["width"]:1);
        $this->style["cell_height"] = isset($this->style["cell_height"])?$this->style["cell_height"]: $this->style["font_size"] * 0.353 + 1;


        foreach($widths as $i=>$width)
        {
            $widths[$i] = $widths[$i] * $arrayWidth;
        }
        
        $this->tableWidths = $widths;
        $this->tableHeaders = $headers;
        $this->tableHeader();
        
        $this->processingTable = true;
        
        $this->SetFillColor(255,255,255);
        $this->SetTextColor(0);
        $this->SetFont
        (    isset($this->style["font"])?$this->style["font"]:"Helvetica",
        ($this->style["bold"]?"B":"").($this->style["underline"]?"U":"").($this->style["italics"]?"I":""),
        isset($this->style["font_size"])?$this->style["font_size"]:8
        );

        if($this->style["decoration"]===true)
        {
            $this->SetDrawColor(180,200,180);
            $fill = false;
            $border = 1;
        }
        else
        {
            $fill = false;
            $border = 0;
        }

        foreach($data as $fields)
        {
            $keys = array_keys($widths);
            $i = reset($keys);
            //print implode(",", $fields) . "<br/>";
            foreach($fields as $field)
            {
                switch($params["type"][$i])
                {
                    case "number":
                        $align = "R";
                        $field = $field === null || $field == "" ? "0" : number_format(str_replace(",","",$field),0,".",",");
                        break;
                    case "double":
                        //print $field;
                        $align = "R";
                        $field = $field === null || $field == "" ? "0.00" : number_format(str_replace(",","",$field),2,".",",");
                        break;
                    case "right_align":
                        $align = "R";
                        break;
                    default:
                        $align = "L";
                        break;
                }
    
                $this->Cell($widths[$i],$this->style["cell_height"],str_replace("\n"," ",$field),$border,0,$align,true);
                if(is_array($params['total']))
                {
                    if(array_search($i,$params["total"])!==false)
                    {
                        $totals[$i]+=$field;
                    }
                }
                $i = next($keys);
            }
            if($this->style["decoration"]===true) $fill=!$fill;
            if($fill)
            {
                $this->SetFillColor(204,255,204);
            }
            else
            {
                $this->SetFillColor(255,255,255);
            }
            $this->Ln();
        }

        if($this->style["decoration"]===true)
        {
            $this->SetDrawColor(102,128,102);
            $this->Cell(array_sum($widths),0,'','T');
        }

        $this->Ln();
        $this->processingTable = false;
    }
    
    public function WriteHTML($height, $html)
	{
	    //HTML parser
	    $html=str_replace("\n",' ',$html);
	    $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
	    foreach($a as $i=>$e)
	    {
	        if($i%2==0)
	        {
	            //Text
	            if($this->HREF)
	                $this->PutLink($this->HREF,$e);
	            else
	                $this->Write($height, $e);
	        }
	        else
	        {
	            //Tag
	            if($e{0}=='/')
	                $this->CloseTag(strtoupper(substr($e,1)));
	            else
	            {
	                //Extract attributes
	                $a2=explode(' ',$e);
	                $tag=strtoupper(array_shift($a2));
	                $attr=array();
	                foreach($a2 as $v)
	                    if(ereg('^([^=]*)=["\']?([^"\']*)["\']?$',$v,$a3))
	                        $attr[strtoupper($a3[1])]=$a3[2];
	                $this->OpenTag($tag,$attr);
	            }
	        }
	    }
	}
	
    public function OpenTag($tag,$attr)
	{
	    //Opening tag
	    if($tag=='B' or $tag=='I' or $tag=='U')
	        $this->SetStyle($tag,true);
	    if($tag=='A')
	        $this->HREF=$attr['HREF'];
	    if($tag=='BR')
	        $this->Ln(5);
	}
	
    public function CloseTag($tag)
	{
	    //Closing tag
	    if($tag=='B' or $tag=='I' or $tag=='U')
	        $this->SetStyle($tag,false);
	    if($tag=='A')
	        $this->HREF='';
	}
	
    public function SetStyle($tag,$enable)
	{
	    //Modify style and select corresponding font
	    $this->$tag+=($enable ? 1 : -1);
	    $style='';
	    foreach(array('B','I','U') as $s)
	        if($this->$s>0)
	            $style.=$s;
	    $this->SetFont('',$style);
	}
	
	public function PutLink($URL,$txt)
	{
	    //Put a hyperlink
	    $this->SetTextColor(0,0,255);
	    $this->SetStyle('U',true);
	    $this->Write(5,$txt,$URL);
	    $this->SetStyle('U',false);
	    $this->SetTextColor(0);
	}
    
    public function SetXY($x, $y)
    {
        parent::SetXY($x + $this->xOffset, $y + $this->yOffset);
    }
}
