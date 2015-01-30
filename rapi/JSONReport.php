<?php
/**
 * 
 * @author james
 */
class JSONReport extends Report
{   
    private $jsonOutput = array();
    
    public function output($file = null)
    {
        header("Content-Type: application/json");
        foreach($this->contents as $content)
        {
            if($content === null) continue;
            if(is_string($content)) continue;
            if($_REQUEST['tables_only'] && $content->getType() <> 'table') 
            {
                continue;
            }
            switch($content->getType())
            {
                case 'text':
                    $this->jsonOutput[] = array(
                        'type' => 'text',
                        'text' => $content->getText()
                    );
                    break;
                case "attributes":
                    $this->jsonOutput[] = array(
                        'type' => 'attribute',
                        'data' => $content->data
                    );
                    break;      
                case "table":
                    if($content->tag == '')
                    {
                        $this->jsonOutput[] = array(
                            'type' => 'table',
                            'data' => $content->getData(),
                            'headers' => $content->getHeaders()
                        );
                    }
                    else
                    {
                        $this->jsonOutput[$content->tag] = array(
                            'type' => 'table',
                            'data' => $content->getData(),
                            'headers' => $content->getHeaders()
                        );
                    }
                    break;
                case "image":
                    $this->jsonOutput[] = array(
                        'type' => 'image',
                        'image' => $content->image,
                    );
            }
        }
        
        echo json_encode($this->jsonOutput);
        die();
    }
}
