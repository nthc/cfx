<?php
class HTMLReport extends Report
{
    public $htmlHeaders;
    private $numColumns;
    private $widthsSet;
    //private $widths = array();

    public function setOptions($options)
    {
    }

    public function output($file = null)
    {
        if($file != null) ob_start();
        if($this->htmlHeaders)
        {
            header('Content-type: text/html');
            print "<html>
                    <head>
                        <title>Report</title>
                    </head>
                <body>";
        }

        foreach($this->contents as $content)
        {

            if(!is_object($content)) continue;
            if($tableOpen === true && $content->getType() != 'table')
            {
                $tableOpen = false;
                print "</tbody></table>";
            }
            switch($content->getType())
            {
            case "logo":
                print "<table style='width:100%;margin-bottom:20px'>
                    <tr>
                        <td>" 
                            . ($this->htmlHeaders ? "<img src='/{$content->image}' style='width:30px;height:30px'/>" : '') .
                            "<span style='font-size:xx-large;font-weight:bold;padding:5px'>{$content->title}</span>
                        </td>
                        <td style = 'font-size:x-small;text-align:right'>";
                foreach($content->address as $address)
                {
                    print $address . "<br/>";
                }
                print "</td>
                    </tr>
                </table>";
                break;
                
            case "text":
                $style = "padding:0px;margin:0px;";
                if(isset($content->style["font"])) $style .= "font-family:{$content->style["font"]};"; else $style .= "font-family:Helvetica;";
                if(isset($content->style["size"])) $style .= "font-size:{$content->style["size"]}pt;";
                if(isset($content->style["top_margin"])) $style .= "margin-top:{$content->style["top_margin"]}px;";
                if(isset($content->style["bottom_margin"])) $style .= "margin-bottom:{$content->style["bottom_margin"]}px;";
                
                $style .= $content->style["bold"]?"font-weight:bold;":"";
                $style .= $content->style["underline"]?"text-decoration:underline;":"";
                $style .= $content->style["align"] == 'R' ? "text-align:right":"";

                print "<div style='$style'>".$content->getText()."</div>";
                break;

            case "table":
                $headerFill = implode(',', $content->style['header:background']);
                $headerBorder = implode(',', $content->style['header:border']);
                $headerText = implode(',', $content->style['header:text']);
                $bodyFill = implode(',', $content->style['body:background']);
                $bodyStripe = implode(',', $content->style['body:stripe']);
                $bodyBorder = implode(',', $content->style['body:border']);
                $bodyText = implode(',', $content->style['body:text']);
                
                if($content->style["totalsBox"])
                {
                    $totals = $content->getData();
                    print "<tr>";
                    for($i = 0; $i<$this->numColumns; $i++)
                    {
                        if($i == 0)
                        {
                            print "<td style='padding:3px; padding-top:10px; border:1px solid rgb($bodyBorder);font-size:8pt;font-family:helvetica;'><b>{$totals[$i]}</b></td>";
                        }
                        else
                        {
                            print "<td style='padding:3px; padding-top:10px;border:1px solid rgb($bodyBorder);font-size:8pt;font-family:helvetica;' align='right'><b>" . (is_numeric($totals[$i]) ? number_format($totals[$i], 2, '.', ',') : "") . "</b></td>";
                        }
                    }
                    print "</tr>";
                }
                else
                {
                    $totalWidths = array_sum($content->data_params["widths"]);
                    foreach($content->data_params["widths"] as $i=>$width)
                    {
                        $this->widths[$i] = round($width / $totalWidths * 100);
                    }
                    
                    
                    print "<table style='border-collapse:collapse' width='100%'><thead style='background-color:rgb($headerFill);color:rgb($headerText); font-size:8pt;font-weight:bold;'><tr>";
                    $tableOpen = true;
                    $headers = $content->getHeaders();
                    $this->numColumns = count($headers);
                    foreach($headers as $key=>$header)
                    {
                        $headers[$key] = str_replace("\\n","<br/>",$header);
                        print "<td style = 'padding:3px;border:1px solid rgb($headerBorder);font-size:8pt;font-family:helvetica'>{$headers[$key]}</td>";
                    }
                    
                    print "</tr></thead><tbody>";
                    $fill = false;
                    $data = $content->getData();
                    $keys = array_keys(reset($data));
                    
                    foreach($data as $row)
                    {
                    	print "<tr " . ($fill ? "style='background-color:rgb($bodyStripe)'" : "") . " >";
                        foreach($headers as $i=>$header)
                        {
                        	$key = $keys[$i];
                            $row[$key] = str_replace("\n","<br/>",trim($row[$key]));
                            print "<td style='padding:3px;border:1px solid rgb($bodyBorder);font-size:8pt;font-family:helvetica;'" . 
                                ($content->data_params["type"][$i] == 'number' || $content->data_params["type"][$i] == 'double' ? " align='right'":"") . 
                            ">{$row[$key]}</td>";
                        }
                        print "</tr>";
                        $fill = !$fill;
                    }

                    if($content->style["autoTotalsBox"])
                    {
                        $totals = $content->getTotals();
                        print "<tr>";
                        foreach($headers as $index => $header)
                        {
                            if($index == 0)
                            {
                                print "<td style='padding:3px;border:1px solid rgb(180,200,180);font-size:8pt;font-family:helvetica;'><b>Totals</b></td>";
                            }
                            else
                            {
                                print "<td style='padding:3px;border:1px solid rgb(180,200,180);font-size:8pt;font-family:helvetica;' align='right'><b>" . (is_numeric($totals[$index]) ? Common::currency($totals[$index]) : "") . "</b></td>";
                            }
                        }
                        print "</tr>";
                    }

                    //print "</tbody></table>";
                }
                
                break;
            }
        }
        if($file != null) 
        {
            $output = ob_get_clean();
            file_put_contents($file, $output);
        }
        else 
        {
            die();
        }
    }
}
