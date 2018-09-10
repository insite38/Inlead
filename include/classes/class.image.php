<?php

class image { 

    public $waterMark = '';
    public $error = '';
    public $dest = '';
    public $quality = 85;
    public $coroner_size = 20;
    public $coroners = array(true, true, true, true);
    private $imgInfo = array();
    private $imgExt = '.png';
    private $marignWtr = 5;
    private $transparency = 100;
    private $overThumb = 50;
    private $destImageHandle;
	
    protected static $_instance;
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    private function __construct(){}
    private function __clone(){}
    private function __wakeup(){}

    private function checkWritible($file, $folder) {
        if (!file_exists($file)) {
            $this->error = "Can't find file " . $file . ", may be uploading error";
            return false;
        }

        if (!is_readable($file)) {
            $this->error = "Can't access to " . $file . ", please set permissions";
            return false;
        }

        if (!file_exists($folder) || !is_dir($folder)) {
            $this->error = "Can't fined dir " . $folder . ", please create it";
            return false;
        }

        if (!is_writeable($folder)) {
            $this->error = "Can't write resized image to " . $folder . ", please set permission";
            return false;
        }

        return true;
    }

    private function getImageExt($imageData) {
        switch ($imageData) {
            case 'image/gif':
            case 'image/x-png':
            case 'image/png':
                $result = '.png';
                break;
            default:
                $result = '.jpg';
                break;
        }

        return $result;
    }

    public function genFileName() {
        $buf = "qwertyuiopasdfhjklzxcvbnm1234567890";
        $t = time();

        $result = '';
        $inc = 0;

        while ($inc <= 6) {
            $result .= substr($buf, mt_rand(1, strlen($buf)), 1);
            $inc++;
        }

        return $t . '_' . $result;
    }

    private function yiq($r, $g, $b) {
        return (($r * 0.299) + ($g * 0.587) + ($b * 0.114));
    }


    // простая загрузка фоток
    public function moveUploadedFile($src, $dstDir = "upload/", $type = "photo"){

        if (!file_exists($src)) {
            $this->error = "При загрузке файла возникла ошибка";
            return false;
        }

        if (!is_readable($src)) {
            $this->error = "Не достаточно прав для загруки файлов в указанную папку";
            return false;
        }

        if (!file_exists($dstDir) || !is_dir($dstDir)) {
            $this->error = "Нет доступа до папки " . $dstDir . ", создайте её";
            return false;
        }

        if (!is_writeable($dstDir)) {
            $this->error = "Не достаточно прав для загруки файлов в указанную папку";
            return false;
        }

        $im = new imagick($src);
        $imageprops = $im->getImageGeometry();
        $srcWidth = $imageprops['width'];
        $srcHeight = $imageprops['height'];

        // для загрузки логотипа
        if($type == 'logo' && $srcHeight > 200){
            $this->error = "Высота логотипа привышает 200px, сделайте по размеру и загрузите еще раз";
            return false;
        }

        // для загрузки превью
        if($type == 'photo' && $srcWidth > 400){
            $this->error = "Высота логотипа привышает 400px, сделайте по размеру и загрузите еще раз";
            return false;
        }

        // для загрузки большого изображения
        if($type == 'PhotoBig' && $srcWidth > 1200){
            $this->error = "Высота логотипа привышает 1200px, сделайте по размеру и загрузите еще раз";
            return false;
        }

        $destReturn = (!empty($this->dest) ? $this->dest : date("ymd") . "_" . $this->genRandString(10) . $this->imgExt);
        if((bool) move_uploaded_file($src, $dstDir.$destReturn) === false){
            return false;
        }

        return basename($destReturn);
    }


    // ресайзер
    public function Iresize($src, $dstDir = "upload/", $maxWidth = 0, $maxHeight = 0){

        if (!file_exists($src)) {
            $this->error = "Can't fined file " . $src . ", may be uploading error";
            return false;
        }

        if (!is_readable($src)) {
            $this->error = "Can't access to " . $src . ", please set permissions";
            return false;
        }

        if (!file_exists($dstDir) || !is_dir($dstDir)) {
            $this->error = "Can't fined dir " . $dstDir . ", please create it";
            return false;
        }

        if (!is_writeable($dstDir)) {
            $this->error = "Can't write resized image to " . $dstDir . ", please set permission";
            return false;
        }

        $im = new imagick($src);

        $imageprops = $im->getImageGeometry();
        $srcWidth = $imageprops['width'];
        $srcHeight = $imageprops['height'];

        if ($maxWidth == 0 && $maxHeight == 0) {
            $rotation = 1;
            //$km ='1. ';
        } else {
            if ($maxWidth > 0 && $maxHeight > 0) {
                if (($maxWidth / $srcWidth) < ($maxHeight / $srcHeight)) {
                    $rotation = $maxWidth / $srcWidth;
                    //$km ='2. ';
                } else {
                    $rotation = $maxHeight / $srcHeight;
                    //$km ='3. ';
                }
            } else {
                if ($maxWidth == 0) {
                    $rotation = $maxHeight / $srcHeight;
                    //$km ='4. ';
                } else {
                    $rotation = $maxWidth / $srcWidth;
                    //$km ='5. ';
                }
            }
        }

        $tmpw = ceil($srcWidth * $rotation);
        $tmph = ceil($srcHeight * $rotation);
        $im->resizeImage($tmpw,$tmph, imagick::FILTER_LANCZOS, 0.9, false);
        $im->cropImage ($tmpw,$tmph,0,0);

        $destReturn = (!empty($this->dest) ? $this->dest : $dstDir . date("ymd") . "_" . $this->genRandString(10) . $this->imgExt);
        if ((bool) $im->writeImage( $_SERVER['DOCUMENT_ROOT'].'/'.$destReturn ) === false) {
            return 'false';
        }

        return basename($destReturn);
    }

    public function resize($src, $dstDir = "upload/", $maxWidth = 0, $maxHeight = 0, $corners = array(false, false, false, false)) {
        //echo 'h='.$maxHeight.' w='.$maxWidth;
        if (!file_exists($src)) {
            $this->error = "Can't fined file " . $src . ", may be uploading error";
            return false;
        }

        if (!is_readable($src)) {
            $this->error = "Can't access to " . $src . ", please set permissions";
            return false;
        }

        if (!file_exists($dstDir) || !is_dir($dstDir)) {
            $this->error = "Can't fined dir " . $dstDir . ", please create it";
            return false;
        }

        if (!is_writeable($dstDir)) {
            $this->error = "Can't write resized image to " . $dstDir . ", please set permission";
            return false;
        }

        $this->imgInfo = getimagesize($src);
        if (!$this->imgInfo || $this->imgInfo[0] == 0 || $this->imgInfo[1] == 0 || $this->imgInfo[2] == 0) {
            $this->error = "You can upload only jpeg, png or gif files";
            return false;
        }

        $srcWidth = $this->imgInfo[0];
        $srcHeight = $this->imgInfo[1];

        switch ($this->imgInfo[2]) {
            case 1:
                $srcImgId = imagecreatefromgif($src);
                break;

            case 2:
                $srcImgId = imagecreatefromjpeg($src);
                break;

            case 3:
                $srcImgId = imagecreatefrompng($src);
                break;

            default:
                $this->error = "You can upload only jpeg, png or gif files";
                return false;
                break;
        }

        if ($maxWidth == 0 && $maxHeight == 0) {
            $rotation = 1;
            //$km ='1. ';
        } else {
            if ($maxWidth > 0 && $maxHeight > 0) {
                if (($maxWidth / $srcWidth) < ($maxHeight / $srcHeight)) {
                    $rotation = $maxWidth / $srcWidth;
                    //$km ='2. ';
                } else {
                    $rotation = $maxHeight / $srcHeight;
                    //$km ='3. ';
                }
            } else {
                if ($maxWidth == 0) {
                    $rotation = $maxHeight / $srcHeight;
                    //$km ='4. ';
                } else {
                    $rotation = $maxWidth / $srcWidth;
                    //$km ='5. ';
                }
            }
        }

        $tmpw = ceil($srcWidth * $rotation);
        $tmph = ceil($srcHeight * $rotation);
        //echo $km.$tmpw.' x '.$tmph.' '.$dstDir.'<br />';
        $dstImgId = imagecreatetruecolor($tmpw, $tmph);
        imagecopyresampled($dstImgId, $srcImgId, 0, 0, 0, 0, $tmpw, $tmph, $srcWidth, $srcHeight);

        // Adding waterMark
        /* if (!empty($this->waterMark) && file_exists($this->waterMark)) {
          $this->destImageHandle = $dstImgId;
          $this->addWaterMark();
          } */

        $destReturn = (!empty($this->dest) ? $this->dest : $dstDir . date("ymd") . "_" . $this->genRandString(10) . $this->imgExt);
        if ((bool) imagepng($dstImgId, $destReturn) === false) {
            return false;
        }
        return basename($destReturn);
    }
    
    
/**
 * ResizeEx - function resize image
 * 
 * @param string $src - source image filename
 * @param string $filename - dest filename
 * @param array $dest_image_size [$dest_folder , $width , $height]
 * @param bool $crop - [optional default false] crop or fullfill image
 * @param bool $round - [optional default false] round image #experimental
 * @param bool $gray - [optional default false] grayscale image #not work yet
 * 
 * @return boolean|string - filename or false if error is occure
 */
    public function resizeEx($src, $filename, $dest_image_size, $crop = false, $round = false, $gray = false) {
        /* $src - source file name, 
         * $filename - dest file name w/o ext,  
         * $dest_image_size - array[dest dir, width, height], 
         * $crop - bool cut or fullfil area, 
         * #not working yet# $round - bool round image, to setup what coroners round set $coroners[lt, rt, lb, rb] and $coroner_size 
         * $gray - bool b&w image
         */
        if (!$this->checkWritible($src, $dest_image_size[0]))
            return false;

        // Get the size and MIME type of the requested image
        $size = GetImageSize($src);
        $mime = $size['mime'];
         $dstDir = $dest_image_size[0];
        $maxWidth = $dest_image_size[1];
        $maxHeight = $dest_image_size[2];
        $width = $size[0];
        $height = $size[1];
        //$fName = $filename;
        $fName = $filename . $this->getImageExt($mime);

        // Make sure that the requested file is actually an image
        if (substr($mime, 0, 6) != 'image/') {
            $this->error = 'Error: requested file is not an accepted type: ' . $src;
            return false;
        }

        if ($crop) {
            $ratioComputed = $width / $height;
            $cropRatioComputed = $maxWidth / $maxHeight;
            //$offsetX = 0;
            //$offsetY = 0;

            if ($ratioComputed < $cropRatioComputed) { // Image is too tall so we will crop the bottom
                //$tnHeight = $height;
                $tnHeight = ceil($width / $cropRatioComputed);
                $tnWidth = $width;
                //$offsetY = ($origHeight - $height) / 2;
            } else { // Image is too wide so we will crop off the right side
                //$origWidth = $width;
                $tnHeight = $height;
                $tnWidth = ceil($height * $cropRatioComputed);
                //$offsetX = ($origWidth - $width) / 2;
            }
        } else {
            $xRatio = $maxWidth / $width;
            $yRatio = $maxHeight / $height;

            if ($xRatio * $height < $maxHeight) { // Resize the image based on width
                $tnHeight = ceil($xRatio * $height);
                $tnWidth = $maxWidth;
            } else {// Resize the image based on height
                $tnWidth = ceil($yRatio * $width);
                $tnHeight = $maxHeight;
            }
        }

        switch ($mime) {
            case 'image/gif':
                $srcImg = ImageCreateFromGif($src);
                $quality = round(10 - ($this->quality / 10)); // We are converting the GIF to a PNG and PNG needs a compression level of 0 (no compression) through 9
                break;

            case 'image/x-png':
            case 'image/png':
                $srcImg = ImageCreateFromPng($src);
                $quality = round(10 - ($this->quality / 10)); // PNG needs a compression level of 0 (no compression) through 9
                break;

            default:
                $srcImg = ImageCreateFromJpeg($src);
                $quality = $this->quality;
                break;
        }

        if ($crop) {
            $dstImg = imagecreatetruecolor($maxWidth, $maxHeight);
        } else {
            $dstImg = imagecreatetruecolor($tnWidth, $tnHeight);
        }

        if (in_array($mime, array('image/gif', 'image/png'))) {
            imagealphablending($dstImg, false);
            imagesavealpha($dstImg, true);
        }


        //ImageCopyResampled($dstImg, $srcImg, 0, 0, $offsetX, $offsetY, $maxWidth, $maxHeight, $width, $height); //center crop
       
        if ($crop) {
            ImageCopyResampled($dstImg, $srcImg, 0, 0, 0, 0, $maxWidth, $maxHeight, $tnWidth, $tnHeight); //crop
        } else {
            ImageCopyResampled($dstImg, $srcImg, 0, 0, 0, 0, $tnWidth, $tnHeight, $width, $height);  //resize
        }


        if (in_array($mime, array('image/gif', 'image/png', 'image/x-png'))) {
            imagePng($dstImg, $dstDir . $fName, $quality);
        } else {
            imageJpeg($dstImg, $dstDir . $fName, $quality);
        }

        /* grayscale image */
        if ($gray) {
            $dstImgGray = imagecreate($tnWidth, $tnHeight);

            if (in_array($mime, array('image/gif', 'image/png'))) {
                imagealphablending($dstImgGray, false);
                imagesavealpha($dstImgGray, true);
            }

            for ($c = 0; $c < 256; $c++) {
                $palette[$c] = imagecolorallocate($dstImgGray, $c, $c, $c);
            }

            for ($y = 0; $y < $tnHeight; $y++) {
                for ($x = 0; $x < $tnWidth; $x++) {
                    $rgb = imagecolorat($dstImg, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    $gs = $this->yiq($r, $g, $b);
                    imagesetpixel($dstImgGray, $x, $y, $palette[$gs]);
                }
            }

            $green = imagecolorallocate($dstImgGray, 0, 0, 0);
            imagecolortransparent($dstImgGray, $green);

            if (in_array($mime, array('image/gif', 'image/png', 'image/x-png'))) {
                imagePng($dstImgGray, $dstDir . 'g_' . $fName, $quality);
            } else {
                imageJpeg($dstImgGray, $dstDir . 'g_' . $fName, $quality);
            }
            ImageDestroy($dstImgGray);
        }


        ImageDestroy($dstImg);
        ImageDestroy($srcImg);

        return $fName;
    }

    private function createThumbnail($src, $file_name, $thumb_data, $crop = true) {
        $size = GetImageSize($src);
        $maxWidth = $thumb_data[1];
        $maxHeight = $thumb_data[2];
        $width = $size[0];
        $height = $size[1];

        if ($maxWidth >= $width && $maxHeight >= $height) {
            $data = file_get_contents($src);
            file_put_contents($thumb_data[0] . $file_name, $data);
        } else {
            $xRatio = $maxWidth / $width;
            $yRatio = $maxHeight / $height;

            if ($xRatio * $height < $maxHeight) { // Resize the image based on width
                $tnHeight = ceil($xRatio * $height);
                $tnWidth = $maxWidth;
            } else {// Resize the image based on height
                $tnWidth = ceil($yRatio * $width);
                $tnHeight = $maxHeight;
            }


            $ratioComputed = $width / $height;
            $cropRatioComputed = $maxWidth / $maxHeight;

            $offsetX = 0;
            $offsetY = 0;

            if ($ratioComputed < $cropRatioComputed) { // Image is too tall so we will crop the top and bottom
                $origHeight = $height;
                $height = $width / $cropRatioComputed;
                $offsetY = ($origHeight - $height) / 2;
            } else if ($ratioComputed > $cropRatioComputed) { // Image is too wide so we will crop off the left and right sides
                $origWidth = $width;
                $width = $height * $cropRatioComputed;
                $offsetX = ($origWidth - $width) / 2;
            }

            switch ($size['mime']) {
                case 'image/gif':
                    $srcImg = ImageCreateFromGif($src);
                    $quality = round(10 - ($this->quality / 10)); // We are converting the GIF to a PNG and PNG needs a compression level of 0 (no compression) through 9
                    break;

                case 'image/x-png':
                case 'image/png':
                    $srcImg = ImageCreateFromPng($src);
                    $quality = round(10 - ($this->quality / 10)); // PNG needs a compression level of 0 (no compression) through 9
                    break;

                default:
                    $srcImg = ImageCreateFromJpeg($src);
                    $quality = $this->quality;
                    break;
            }
            print $tnWidth . 'x' . $tnHeight . '<br>' . $maxWidth . 'x' . $maxHeight . '<br>' . (int) $crop;
            if ($crop) {
                $dstImg = imagecreatetruecolor($maxWidth, $maxHeight);
            } else {
                $dstImg = imagecreatetruecolor($tnWidth, $tnHeight);
            }

            if (in_array($size['mime'], array('image/gif', 'image/png'))) {
                imagealphablending($dstImg, false);
                imagesavealpha($dstImg, true);
            }

            //ImageCopyResampled($dstImg, $srcImg, 0, 0, $offsetX, $offsetY, $maxWidth, $maxHeight, $width, $height); //center crop
            if ($crop) {
                ImageCopyResampled($dstImg, $srcImg, 0, 0, 0, 0, $maxWidth, $maxHeight, $width, $height); //lt crop
            } else {
                ImageCopyResampled($dstImg, $srcImg, 0, 0, 0, 0, $tnWidth, $tnHeight, $size[0], $size[1]); //resize
            }

            if (in_array($size['mime'], array('image/gif', 'image/png', 'image/x-png'))) {
                imagePng($dstImg, $thumb_data[0] . $file_name, $quality);
            } else {
                imageJpeg($dstImg, $thumb_data[0] . $file_name, $quality);
            }

            ImageDestroy($dstImg);
            ImageDestroy($srcImg);
        }
        return true;
    }

    public function createGroup($src, $data) {
        $size = GetImageSize($src);
        $fName = $this->genFileName();
        $fName .= $this->getImageExt($size['mime']);
        $this->createThumbnail($src, $fName, $data);

        return $fName;
    }

    /*     * **************************************************************************** */

    public function roundCorners($imagepath, $img_dir, $topleft = false, $bottomleft = false, $topright = false, $bottomright = false) {
        $image = $imagepath;
        $corner = $this->coroner_size;

        file_exists($image) or die("No such file: " . $image); //check if image exists before processing

        $dim = getimagesize($image);

        switch ($dim[2]) {
            case 1:
                $new = imagecreatefromgif($imagepath);
                break;

            case 2:
                $new = imagecreatefromjpeg($imagepath);
                break;

            case 3:
                $new = imagecreatefrompng($imagepath);
                break;

            default:
                $this->error = "You can upload only jpeg, png or gif files";
                return false;
                break;
        }
//find colorcode
        $palette = imagecreatetruecolor($dim[0], $dim[1]);
        $found = false;
        while ($found == false) {
            $r = rand(0, 255);
            $g = rand(0, 255);
            $b = rand(0, 255);

            if (imagecolorexact($new, $r, $g, $b) != (-1)) {
                $colorcode = imagecolorallocate($palette, $r, $g, $b);
                $found = true;
            }
        }
        //$colorcode = imagecolorallocate($palette, 0, 0, 0);
        //draw corners
        if ($topleft == true) {
            imagearc($new, $corner - 1, $corner - 1, $corner * 2, $corner * 2, 180, 270, $colorcode);
            imagefilltoborder($new, 0, 0, $colorcode, $colorcode);
        }
        if ($topright == true) {
            imagearc($new, $dim[0] - $corner, $corner - 1, $corner * 2, $corner * 2, 270, 0, $colorcode);
            imagefilltoborder($new, $dim[0], 0, $colorcode, $colorcode);
        }
        if ($bottomleft == true) {
            imagearc($new, $corner - 1, $dim[1] - $corner, $corner * 2, $corner * 2, 90, 180, $colorcode);
            imagefilltoborder($new, 0, $dim[1], $colorcode, $colorcode);
        }
        if ($bottomright == true) {
            imagearc($new, $dim[0] - $corner, $dim[1] - $corner, $corner * 2, $corner * 2, 0, 90, $colorcode);
            imagefilltoborder($new, $dim[0], $dim[1], $colorcode, $colorcode);
        }

        imagecolortransparent($new, $colorcode); //make corners transparent
//display rounded image
        imagepng($new, $imagepath, 3);
        imagedestroy($new);
    }

    /*     * ********************************************************************* */

    function addWaterMark() {
        if (!is_readable($this->waterMark)) {
            return false;
        }


        $wtrInfo = getimagesize($this->waterMark);

        switch ($wtrInfo[2]) {
            case 1:
                $wtrImageId = imagecreatefromgif($this->waterMark);
                break;

            case 2:
                $wtrImageId = imagecreatefromjpeg($this->waterMark);
                break;

            case 3:
                $wtrImageId = imagecreatefrompng($this->waterMark);
                break;

            default:
                return false;
                break;
        }

        $wtrX = (bool) rand(0, 1) ? $this->marignWtr : (imagesx($this->destImageHandle) - imagesx($wtrImageId)) - $this->marignWtr;
        $wtrY = (bool) rand(0, 1) ? $this->marignWtr : (imagesy($this->destImageHandle) - imagesy($wtrImageId)) - $this->marignWtr;

        imagecopymerge($this->destImageHandle, $wtrImageId, $wtrX, $wtrY, 0, 0, $wtrInfo[0], $wtrInfo[1], $this->transparency);
        imagedestroy($wtrImageId);
        return true;
    }

    public function genRandString($len = 10) {
        $buf = "qwertyuiopasdfhjklzxcvbnm1234567890";

        while (@$incr <= $len) {
            @$return .= substr($buf, mt_rand(1, strlen($buf)), 1);
            @$incr++;
        }

        return $return;
    }

}

?>