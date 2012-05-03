<?php
class CSVReport extends Report
{
    public function __construct()
    {
        //parent::__construct();
    }

    public function output($file = null)
    {
        header("Content-Type: text/csv");
        header('Content-Disposition: attachment; filename="report.csv"');
        header('Content-Transfer-Encoding: binary');

        foreach($this->contents as $content)
        {
            switch($content->getType())
            {
            case "table":
                //$pdf->table($content->getHeaders(),$content->getData());
                print '"'.implode('","',$content->getHeaders()).'"'."\n";
                foreach($content->getData() as $data)
                {
                    print '"'.implode('","',$data).'"'."\n";
                }
                break;
            }
        }
        die();
    }
}