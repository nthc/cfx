<?php

class PDFDocument extends fpdf\FPDF_EXTENDED
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

    public function __construct($orientation = "L", $paper = "A4")
    {
        parent::__construct(10, $orientation, "mm", $paper);

        if (is_string($paper))
        {
            switch ($paper)
            {
                case "A3":
                    if ($orientation == "L")
                        $this->twidth = 400;
                    else
                        $this->twidth = 277;
                    break;

                case "A4":
                    if ($orientation == "L")
                        $this->twidth = 277;
                    else
                        $this->twidth = 190;
                    break;

                case "A5":
                    if ($orientation == "L")
                        $this->twidth = 190;
                    else
                        $this->twidth = 128;
                    break;
            }
        }
        elseif (is_array($paper))
        {
            if ($orientation = "P")
                $this->twidth = $paper[1] - 20;
            else
                $this->twidth = $paper[0] - 20;
        }

        $this->AddPage();
        $this->SetFont('Helvetica', null, 8);
        $this->SetAutoPageBreak(true, 15);
    }

    public function getTableWidths($headers = array(), $data = array())
    {
        $widths = array();
        foreach ($headers as $i => $header)
        {
            $lines = explode("\n", $header);
            foreach ($lines as $line)
            {
                $widths[$i] = strlen($line) / 4 > $widths[$i] ? strlen($line) / 4 : $widths[$i];
            }
            $widths[$i] = $widths[$i];
        }
        foreach ($data as $row)
        {
            $i = 0;
            foreach ($row as $column)
            {
                $widths[$i] = strlen($column) > $widths[$i] ? strlen($column) : $widths[$i];
                $i++;
            }
        }

        return $widths;
    }

    public function Header()
    {
        $this->SetFont('Helvetica', 'I', 8);
        if (strlen(trim($header)) > 0)
        {
            $this->Cell(0, 0, $header, 0, 0, 'L');
            $this->Ln(5);
        }

        if ($this->processingTable)
            $this->tableHeader();
    }

    public function Footer()
    {
        if ($this->showFooter === true)
        {
            $this->SetY(-15);
            $this->SetFont('Helvetica', 'I', 6);
            if ($this->showPageNumbers === true)
            {
                $this->Cell(40, 10, 'Page ' . ($this->PageNo() - $this->pageNumberOffset), 0, 0);
            }
            $this->Cell(0, 10, "Generated on " . date("jS F, Y @ g:i:s A") . " by " . $_SESSION["user_lastname"] . " " . $_SESSION["user_firstname"], 0, 0, 'R');
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
        $font = $style['font'] == null ? 'Helvetica' : $style['font'];
        $this->SetFont($font, "B", $fontSize);
        $maxWidth = 0;
        $maxValWidth = 0;
        foreach ($data as $dat)
        {
            $width = $this->GetStringWidth($dat[0]);
            if ($width > $maxWidth)
            {
                $maxWidth = $width;
            }

            $width = $this->GetStringWidth($dat[1]);
            if ($width > $maxValWidth)
            {
                $maxValWidth = $width;
            }
        }

        $maxWidth+=10;

        foreach ($data as $dat)
        {
            $this->Cell($maxWidth, $fontSize * 1.4 * 0.353, $dat[0]);
            $this->SetFont($font, "", $fontSize);
            $this->Cell($maxValWidth, $fontSize * 1.4 * 0.353, $dat[1], 0, 0, $style['align']);
            $this->SetFont($font, "B", $fontSize);
            $this->Ln();
        }
    }

    protected function setFillColorArray($color)
    {
        $this->setFillColor($color[0], $color[1], $color[2]);
    }

    protected function setDrawColorArray($color)
    {
        $this->setDrawColor($color[0], $color[1], $color[2]);
    }

    protected function setTextColorArray($color)
    {
        $this->setTextColor($color[0], $color[1], $color[2]);
    }

    protected function tableHeader()
    {
        $fill = false;
        $borders = 0;
        if ($this->style["decoration"] === true)
        {
            switch ($this->style["heading"])
            {
                case "PLAIN":
                    $this->SetFillColor(255, 255, 255);
                    $this->SetTextColor(0, 0, 0);
                    $this->SetDrawColorArray($this->style['header:border']);
                    $fill = true;
                    $borders = 1;
                    $headingStyle = 'B';
                    break;

                default:
                    $this->SetFillColorArray($this->style['header:background']);
                    $this->SetTextColorArray($this->style['header:text']);
                    $this->SetDrawColorArray($this->style['header:border']);
                    $fill = true;
                    $borders = 1;
                    $headingStyle = 'B';
            }
        }

        //$this->SetFont('Helvetica',$headingStyle,8);
        $this->SetFont
                (isset($this->style["font"]) ? $this->style["font"] : "Helvetica", "B", //($this->style["bold"]?"B":"").($this->style["underline"]?"U":"").($this->style["italics"]?"I":""),
                isset($this->style["font_size"]) ? $this->style["font_size"] : 8
        );

        foreach ($this->tableHeaders as $i => $tableHeader)//for($i=0;$i<count($this->tableHeaders);$i++)
        {
            $this->WrapCell(
                    $this->tableWidths[$i], $this->headerHeight * $this->style["font_size"] * 0.353 + 3, $this->tableHeaders[$i], $borders, 0, 'L', $fill);
        }

        $this->Ln();
    }

    public function totalsBox($totals, $params)
    {
        $this->SetFont
                (isset($this->style["font"]) ? $this->style["font"] : "Helvetica", ($this->style["bold"] ? "B" : "B") . ($this->style["underline"] ? "U" : "") . ($this->style["italics"] ? "I" : ""), isset($this->style["font_size"]) ? $this->style["font_size"] : 8
        );

        $arrayWidth = $this->twidth * (isset($this->style["width"]) ? $this->style["width"] : 1);

        $max = array_sum($params["widths"]);
        foreach ($params["widths"] as $i => $width)
        {
            $params["widths"][$i] = $params["widths"][$i] / $max;
        }

        foreach ($params["widths"] as $i => $width)
        {
            $params["widths"][$i] = $params["widths"][$i] * $arrayWidth;
        }

        $this->SetDrawColor(204, 255, 204);
        for ($i = 0; $i < count($params["widths"]); $i++)
        {
            if (isset($totals[$i]) && $i != 0)
            {
                //$totals[$i] = str_replace(",", "", $totals[$i]);
                if ($params['type'][$i] == 'double')
                {
                    $totals[$i] = number_format($totals[$i], 2, ".", ",");
                }
                elseif ($params['type'][$i] == 'integer' || $params['type'][$i] == 'number')
                {
                    $totals[$i] = number_format($totals[$i], 0, ".", ",");
                }
            }
            else
            {
                $borders = 0;
            }

            $this->Cell($params["widths"][$i], $this->style["cell_height"], $totals[$i], $borders, 0, $i == 0 ? 'L' : 'R');
        }
        $this->Ln();

        if ($this->style["decoration"] === true)
        {
            $this->SetDrawColorArray($this->style['body:border']);
            $this->Cell(array_sum($params["widths"]), 0, '', 'T');
            $this->Ln(0.4);
            $this->Cell(array_sum($params["widths"]), 0, '', 'T');
            $this->Ln();
        }
    }

    public function table($headers, $data, $style = null, &$params = null)
    {
        $this->style = $style != null ? $style : $this->style;
        $this->style["font_size"] = $this->style["font_size"] == "" ? 8 : $this->style["font_size"];

        foreach ($headers as $key => $header)
        {
            $header = str_replace("\\n", "\n", $header);
            $headers[$key] = $header;
            $lines = explode("\n", $header);
            $this->headerHeight = count($lines) > $this->headerHeight ? count($lines) : $this->headerHeight;
        }

        if (isset($params["widths"]))
        {
            $widths = $params["widths"];
        }
        else
        {
            $widths = $this->getTableWidths($headers, $data);
            $params["widths"] = $widths;
        }

        //array($widths);

        $max = array_sum($params["widths"]);
        foreach ($params["widths"] as $i => $width)
        {
            $widths[$i] = $widths[$i] / $max;
        }
        $arrayWidth = $this->twidth * (isset($this->style["width"]) ? $this->style["width"] : 1);
        $this->style["cell_height"] = isset($this->style["cell_height"]) ? $this->style["cell_height"] : $this->style["font_size"] * 0.353 + 1;


        foreach ($widths as $i => $width)
        {
            $widths[$i] = $widths[$i] * $arrayWidth;
        }

        $this->tableWidths = $widths;
        $this->tableHeaders = $headers;
        $this->tableHeader();

        $this->processingTable = true;

        $this->SetFillColorArray($this->style['body:background']);
        $this->SetTextColorArray($this->style['body:text']);
        $this->SetFont(
                isset($this->style["font"]) ? $this->style["font"] : "Helvetica", ($this->style["bold"] ? "B" : "") . ($this->style["underline"] ? "U" : "") . ($this->style["italics"] ? "I" : ""), isset($this->style["font_size"]) ? $this->style["font_size"] : 8
        );

        if ($this->style["decoration"] === true)
        {
            $this->SetDrawColorArray($this->style['body:border']);
            $fill = false;
            $border = 1;
        }
        else
        {
            $fill = false;
            $border = 0;
        }

        foreach ($data as $fields)
        {
            $keys = array_keys($widths);
            $i = reset($keys);
            //print implode(",", $fields) . "<br/>";
            foreach ($fields as $field)
            {
                switch ($params["type"][$i])
                {
                    case "number":
                        $align = "R";
                        $field = $field === null || $field == "" ? "0" : number_format(str_replace(",", "", $field), 0, ".", ",");
                        break;
                    case "double":
                        //print $field;
                        $align = "R";
                        $field = $field === null || $field == "" ? "0.00" : number_format(str_replace(",", "", $field), 2, ".", ",");
                        break;
                    case "right_align":
                        $align = "R";
                        break;
                    default:
                        $align = "L";
                        break;
                }

                if ($params['wrap_cell'] === true)
                {
                    $this->WrapCell(
                            $widths[$i], $this->style['cell_height'], $field, $border, 0, $align, true
                    );
                }
                else
                {
                    $this->Cell($widths[$i], $this->style["cell_height"], str_replace("\n", " ", $field), $border, 0, $align, true);
                }

                if (is_array($params['total']))
                {
                    if (array_search($i, $params["total"]) !== false)
                    {
                        $totals[$i]+=$field;
                    }
                }
                $i = next($keys);
            }
            if ($this->style["decoration"] === true)
                $fill = !$fill;
            if ($fill)
            {
                $this->SetFillColorArray($this->style['body:stripe']);
            }
            else
            {
                $this->SetFillColorArray($this->style['body:background']);
            }
            $this->Ln();
        }

        if ($this->style["decoration"] === true)
        {
            $this->SetDrawColorArray($this->style['body:border']);
            $this->Cell(array_sum($widths), 0, '', 'T');
        }

        $this->Ln();
        $this->processingTable = false;
    }

    public function WriteHTML($height, $html)
    {
        //HTML parser
        $html = str_replace("\n", ' ', $html);
        $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($a as $i => $e)
        {
            if ($i % 2 == 0)
            {
                //Text
                if ($this->HREF)
                    $this->PutLink($this->HREF, $e);
                else
                    $this->Write($height, $e);
            }
            else
            {
                //Tag
                if ($e{0} == '/')
                    $this->CloseTag(strtoupper(substr($e, 1)));
                else
                {
                    //Extract attributes
                    $a2 = explode(' ', $e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = array();
                    foreach ($a2 as $v)
                        if (ereg('^([^=]*)=["\']?([^"\']*)["\']?$', $v, $a3))
                            $attr[strtoupper($a3[1])] = $a3[2];
                    $this->OpenTag($tag, $attr);
                }
            }
        }
    }

    public function OpenTag($tag, $attr)
    {
        //Opening tag
        if ($tag == 'B' or $tag == 'I' or $tag == 'U')
            $this->SetStyle($tag, true);
        if ($tag == 'A')
            $this->HREF = $attr['HREF'];
        if ($tag == 'BR')
            $this->Ln(5);
    }

    public function CloseTag($tag)
    {
        //Closing tag
        if ($tag == 'B' or $tag == 'I' or $tag == 'U')
            $this->SetStyle($tag, false);
        if ($tag == 'A')
            $this->HREF = '';
    }

    public function SetStyle($tag, $enable)
    {
        //Modify style and select corresponding font
        $this->$tag+=($enable ? 1 : -1);
        $style = '';
        foreach (array('B', 'I', 'U') as $s)
            if ($this->$s > 0)
                $style.=$s;
        $this->SetFont('', $style);
    }

    public function PutLink($URL, $txt)
    {
        //Put a hyperlink
        $this->SetTextColor(0, 0, 255);
        $this->SetStyle('U', true);
        $this->Write(5, $txt, $URL);
        $this->SetStyle('U', false);
        $this->SetTextColor(0);
    }

    public function SetXY($x, $y)
    {
        parent::SetXY($x + $this->xOffset, $y + $this->yOffset);
    }

    public function WrapCell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = 0, $link = '')
    {
        //Output a cell
        $k = $this->k;
        if ($this->y + $h > $this->PageBreakTrigger and ! $this->InFooter and $this->AcceptPageBreak())
        {
            $x = $this->x;
            $ws = $this->ws;
            if ($ws > 0)
            {
                $this->ws = 0;
                $this->_out('0 Tw');
            }
            $this->AddPage($this->CurOrientation);
            $this->x = $x;
            if ($ws > 0)
            {
                $this->ws = $ws;
                $this->_out(sprintf('%.3f Tw', $ws * $k));
            }
        }
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $s = '';
        // begin change Cell function 12.08.2003
        if ($fill == 1 or $border > 0)
        {
            if ($fill == 1)
                $op = ($border > 0) ? 'B' : 'f';
            else
                $op = 'S';
            if ($border > 1)
            {
                $s = sprintf(' q %.2f w %.2f %.2f %.2f %.2f re %s Q ', $border, $this->x * $k, ($this->h - $this->y) * $k, $w * $k, -$h * $k, $op);
            }
            else
                $s = sprintf('%.2f %.2f %.2f %.2f re %s ', $this->x * $k, ($this->h - $this->y) * $k, $w * $k, -$h * $k, $op);
        }
        if (is_string($border))
        {
            $x = $this->x;
            $y = $this->y;
            if (is_int(strpos($border, 'L')))
                $s.=sprintf('%.2f %.2f m %.2f %.2f l S ', $x * $k, ($this->h - $y) * $k, $x * $k, ($this->h - ($y + $h)) * $k);
            else if (is_int(strpos($border, 'l')))
                $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ', $x * $k, ($this->h - $y) * $k, $x * $k, ($this->h - ($y + $h)) * $k);

            if (is_int(strpos($border, 'T')))
                $s.=sprintf('%.2f %.2f m %.2f %.2f l S ', $x * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - $y) * $k);
            else if (is_int(strpos($border, 't')))
                $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ', $x * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - $y) * $k);

            if (is_int(strpos($border, 'R')))
                $s.=sprintf('%.2f %.2f m %.2f %.2f l S ', ($x + $w) * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
            else if (is_int(strpos($border, 'r')))
                $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ', ($x + $w) * $k, ($this->h - $y) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);

            if (is_int(strpos($border, 'B')))
                $s.=sprintf('%.2f %.2f m %.2f %.2f l S ', $x * $k, ($this->h - ($y + $h)) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
            else if (is_int(strpos($border, 'b')))
                $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ', $x * $k, ($this->h - ($y + $h)) * $k, ($x + $w) * $k, ($this->h - ($y + $h)) * $k);
        }
        if (trim($txt) != '')
        {
            $cr = substr_count($txt, "\n");
            if ($cr > 0)
            { // Multi line
                $txts = explode("\n", $txt);
                $lines = count($txts);
                //$dy=($h-2*$this->cMargin)/$lines;
                for ($l = 0; $l < $lines; $l++)
                {
                    $txt = $txts[$l];
                    $w_txt = $this->GetStringWidth($txt);
                    if ($align == 'R')
                        $dx = $w - $w_txt - $this->cMargin;
                    elseif ($align == 'C')
                        $dx = ($w - $w_txt) / 2;
                    else
                        $dx = $this->cMargin;

                    $txt = str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $txt)));
                    if ($this->ColorFlag)
                        $s.='q ' . $this->TextColor . ' ';
                    $s.=sprintf('BT %.2f %.2f Td (%s) Tj ET ', ($this->x + $dx) * $k, ($this->h - ($this->y + .5 * $h + (.7 + $l - $lines / 2) * $this->FontSize)) * $k, $txt);
                    if ($this->underline)
                        $s.=' ' . $this->_dounderline($this->x + $dx, $this->y + .5 * $h + .3 * $this->FontSize, $txt);
                    if ($this->ColorFlag)
                        $s.=' Q ';
                    if ($link)
                        $this->Link($this->x + $dx, $this->y + .5 * $h - .5 * $this->FontSize, $w_txt, $this->FontSize, $link);
                }
            }
            else
            { // Single line
                $w_txt = $this->GetStringWidth($txt);
                $Tz = 100;
                if ($w_txt > $w - 2 * $this->cMargin)
                { // Need compression
                    $Tz = ($w - 2 * $this->cMargin) / $w_txt * 100;
                    $w_txt = $w - 2 * $this->cMargin;
                }
                if ($align == 'R')
                    $dx = $w - $w_txt - $this->cMargin;
                elseif ($align == 'C')
                    $dx = ($w - $w_txt) / 2;
                else
                    $dx = $this->cMargin;
                $txt = str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $txt)));
                if ($this->ColorFlag)
                    $s.='q ' . $this->TextColor . ' ';
                $s.=sprintf('q BT %.2f %.2f Td %.2f Tz (%s) Tj ET Q ', ($this->x + $dx) * $k, ($this->h - ($this->y + .5 * $h + .3 * $this->FontSize)) * $k, $Tz, $txt);
                if ($this->underline)
                    $s.=' ' . $this->_dounderline($this->x + $dx, $this->y + .5 * $h + .3 * $this->FontSize, $txt);
                if ($this->ColorFlag)
                    $s.=' Q ';
                if ($link)
                    $this->Link($this->x + $dx, $this->y + .5 * $h - .5 * $this->FontSize, $w_txt, $this->FontSize, $link);
            }
        }
        // end change Cell function 12.08.2003
        if ($s)
            $this->_out($s);
        $this->lasth = $h;
        if ($ln > 0)
        {
            //Go to next line
            $this->y+=$h;
            if ($ln == 1)
                $this->x = $this->lMargin;
        }
        else
            $this->x+=$w;
    }

//End of the Cell Function    
}
