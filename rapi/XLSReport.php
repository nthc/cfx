<?php

class XLSReport extends Report
{
    private $widthsSet;
    private $numColumns;
    
    public function __construct() 
    {
        add_include_path('lib/rapi/PHPExcel/Classes');
    }
    
    private function convertColor($color)
    {
        return dechex($color[0]) . dechex($color[1]) . dechex($color[2]);
    }

    public function output($file = null)
    {
    	ob_clean();
        $spreadsheet = new PHPExcel($file);
        $spreadsheet->getProperties()
            ->setCreator('WYF PHP Framework')
            ->setTitle('Report');
        
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->getHeaderFooter()->setEvenFooter("Generated on ".date("jS F, Y @ g:i:s A")." by ".$_SESSION["user_lastname"]." ".$_SESSION["user_firstname"]);
        $worksheet->getHeaderFooter()->setOddFooter("Generated on ".date("jS F, Y @ g:i:s A")." by ".$_SESSION["user_lastname"]." ".$_SESSION["user_firstname"]);
        
        $row = 1;
        foreach($this->contents as $content)
        {
            if(!is_object($content)) continue;
            switch($content->getType())
            {
                case "text":
                    $worksheet->setCellValueByColumnAndRow(0, $row, $content->getText());
                    $worksheet->getStyleByColumnAndRow(0, $row)
                        ->getFont()
                            ->setBold(true)
                            ->setSize($content->style['size'])
                            ->setName($content->style['font']);
                    $worksheet->getRowDimension($row)->setRowHeight($content->style['size'] + $content->style['top_margin'] + $content->style['bottom_margin']);
                    break;

                case "table":
                    if($content->style["totalsBox"])
                    {
                        $totals = $content->getData();
                        for($i = 0; $i<$this->numColumns; $i++)
                        {
                            $worksheet->setCellValueByColumnAndRow($i,$row,$totals[$i]);
                            $worksheet->getStyleByColumnAndRow($i, $row)
                                ->getFont()
                                ->setBold(true);
                        }
                    }
                    else
                    {

                        /*if(!$this->widthsSet && isset($content->data_params["widths"]))
                        {
                            foreach($content->data_params["widths"] as $i=>$width)
                            {
                                $worksheet->setColumn($i, $i, $width * 1.5);
                            }
                            $this->widthsSet = true;
                        }*/
                        
                        $headBg = $this->convertColor($content->style['header:background']);
                        $headText = $this->convertColor($content->style['header:text']);
                        $bodyStripe = $this->convertColor($content->style['body:stripe']);

                        $headers = $content->getHeaders();
                        $this->numColumns = count($headers);

                        foreach($headers as $col=>$header)
                        {
                            $worksheet->setCellValueByColumnAndRow($col,$row,str_replace("\\n","\n",$header));
                            $worksheet->getStyleByColumnAndRow($col, $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                            $worksheet->getStyleByColumnAndRow($col, $row)->getFill()->getStartColor()->setRGB($headBg);
                            $worksheet->getStyleByColumnAndRow($col, $row)->getFont()->setBold(true)->getColor()->setRGB($headText);
                        }

                        $fill = false;

                        foreach($content->getData() as $rowData)
                        {
                            $row++;
                            $col = 0;
                            foreach($rowData as $field)
                            {
                                switch($content->data_params["type"][$col])
                                {
                                     case "number":
                                         $field = str_replace(",", "", $field);
                                         $field = $field === null || $field == "" ? "0" : round($field, 0);
                                         break;
                                     case "double":
                                         $field = str_replace(",", "", $field);
                                         $field = $field === null || $field == "" ? "0.00" : round($field, 2);
                                         break;
                                     case "right_align":
                                         //$align = "R";
                                         break;
                                 }
                                $worksheet->setCellValueByColumnAndRow($col, $row, trim($field));
                                if($fill)
                                {
                                    $worksheet->getStyleByColumnAndRow($col, $row)
                                        ->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                                    $worksheet->getStyleByColumnAndRow($col, $row)
                                        ->getFill()->getStartColor()->setARGB($bodyStripe);
                                }
                                $col++;
                            }
                            $fill = !$fill;
                        }
                    }
                    break;
            }
            $row++;
        }
        
        $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
        if($file == '')
        {
            $file = "app/temp/" . uniqid() . "_report.xlsx";
            $writer->save($file);
            Application::redirect("/$file");
        }
        else
        {
            $writer->save($file);
        }
   }
}

