<?php
/**
 * Renders reports to CSV Tables. Ideal for writing data exporting classes. This
 * class is also used by the ReportController classes as a means to generate CSV
 * output.
 */
class CSVReport extends Report
{
    /**
     * The filename for the output file.
     * @var string
     */
    private $downloadFileName = 'report.csv';
    
    /**
     * Set a filename for the file which would be downloaded or generated when
     * this report class generates its output.
     * @param type $downloadFileName The filename for the output file.
     */
    public function setDownloadFileName($downloadFileName)
    {
        $this->downloadFileName = $downloadFileName;
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
            header("Content-Disposition: attachment; filename=\"{$this->downloadFileName}\"");
            header('Content-Transfer-Encoding: binary');
            echo $csv;
        }
        else
        {
            file_put_contents($file, $csv);
        }
    }
}
