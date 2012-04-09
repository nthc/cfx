<?php

class ImageContent extends ReportContent
{
    public $image;
    public $width;
    public $height;
    
    public function __construct($image, $width, $height)
    {
        $this->image = $image;
        $this->width = $width;
        $this->height = $height;
    }
    
    public function getType()
    {
        return 'image';
    }
}
