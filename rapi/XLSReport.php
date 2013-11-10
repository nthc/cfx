<?php

class XLSReport extends Report
{
    private $widthsSet;
    private $numColumns;
    
    public function __construct() 
    {
        add_include_path('lib/rapi/PHPExcel/Classes');
    }

    public function output($file = null)
    {
    	ob_clean();
        $spreadsheet = new PHPExcel($file);
        
        $worksheet = $spreadsheet->getActiveSheet();
        $row = 1;
        foreach($this->contents as $content)
        {
            if(!is_object($content)) continue;
            switch($content->getType())
            {
                case "text":
                    $worksheet->setCellValueByColumnAndRow(0, $row, $content->getText());
                    break;

                case "table":
                    if($content->style["totalsBox"])
                    {
                        $totals = $content->getData();
                        for($i = 0; $i<$this->numColumns; $i++)
                        {
                            $worksheet->setCellValueByColumnAndRow($row,$i,$totals[$i]);
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

                        $headers = $content->getHeaders();
                        /*@$format = &$spreadsheet->addFormat();
                        $format->setFontFamily("Helvetica");
                        $format->setSize(12);
                        $spreadsheet->setCustomColor(12,102,128,102);
                        $format->setFgColor(12);
                        $format->setColor("white");
                        $format->setBold(700);*/

                        $this->numColumns = count($headers);

                        foreach($headers as $col=>$header)
                        {
                            $worksheet->setCellValueByColumnAndRow($row,$col,str_replace("\\n","\n",$header));
                        }


                        foreach($content->getData() as $rowData)
                        {
                            $row++;
                            $col = 0;
                            foreach($rowData as $field)
                            {
                                switch($content->data_params["type"][$col])
                                {
                                     case "number":
                                         $field = $field === null || $field == "" ? "0" : round($field, 0);
                                         break;
                                     case "double":
                                         $field = $field === null || $field == "" ? "0.00" : round($field, 2);
                                         break;
                                     case "right_align":
                                         //$align = "R";
                                         break;
                                 }
                                $worksheet->setCellValueByColumnAndRow($row,$col,trim($field));
                                $col++;
                            }
                        }
                    }
                    break;
            }
            $row++;
        }
        
        $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
        $writer->save('app/temp/report.xlsx');

    }
}

