<?php
class CSVReport extends Report
{
    public function __construct()
    {
        //parent::__construct();
    }

    public function output($file = null)
    {
        foreach($this->contents as $content)
        {
            switch($content->getType())
            {
            case "table":
                //$pdf->table($content->getHeaders(),$content->getData());
                $csv .= '"'.implode('","',$content->getHeaders()).'"'."\n";
                foreach($content->getData() as $data)
                {
                    $csv .= '"'.implode('","',$data).'"'."\n";
                }
                break;
            }
        }
        if($file == '')
        {
            header("Content-Type: text/csv");
            header('Content-Disposition: attachment; filename="report.csv"');
            header('Content-Transfer-Encoding: binary');
            echo $csv;
        }
        else
        {
            file_put_contents($file, $csv);
        }
    }
}
