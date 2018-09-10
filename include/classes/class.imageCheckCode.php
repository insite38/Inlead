<?php

class imageCheckCode {	private $return	= array();
	private $session = array();
	private $charsToGen = "12346789qwertyupadfghkzxcvbnm";
	private $usingFont	= "checkCodeFont.ttf";
	private $code		= "";

	private $bgColor	= "#FFFFFF";
	private $textColor	= "#000000";

	private $stringLength = 5;

	// Не изменять
	private $bgRed		= 155;
	private $bgGreen	= 155;
	private $bgBlue		= 155;


	public function create() {		$width 	= 0;
		$height = 0;
		$chars 	= array();
		set_time_limit(4);
		$fontFullPath =dirname(__FILE__)."/".$this->usingFont;
		if (!file_exists($fontFullPath)) {			exit("Fatal error: can't fined font ".$fontFullPath);		}

        for ($i = 0; $i < $this->stringLength; ++$i) {        	$offset = mt_rand(0, strlen($this->charsToGen)-1);
        	$this->code .= strtoupper(substr($this->charsToGen, $offset, 1));        }

		for ($i = 0; $i < strlen($this->code); ++$i) {
			$fontSize	= mt_rand(18, 20);
			$angle		= mt_rand(-10, 10);
			$box		= imagettfbbox ($fontSize, $angle, $fontFullPath, $this->code[$i]);

    		$heightOffset	= max($box[1], $box[3], $box[5], $box[7]) - min($box[1], $box[3], $box[5], $box[7]);
			$widthOffset	= max($box[0], $box[2], $box[4], $box[6]) - min($box[0], $box[2], $box[4], $box[6]) + 1;

			$chars[$i] = array (
			 						'size'		=> $fontSize,
			 						'angle'		=> $angle,
			 						'width'		=> $widthOffset + 2,
			 						'height' 	=> $heightOffset);

			$width += $widthOffset + 2;

			if ($height < $heightOffset - 5) {				$height = $heightOffset + 5;
			}
		}

		$im = imagecreate($width + 4, $height + 4);

		$backgroundColor	= imagecolorallocate($im, $this->bgRed, $this->bgGreen, $this->bgBlue);

		imagefill($im, 0, 0, $backgroundColor);

		for ($i = 0; $i < strlen($this->code) *100; $i++)   {
			imagesetpixel($im, mt_rand(0, $width + 4), mt_rand(0, $height + 4), 255);
		}

		$x = 4;
		$xStep = $width / strlen($this->code);
		for ($i = 0; $i < strlen($this->code); ++$i) {			$fontSize = 12;
			imagettftext(	$im,
							$chars[$i]['size'],
							$chars[$i]['angle'],
							$x,
							$height - 3 + mt_rand (-3, 3),
							imagecolorallocate($im, mt_rand(0,120), mt_rand(0,120), mt_rand(0,120)),
							$fontFullPath,
							$this->code[$i]
							);
			$x = $x + $xStep;
		}		$this->session['imageCheckCode'] = $this->code;


		header('Content-type: image/png');
		imagepng($im);
		imagedestroy($im);
	}

	function __construct() {		global $_SESSION;
		$this->session = &$_SESSION;

		if (preg_match("/^\#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i", $this->bgColor, $match)) {
			$this->bgRed		= hexdec($match[1]);
			$this->bgGreen		= hexdec($match[2]);
			$this->bgBlue		= hexdec($match[3]);
		}
		return true;	}


}


?>
