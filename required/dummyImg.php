<?php

include 'variables.php';
set_error_handler("customError"); 

// vars
$imgWidth  		=  (isset($_GET['w'])) ? intval($_GET['w']) : 600; // only numbers
$imgHeight 		=  (isset($_GET['h'])) ? intval($_GET['h']) : 400; // only numbers

$angle 			=  (isset($_GET['a'])) ? intval($_GET['a']) : 0; // angle at which to rotate the text
$text 			=  (isset($_GET['text'])) ? $_GET['text'] : "FILE NOT FOUND!"; // The text to display

$forceFontSize 	=  (isset($_GET['fs'])) ? intval($_GET['fs']) : 0; // font size - int

$maxWidth 		=  (isset($_GET['maxwidth'])) ? intval($_GET['maxwidth']) : 80; // in % - int

$imageDiagonal 	=  (isset($_GET['diag'])) ? $_GET['diag'] : false; // 0, 1, false, true

$help 			=  (isset($_GET['help'])) ? $_GET['help'] : false; // 0, 1, false, true
$output 		=  (isset($_GET['output'])) ? $_GET['output'] : true; // 0, 1, false, true

$colorHex 		= (isset($_GET['color'])) ? $_GET['color'] : "#DD4B39"; // hex 3 or 6 # is not necessary
$bgColorHex 	= (isset($_GET['bgcolor'])) ? $_GET['bgcolor'] : "#FFFFFF"; // hex 3 or 6 # is not necessary

// force x and/or y position, overwrite the calculated ones
$forceX			= (isset($_GET['x'])) ? $_GET['x'] : false; // can be followed by %, al other non numericals will be filtered out
$forceY			= (isset($_GET['y'])) ? $_GET['y'] : false; // can be followed by %, al other non numericals will be filtered out


// calculations
$fontPath = FILEROOT."css/fonts/";
//$font = $fontPath.'chalb.ttf'; // Chanson Heavy
$font = $fontPath.'ArialBlack.ttf';

$dbgfont = $fontPath.'CourierNew.ttf';

if($angle > 360) $angle = $angle - 360;

if(empty($forceFontSize)){
	 $fontSize = 100;
	 $maximize = true;
}else{
	 $fontSize = $forceFontSize;
	 $maximize = false;
}


$hypotenusaImage =  sqrt( pow($imgWidth, 2) + pow($imgHeight, 2) ); // diagonal of the image
$sin = asin($imgHeight/$hypotenusaImage); // sinis value of the diagonal
$diagonal = rad2deg($sin); // convert sinis value to degree 

// set angle to diagonal value if flag is true
if($imageDiagonal){	
	$angle = $diagonal; // set text angle to diagonal degree
}


// get's the size of the textlblock in pixels - returns array with 8 xy coords starting with x of the bottom left corner [0] and ending with the y of the upper left corner [7]
// orgin of text block is bottom left corner
$tb = imagettfbbox($fontSize, $angle, $font, $text); 
$x = ceil(($imgWidth - $tb[2] - $tb[6] ) / 2); // lower right x -  upper left x (in case of angle != 0 : text block width in respect to the img width
$y = ceil(($imgHeight - $tb[5]  ) / 2); // upper right y : text block height ( negative value)

// if the flag is true font size will be recalculated so text block can fill all available space
if($maximize){
	// calculate font size based on image width and text width
	$textLength = strlen($text); // number of caracters
	$hypotenusaText = sqrt( pow($tb[2], 2) + pow($tb[3], 2) ); // in case it's at an angle
	$averageFontWidth = ceil( $hypotenusaText / $textLength); //the avarage width of the letters
	$factor = $fontSize/$averageFontWidth; // an enlargment/reduction factor between font size and the calculated average width of the letters
	
	$verticalLimit = 180-$diagonal; // degree after which we should use the height to calculate the diagonal space available instead of the width
	
	$a = ($angle > $verticalLimit) ? abs($angle-180) : $angle; // if the angle is bigger than the vertical limit subtract 180 from the angle to calculate the diagonal space
	
	// if the angle is larger than the image diagonal and smaller than the vertical limit or the angle is larger than the the diagonal + 180 but smaller than the verticalLimit + 180
	// use image height to calculate the diagonal space, else use imageWidth
	$diagonalSpace = ( ($angle > $diagonal and $angle < $verticalLimit) or ($angle > $diagonal + 180 and $angle < $verticalLimit + 180)  ) ? ($imgHeight/2) / sin(deg2rad($a)) : ($imgWidth/2) / cos(deg2rad($a));
	
	// fillUpSpace is a percentage of the diagonal
	$fillUpSpace = ( ( $diagonalSpace * 2 ) / 100 ) * $maxWidth  ; 

	$fullTextWidth = $fillUpSpace/$textLength; // get the ideal letter width to fill up the whole width of the image
	
	$fontSize = abs(floor($fullTextWidth*$factor)); // calculate the new font size multiplying the factor with the ideal letter width, floor it and assure it's alway positive
	
	// get the new coords of the textblock and reset x and y values accordingly
	$tb = imagettfbbox($fontSize, $angle, $font, $text); 	
	$x = ceil(($imgWidth - $tb[2] - $tb[6] ) / 2); 
	$y = ceil(($imgHeight - $tb[5]  ) / 2);
}

// let's see if user has forced x and/or y position
if($forceX !== false){
	$forceX = preg_replace('/[^0-9%]/', '', $forceX);
	if( stripos($forceX, "%") ){
		$percX = (int) preg_replace('/[^0-9]/', '', $forceX);
		if($percX > 100) $percX = 100;
		if($percX < 0) $percX = 0;
		$forceX = ($imgWidth/100)*$percX;
	}
	if($forceX < 0) $forceX = 0;
	if($forceX > $imgWidth) $forceX = $imgWidth; // ho poco senso dovrei implementare con controllo larghezza, ma per ora lascio così
	$x = $forceX;
}


if($forceY !== false){
	// anchor point of text block is lower left corner so to obtain the result the user expects we have to add the font haight to the y value
	$forceY = preg_replace('/[^0-9%]/', '', $forceY);
	if( stripos($forceY, "%") ){
		$percY = (int) preg_replace('/[^0-9]/', '', $forceY);
		if($percY > 100) $percY = 100;
		if($percY < 0) $percY = 0;
		$forceY = ($imgHeight/100)*$percY;
	}
	if($forceY < 0) $forceY = 0;
	$forceY = $forceY + $fontSize;
	if($forceY > $imgHeight) $forceY = $imgHeight;
	$y = $forceY;
}


// Create the image
$im = imagecreatetruecolor($imgWidth, $imgHeight);

// Create colors
$color 				= rgb2array($colorHex);
$bgcolor 			= rgb2array($bgColorHex);

$textColor 			= imagecolorallocate($im, $color[0], $color[1], $color[2]);
$backgroundColor 	= imagecolorallocate($im, $bgcolor[0], $bgcolor[1], $bgcolor[2]);

$white			 	= imagecolorallocate($im, 255, 255, 255);
$midgrey 			= imagecolorallocate($im, 128, 128, 128);
$grey	 			= imagecolorallocate($im, 204, 204, 204);
$lightgrey	 		= imagecolorallocate($im, 224, 224, 224);
$black 				= imagecolorallocate($im, 0, 0, 0);

// fill the image with the background color 
imagefilledrectangle($im, 0, 0, $imgWidth-1, $imgHeight-1, $backgroundColor);


if($help){
	$dbgTextCol = $black;
	$dbgLinesCol = $lightgrey;
	// draw guiding lines and info on the image
	imageline ( $im , 0 , $imgHeight , $imgWidth , 0 ,$dbgLinesCol ); // diagonal
	imageline ( $im , 0 , 0 , $imgWidth , $imgHeight ,$dbgLinesCol ); // diagonal
	
	imageline ( $im , $imgWidth/2 , 0 , $imgWidth/2 , $imgHeight ,$dbgLinesCol ); // vertical
	imageline ( $im , 0, $imgHeight/2 , $imgWidth , $imgHeight/2 ,$dbgLinesCol ); // horizontal
	
	if($maximize){
		$maxSizeWidth = ($imgWidth/100)*$maxWidth;
		$maxSizeHeight = ($imgHeight/100)*$maxWidth;
		$p1x = ($imgWidth-$maxSizeWidth)/2;
		$p1y = ($imgHeight-$maxSizeHeight)/2;
		$p2x = $p1x+$maxSizeWidth;
		$p2y = $p1y;
		$p3x = $p2x;
		$p3y = $p2y+$maxSizeHeight;
		$p4x = $p1x;
		$p4y = $p3y;
		imagepolygon ( $im, array( $p1x, $p1y, $p2x, $p2y, $p3x, $p3y, $p4x, $p4y ), 4, $dbgLinesCol);		
	}
	

	$dbgBasic = "imgW: ".$imgWidth." | imgH: ".$imgHeight." | x: ".$x." | y: ".$y." | sin: ".rad2deg($sin)." - ";
	$dbgBasic = "imgW: ".$imgWidth."px | imgH: ".$imgHeight."px | x: ".$x."px | y: ".$y."px | fontsize: ".$fontSize."px | col: #".$color['hex']." | bgcol: #".$bgcolor['hex'];
	$dbgAdvanced = "angle: ".number_format($angle, 2)."°";
	if(!empty($maxWidth)) $dbgAdvanced .= " | maxW: ".number_format($maxWidth, 0)."%";
	if(!empty($verticalLimit)) $dbgAdvanced .= " | v-limit: ".number_format($verticalLimit, 2)."°";
	if(!empty($a)) $dbgAdvanced .= " | a: ".number_format($a, 2)."°";
	imagettftext($im, 10, 0, 10, $imgHeight-25, $dbgTextCol, $dbgfont, $dbgBasic);
	imagettftext($im, 10, 0, 10, $imgHeight-12, $dbgTextCol, $dbgfont, $dbgAdvanced);
}


// Add the main text
imagettftext($im, $fontSize, $angle, $x, $y, $textColor, $font, $text);


if($output){
	// Set the content-type
	header('Content-Type: image/png');

	// Create and outpur png file
	imagepng($im);
	imagedestroy($im);
	
}



// color convert function
function rgb2array($rgb) {
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

function customError($errno, $errstr, $file, $line, $context) {
  echo "<b>Error:</b> [$errno] <strong>$errstr</strong> on line <strong>$line</strong><br>";
  echo "Ending Script";
  die();
} 
?>
