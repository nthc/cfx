<?php
/*
 * WYF Framework
 * Copyright (c) 2011 James Ekow Abaka Ainooson
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

require SOFTWARE_HOME . "app/config.php";

/**
 * A class for scaling and caching images for display. Most meth
 * @package wyf.utils
 */
class ImageCache
{
    /**
     * Resizes an image
     * @param string $src Path to the image to be resized
     * @param string $dest Path to sore the resized image
     * @param integer $width New width of the image
     * @param integer $height New Height of the image
     * @param string $tag A text tag to place at the bottom of the image
     */
    public static function resize_image($src,$dest,$width,$height,$tag='')
    {
        $im = imagecreatefromjpeg($src);
        $o_width = imagesx($im);
        $o_height = imagesy($im);

        $aspect = $o_width / $o_height;

        if($width<=0)
        {
            $width = $aspect * $height;
        }
        else if($height<=0)
        {
            $height = $width / $aspect;
        }

        $dest_im = imagecreatetruecolor($width, $height);
        imagecopyresampled($dest_im, $im, 0,0,0,0,$width,$height,$o_width,$o_height);

        @imagejpeg($dest_im, $dest, 100);
        imagedestroy($im);
        imagedestroy($dest_im);
    }

    /**
     * Crops an image. This function crops by fitting the image into the center
     * of a new cropping area. If the cropping area is smaller than the image
     * the image is scaled to fit.
     *
     * @param string $src The path to the source image
     * @param string $dest The path to the destination image
     * @param int $width The cropping width
     * @param int $height The cropping height
     * @param boolean $head Place the cropping area on top of the image
     */
    public static function crop_image($src, $dest, $width, $height,$head=false)
    {
        $im = imagecreatefromjpeg($src);
        $o_width = imagesx($im);
        $o_height = imagesy($im);
        if($head==false) $top = ($o_height/2)-($height/2); else $top=0;
        $left = ($o_width/2)-($width/2);
        $im2 = imagecreatetruecolor ($width, $height);

        imagecopyresampled($im2,$im,0,0,$left,$top,$width,$height,$width,$height);
        imagejpeg($im2, $dest, 100);
        imagedestroy($im);
        imagedestroy($im2);
    }

    /**
     * Caches an image and stores it in <tt>/app/temp/XXXXXX.cachew.XXX.jpeg</tt>.
     * This function gives preference to the width of the image. It would always
     * guarantee that the width of the outputed cached image is equal to the
     * width passed to the function.
     *
     * @param string $file The image file
     * @param integer $width The width of the image
     * @return string
     */
    public static function width($file,$width)
    {
        if(!is_file($file)) return;
        $uuid = md5($file);
        $src = "/app/temp/$uuid.cachew.$width.jpeg";
        if(!is_file($src) || (filectime($file)>filectime($src)))
        {
            ImageCache::resize_image($file,SOFTWARE_HOME . $src,$width, 0,$tag);
        }
        return $src;
    }

    /**
     * Caches an image and stores it in <tt>/app/temp/XXXXXX.cacheh.XXX.jpeg</tt>.
     * This function gives preference to the width of the image. It would always
     * guarantee that the height of the outputed cached image is equal to the
     * height passed to the function.
     *
     * @param string $file The image file
     * @param integer $height The height of the image
     * @return string
     */
    public static function height($file,$height)
    {
        if(!is_file($file)) return;
        $uuid = md5($file);
        $src = "/app/temp/$uuid.cacheh.$height.jpeg";
        if(!is_file($src) || (filectime($file)>filectime($src)))
        {
            ImageCache::resize_image($file,SOFTWARE_HOME . $src,0, $height);
        }
        return $src;
    }

    /**
     * A smart thumbnailing function. Generates thumbnail images without
     * distorting the output of the final image.
     * 
     * @param string $file
     * @param string $width
     * @param string $height
     * @param string $head
     * @return string
     */
    public static function thumbnail($file,$width,$height,$head=false)
    {
        if(!is_file($file)) return;
        $uuid = md5($file);
        $src = SOFTWARE_HOME . "app/temp/$uuid.thumb.$width.$height.jpeg";

        if(!is_file($src) || (filectime($file)>filectime($src)))
        {
            $im = imagecreatefromjpeg($file);
            $i_width = imagesx($im);
            $i_height = imagesy($im);
            imagedestroy($im);
            $tempImage = uniqid() . ".jpeg";

            if($width>$height)
            {
                ImageCache::resize_image($file,SOFTWARE_HOME . "app/temp/$tempImage",$width,0);
                ImageCache::crop_image(SOFTWARE_HOME . "app/temp/$tempImage",$src,$width,$height,$head);
            }
            else if($height>$width)
            {
                ImageCache::resize_image($file,SOFTWARE_HOME . "app/temp/$tempImage",$height,0);
                ImageCache::crop_image(SOFTWARE_HOME . "app/temp/$tempImage",$src,$width,$height,$head);
            }
            else
            {
                if($i_width>$i_height) ImageCache::resize_image($file,SOFTWARE_HOME . "app/temp/$tempImage",0,$height);
                else ImageCache::resize_image($file,SOFTWARE_HOME . "app/temp/$tempImage",$width,0);
                ImageCache::crop_image(SOFTWARE_HOME . "app/temp/$tempImage",$src,$width,$height,$head);
            }
            unlink(SOFTWARE_HOME . "app/temp/$tempImage");
        }
        return $src;
    }
}
