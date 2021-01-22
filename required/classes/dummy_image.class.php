<?php

//include 'variables.php';

class dummy_image{

	
	public $debug = false, $output = true;
	
	private $imgWidth = 600, $imgHeight = 400, $angle = 0, $forceFontSize = 0,
			$maxWidth = 80, $imageDiagonal = false, $colorHex = "#DD4B39", $bgColorHex = "#ffffff", 
			$forceX = false, $forceY = false, 
			$font = 'ArialBlack.ttf', $dbgfont = 'CourierNew.ttf', $fontPath = "fonts/";
	
	
	public function __construct($fontPath = false){
		if($fontPath) $this->fontPath = $fontPath;
	}

	public function imageSize($w, $h){
		$w = abs($w);
		$h = abs($h);
		if($w > 0) $this->imgWidth = $w;
		if($h > 0) $this->imgHeight = $h;
	}

	public function x($x){
		$x = abs($x);
		$this->forceX = $x;
	}

	public function y($y){
		$y = abs($y);
		$this->forceY = $y;
	}
	
	public function rotate($angle){
		$angle = abs($angle);
		if($angle > 360) $angle = $angle - 360;
		$this->angle = $angle;
	}

	public function fontSize($size){				
		$this->$forceFontSize = abs($size);
	}
	
	public function maxSize($perc){
		$perc = abs($perc);
		if($perc > 100) $perc = 100;
		$this->$maxWidth = $perc;
	}

	public function diagonalOn(){
		$this->imageDiagonal = true;
	}

	public function diagonalOff(){
		$this->imageDiagonal = false;
	}

	public function color($hex){
		$this->colorHex = trim($hex);
	}

	public function bgColor($hex){
		$this->bgColorHex = trim($hex);
	}

	public function setFont($font){
		$font = trim($font);
		$exp = explode(".", $font);
		if(count($exp) == 1){
			$font .= ".ttf";
		}else{
			if(!end($exp) == 'ttf'){
				$font = $this->font;
			}
		}
		if(file_exists($this->fontPath.$font)){
			$this->font = $font;
		}		
	}

	public function setDbgFont($font){
		$font = trim($font);
		$exp = explode(".", $font);
		if(count($exp) == 1){
			$font .= ".ttf";
		}else{
			if(!end($exp) == 'ttf'){
				$font = $this->font;
			}
		}
		if(file_exists($this->fontPath.$font)){
			$this->dbgfont = $font;
		}		
	}
	
	public function draw($text = "FILE NOT FOUND!"){		
		
		// calculations

		if(empty($this->forceFontSize)){
			 $fontSize = 100;
			 $maximize = true;
		}else{
			 $fontSize = $this->forceFontSize;
			 $maximize = false;
		}
		
		$font = $this->fontPath.$this->font;
		$dbgfont = $this->fontPath.$this->dbgfont;


		$hypotenusaImage =  sqrt( pow($this->imgWidth, 2) + pow($this->imgHeight, 2) ); // diagonal of the image
		$sin = asin($this->imgHeight/$hypotenusaImage); // sinis value of the diagonal
		$diagonal = rad2deg($sin); // convert sinis value to degree 

		// set angle to diagonal value if flag is true
		if($this->imageDiagonal){	
			$this->angle = $diagonal; // set text angle to diagonal degree
		}


		// get's the size of the textlblock in pixels - returns array with 8 xy coords starting with x of the bottom left corner [0] and ending with the y of the upper left corner [7]
		// orgin of text block is bottom left corner
		$tb = imagettfbbox($fontSize, $this->angle, $font, $text); 
		$x = ceil(($this->imgWidth - $tb[2] - $tb[6] ) / 2); // lower right x -  upper left x (in case of angle != 0 : text block width in respect to the img width
		$y = ceil(($this->imgHeight - $tb[5]  ) / 2); // upper right y : text block height ( negative value)

		// if the flag is true font size will be recalculated so text block can fill all available space
		if($maximize){
			// calculate font size based on image width and text width
			$textLength = strlen($text); // number of caracters
			$hypotenusaText = sqrt( pow($tb[2], 2) + pow($tb[3], 2) ); // in case it's at an angle
			$averageFontWidth = ceil( $hypotenusaText / $textLength); //the avarage width of the letters
			$factor = $fontSize/$averageFontWidth; // an enlargment/reduction factor between font size and the calculated average width of the letters

			$verticalLimit = 180-$diagonal; // degree after which we should use the height to calculate the diagonal space available instead of the width

			$a = ($this->angle > $verticalLimit) ? abs($this->angle-180) : $this->angle; // if the angle is bigger than the vertical limit subtract 180 from the angle to calculate the diagonal space

			// if the angle is larger than the image diagonal and smaller than the vertical limit or the angle is larger than the the diagonal + 180 but smaller than the verticalLimit + 180
			// use image height to calculate the diagonal space, else use imageWidth
			$diagonalSpace = ( ($this->angle > $diagonal and $this->angle < $verticalLimit) or ($this->angle > $diagonal + 180 and $this->angle < $verticalLimit + 180)  ) ? ($this->imgHeight/2) / sin(deg2rad($a)) : ($this->imgWidth/2) / cos(deg2rad($a));

			// fillUpSpace is a percentage of the diagonal
			$fillUpSpace = ( ( $diagonalSpace * 2 ) / 100 ) * $this->maxWidth  ; 

			$fullTextWidth = $fillUpSpace/$textLength; // get the ideal letter width to fill up the whole width of the image

			$fontSize = abs(floor($fullTextWidth*$factor)); // calculate the new font size multiplying the factor with the ideal letter width, floor it and assure it's alway positive

			// get the new coords of the textblock and reset x and y values accordingly
			$tb = imagettfbbox($fontSize, $this->angle, $font, $text); 	
			$x = ceil(($this->imgWidth - $tb[2] - $tb[6] ) / 2); 
			$y = ceil(($this->imgHeight - $tb[5]  ) / 2);
		}

		// let's see if user has forced x and/or y position
		if($this->forceX !== false){
			$forceX = preg_replace('/[^0-9%]/', '', $this->forceX);
			if( stripos($forceX, "%") ){
				$percX = (int) preg_replace('/[^0-9]/', '', $forceX);
				if($percX > 100) $percX = 100;
				if($percX < 0) $percX = 0;
				$forceX = ($this->imgWidth/100)*$percX;
			}
			if($forceX < 0) $forceX = 0;
			if($forceX > $this->imgWidth) $forceX = $this->imgWidth; // ho poco senso dovrei implementare con controllo larghezza, ma per ora lascio così
			$x = $forceX;
		}


		if($this->forceY !== false){
			// anchor point of text block is lower left corner so to obtain the result the user expects we have to add the font haight to the y value
			$forceY = preg_replace('/[^0-9%]/', '', $this->forceY);
			if( stripos($forceY, "%") ){
				$percY = (int) preg_replace('/[^0-9]/', '', $forceY);
				if($percY > 100) $percY = 100;
				if($percY < 0) $percY = 0;
				$forceY = ($this->imgHeight/100)*$percY;
			}
			if($forceY < 0) $forceY = 0;
			$forceY = $forceY + $fontSize;
			if($forceY > $this->imgHeight) $forceY = $this->imgHeight;
			$y = $forceY;
		}


		// Create the image
		$im = imagecreatetruecolor($this->imgWidth, $this->imgHeight);

		// Create colors
		$color 				= $this->rgb2array($this->colorHex);
		$bgcolor 			= $this->rgb2array($this->bgColorHex);

		$textColor 			= imagecolorallocate($im, $color[0], $color[1], $color[2]);
		$backgroundColor 	= imagecolorallocate($im, $bgcolor[0], $bgcolor[1], $bgcolor[2]);

		$white			 	= imagecolorallocate($im, 255, 255, 255);
		$midgrey 			= imagecolorallocate($im, 128, 128, 128);
		$grey	 			= imagecolorallocate($im, 204, 204, 204);
		$lightgrey	 		= imagecolorallocate($im, 224, 224, 224);
		$black 				= imagecolorallocate($im, 0, 0, 0);

		// fill the image with the background color 
		imagefilledrectangle($im, 0, 0, $this->imgWidth-1, $this->imgHeight-1, $backgroundColor);


		if($this->debug){
			$dbgTextCol = $black;
			$dbgLinesCol = $lightgrey;
			// draw guiding lines and info on the image
			imageline ( $im , 0 , $this->imgHeight , $this->imgWidth , 0 ,$dbgLinesCol ); // diagonal
			imageline ( $im , 0 , 0 , $this->imgWidth , $this->imgHeight ,$dbgLinesCol ); // diagonal

			imageline ( $im , $this->imgWidth/2 , 0 , $this->imgWidth/2 , $this->imgHeight ,$dbgLinesCol ); // vertical
			imageline ( $im , 0, $this->imgHeight/2 , $this->imgWidth , $this->imgHeight/2 ,$dbgLinesCol ); // horizontal

			if($maximize){
				$maxSizeWidth = ($this->imgWidth/100)*$this->maxWidth;
				$maxSizeHeight = ($this->imgHeight/100)*$this->maxWidth;
				$p1x = ($this->imgWidth-$maxSizeWidth)/2;
				$p1y = ($this->imgHeight-$maxSizeHeight)/2;
				$p2x = $p1x+$maxSizeWidth;
				$p2y = $p1y;
				$p3x = $p2x;
				$p3y = $p2y+$maxSizeHeight;
				$p4x = $p1x;
				$p4y = $p3y;
				imagepolygon ( $im, array( $p1x, $p1y, $p2x, $p2y, $p3x, $p3y, $p4x, $p4y ), 4, $dbgLinesCol);		
			}


			$dbgBasic = "imgW: ".$this->imgWidth."px | imgH: ".$this->imgHeight."px | x: ".$x."px | y: ".$y."px | fontsize: ".$fontSize."px | col: #".$color['hex']." | bgcol: #".$bgcolor['hex'];
			$dbgAdvanced = "angle: ".number_format($this->angle, 2)."°";
			if(!empty($this->maxWidth)) $dbgAdvanced .= " | maxW: ".number_format($this->maxWidth, 0)."%";
			if(!empty($verticalLimit)) $dbgAdvanced .= " | v-limit: ".number_format($verticalLimit, 2)."°";
			if(!empty($a)) $dbgAdvanced .= " | a: ".number_format($a, 2)."°";
			imagettftext($im, 10, 0, 10, $this->imgHeight-25, $dbgTextCol, $dbgfont, $dbgBasic);
			imagettftext($im, 10, 0, 10, $this->imgHeight-12, $dbgTextCol, $dbgfont, $dbgAdvanced);
		}


		// Add the main text
		imagettftext($im, $fontSize, $this->angle, $x, $y, $textColor, $font, $text);


		if($this->output){
			// Set the content-type
			header('Content-Type: image/png');

			// Create and output png file
			imagepng($im);
			imagedestroy($im);

			
		}else{
			if($this->debug){
				echo "<pre>";
				print_r(get_defined_vars());
				echo "</pre>";
			}else{
				return $im;
				
			}
		}
		die();

		
		
		
	}
	
	
	// color convert function
	private function rgb2array($rgb) {
		$rgb = strtolower($rgb);
		$rgb = preg_replace('/[^0-9abcdef]/', '', $rgb);

		switch(strlen($rgb)){
			case 6:
				$r1 = substr($rgb, 0, 1);
				$r2 = substr($rgb, 1, 1);
				$g1 = substr($rgb, 2, 1);
				$g2 = substr($rgb, 3, 1);
				$b1 = substr($rgb, 4, 1);
				$b2 = substr($rgb, 5, 1);
				$rgb = $r1.$r2.$g1.$g2.$b1.$b2;
				break;
			case 3:
				$r = substr($rgb, 0, 1);
				$g = substr($rgb, 1, 1);
				$b = substr($rgb, 2, 1);
				$rgb = $r.$r.$g.$g.$b.$b;
				break;
			default:
				$rgb = "000000";
				break;
		}

		return array(
			base_convert(substr($rgb, 0, 2), 16, 10),
			base_convert(substr($rgb, 2, 2), 16, 10),
			base_convert(substr($rgb, 4, 2), 16, 10),
			"hex" => $rgb
		);
	}

	private function customError($errno, $errstr, $file, $line, $context) {
		echo "<b>Error:</b> [$errno] <strong>$errstr</strong> on line <strong>$line</strong><br>";
		echo "Ending Script";
		die();
	} 
	
}

?>