<?php

class ImageContent extends ReportContent
{
    public $image;
    public $width;
    public $height;
    public $x;
    public $y;
    
    public function __construct($image, $width, $height, $x=null, $y=null)
    {
        $this->image = $image;
        $this->width = $width;
        $this->height = $height;
        $this->x=$x;
        $this->y=$y;
    }
    
    public function getType()
    {
        return 'image';
    }
}
