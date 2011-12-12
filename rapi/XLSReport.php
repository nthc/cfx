<?php

require_once "Spreadsheet/Excel/Writer.php";

class XLSReport extends Report
{
    private $widthsSet;
    private $numColumns;

    public function output($file = null)
    {
    	ob_clean();
        $spreadsheet = new Spreadsheet_Excel_Writer();
        $spreadsheet->send("report.xls");
        $worksheet =& $spreadsheet->addWorkSheet("Report");
        $worksheet->setLandscape();
        $worksheet->hideGridlines();
        $worksheet->setPaper(9);
        $worksheet->setMargins(0.25);
        $worksheet->setFooter("Generated on ".date("jS F, Y @ g:i:s A")." by ".$_SESSION["user_lastname"]." ".$_SESSION["user_firstname"]);
        $row = 0;
        foreach($this->contents as $content)
        {
            if(!is_object($content)) continue;
            switch($content->getType())
            {
                case "text":
                    $format = &$spreadsheet->addFormat();
                    if($row!=0) $row++;
                    $style = "padding:0px;margin:0px;";
                    if(isset($content->style["font"])) $format->setFontFamily($content->style["font"]);
                    if(isset($content->style["size"])) $format->setSize($content->style["size"]);
                    if(isset($content->style["bold"])) $format->setBold(700);

                    $worksheet->write($row,0,$content->getText(),$format);
                    break;

                case "table":
                    if($content->style["totalsBox"])
                    {
                        $format = &$spreadsheet->addFormat();
                        $format->setFontFamily("Helvetica");
                        $format->setSize(8);
                        $spreadsheet->setCustomColor(13,180,200,180);
                        $format->setBorderColor(13);
                        $format->setBottom(2);
                        $format->setBold(700);

                        $totals = $content->getData();
                        for($i = 0; $i<$this->numColumns; $i++)
                        {
                            $worksheet->write($row,$i,$totals[$i],$format);//,$format);
                        }
                    }
                    else
                    {

                        if(!$this->widthsSet && isset($content->data_params["widths"]))
                        {
                            foreach($content->data_params["widths"] as $i=>$width)
                            {
                                $worksheet->setColumn($i, $i, $width * 1.5);
                            }
                            $this->widthsSet = true;
                        }

                        $headers = $content->getHeaders();
                        $format = &$spreadsheet->addFormat();
                        $format->setFontFamily("Helvetica");
                        $format->setSize(8);
                        $spreadsheet->setCustomColor(12,102,128,102);
                        $format->setFgColor(12);
                        $format->setColor("white");
                        $format->setBold(700);

                        $this->numColumns = count($headers);

                        foreach($headers as $col=>$header)
                        {
                            $worksheet->write($row,$col,str_replace("\\n","\n",$header),$format);
                        }

                        $format = &$spreadsheet->addFormat();
                        $format->setFontFamily("Helvetica");
                        $format->setSize(8);
                        $spreadsheet->setCustomColor(13,180,200,180);
                        $format->setBorderColor(13);
                        $format->setBorder(1);

                        foreach($content->getData() as $rowData)
                        {
                            $row++;
                            $col = 0;
                            foreach($rowData as $field)
                            {
                                $worksheet->write($row,$col,trim($field),$format);
                                $col++;
                            }
                        }
                    }
                    break;
            }
            $row++;
        }
        $spreadsheet->close();
    }
}

?>
