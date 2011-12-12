<?php
/**
 * Utility class which provides services such as
 */

require SOFTWARE_HOME . "app/config.php";

/**
 * A class for scaling and caching images for display. Most meth
 * @ingroup Utilities
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
     * Caches an image and stores it in <tt>/app/cache/images/XXXXXX.cachew.XXX.jpeg</tt>.
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
        $src = "/app/cache/images/$uuid.cachew.$width.jpeg";
        if(!is_file($src) || (filectime($file)>filectime($src)))
        {
            ImageCache::resize_image($file,SOFTWARE_HOME . $src,$width, 0,$tag);
        }
        return $src;
    }

    /**
     * Caches an image and stores it in <tt>/app/cache/images/XXXXXX.cacheh.XXX.jpeg</tt>.
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
        $src = "/app/cache/images/$uuid.cacheh.$height.jpeg";
        if(!is_file($src) || (filectime($file)>filectime($src)))
        {
            ImageCache::resize_image($file,SOFTWARE_HOME . $src,0, $height);
        }
        return $src;
    }

    /**
     * A smart thumbnailing function. Generates thumbnail images without
     * distorting the output of the final image.
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
        $src = SOFTWARE_HOME . "app/cache/images/$uuid.thumb.$width.$height.jpeg";

        if(!is_file($src) || (filectime($file)>filectime($src)))
        {
            $im = imagecreatefromjpeg($file);
            $i_width = imagesx($im);
            $i_height = imagesy($im);
            imagedestroy($im);
            $tempImage = uniqid() . ".jpeg";

            if($width>$height)
            {
                //if($i_width>$i_height) ImageCache::resize_image($file,"cache/images/$tempImage",$width,0);
                //else ImageCache::resize_image($file,"cache/images/$tempImage",0,$height);
                ImageCache::resize_image($file,SOFTWARE_HOME . "app/cache/images/$tempImage",$width,0);
                ImageCache::crop_image(SOFTWARE_HOME . "app/cache/images/$tempImage",$src,$width,$height,$head);
            }
            else if($height>$width)
            {
                //if($i_width>$i_height) ImageCache::resize_image($file,"cache/images/$tempImage",0,$height);
                //else ImageCache::resize_image($file,"cache/images/$tempImage",$width,0);
                ImageCache::resize_image($file,SOFTWARE_HOME . "app/cache/images/$tempImage",$height,0);
                ImageCache::crop_image(SOFTWARE_HOME . "app/cache/images/$tempImage",$src,$width,$height,$head);
            }
            else
            {
                if($i_width>$i_height) ImageCache::resize_image($file,SOFTWARE_HOME . "app/cache/images/$tempImage",0,$height);
                else ImageCache::resize_image($file,SOFTWARE_HOME . "app/cache/images/$tempImage",$width,0);
                ImageCache::crop_image(SOFTWARE_HOME . "app/cache/images/$tempImage",$src,$width,$height,$head);
            }
            unlink(SOFTWARE_HOME . "app/cache/images/$tempImage");
        }
        return $src;
    }
}
