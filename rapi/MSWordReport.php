<?php
class MSWordReport extends HTMLReport
{
    public function output()
    {
        header("Content-type: application/vnd.ms-word");
        header("Content-Disposition: attachment;Filename=document_name.doc");
        parent::output();
    }
}
