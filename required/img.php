<?php

/****
 # ISTRUZIONI
   Parametro minimo : file, se no da errore
   Combinazioni di ridimensionamento possibili:
   - W / H : ridimenziona la larghezza o altezza come da parametro e ridimension l'altro lato in base alle proporzioni originali - no upscale, la dimnensione massima  non supererà mai quella dell'immagine originale
   - W / H + U : ridimenziona la larghezza o altezza come da parametro e ridimension l'altro lato in base alle proporzioni originali - con upscale, l'immagine verrà se necessario ingrandita per riepire tutto lo spazio
   - W + H : ridimensiona larghezza ed altezza come da parametri (stretching o squashing)
   - W + H + FC : viene creato un rettangolo con la dimensione di W e H in cui viene inserita l'immagine proporzionata il cui lato più lungo occuperà tutto lo spazio della larghezza o altezza, le parti vuote saranno riempite con FC
   - W + H + C  : viene creato un rettangolo con la dimensione di W e H in cui viene inserita l'immagine che viene ritaglaita per non lasciare spazi vuoti. Viene opscalata per riempire sempre tutto
   Gli altri parametri non influiscono sulla dimensione o aspetto (ratio) dell'immagine
***/

include 'variables.php';
include 'classes/dummy_image.class.php';

include 'classes/cc_mysqli.class.php';
$db = new cc_dbconnect(DB_NAME);
$watermark = $db->get1value("value", DBTABLE_CONFIG, "WHERE param = 'watermark'");


$dummy = new dummy_image(FILEROOT."css/fonts/"); // load dummy_image class and pass abs path of fonts

$save_img = false;

// parse GET values
if(isset($_GET['file'])){
	$file_name = $_GET['file']; // full path
	$path_segments = explode("/", $file_name);
	$photo = array_pop($path_segments); // remove last segment and memorize it as the name of the photograph
	$full_path = implode("/", $path_segments); // reconstruct full path without the file name
	$explode =  explode(".", $photo);
	$ext = end( $explode ); // get extension
}else{
	// TODO  traduzione
	$dummy->draw("No image defined!"); // after output script dies
}


// elaborate other get params
$target_width =  (isset($_GET['w'])) ? intval($_GET['w']) : 0; // width of output image - numbers only
$target_height = (isset($_GET['h'])) ? intval($_GET['h']) : 0; // height of output image - numbers only
$fill_color =    (isset($_GET['fc'])) ? $_GET['fc'] : false; // fill color: of != false keeps aspect ratio and fills the gaps with the defined color - can be #xxx, #xxxxxx or false
$cutimage =      (isset($_GET['c'])) ? parseBool($_GET['c']) : false; // cut image instead of streching/squashing it - ignored if fc != false - boolean
$portion =       (isset($_GET['p'])) ? intval($_GET['p']) : 50; // only for cut image, the starting point in % from where to extract from original image (0 = top, 50 = middle, 10 = bottom)
$upscale =       (isset($_GET['u'])) ? parseBool($_GET['u']) : false; // upscales image if w/h > than source image. if false max size of output file will be size of original img - ignored if fc != false - boolean
$quality =       (isset($_GET['q'])) ? intval($_GET['q']) : 80; //Quality of output iamge - only for jpegs - num from 0 (max compression) to 100 (no compression)
$use_cache =     (isset($_GET['cache'])) ? parseBool($_GET['cache']) : true;  // flag if cached immage is to be used or not



// TODO : traduzione
if(!file_exists($file_name)) $dummy->draw("L'immagine non esiste!");

/*** Cache handling ***
 if cache is on (default) a md5 hash of the filename with all the params will be created. We then check if suche file exists in cache if so get that one, 
else set variable save image to the name of the hashed file name and proceed as usual (file will be saved in last block of code).
Only downfall: old cache will not be deleted. Got to switch to media.php which will use media DB table (TODO).
***/
if($use_cache){
	// create hash of file name + params and add original extension to it 
	$cached_img = md5($photo."w=".$target_width."&h=".$target_height."&fc=".$fill_color."&c=".$cutimage."&p=".$portion."&u=".$upscale."&q=".$quality."&wm=".$watermark).".".$ext;
	// if cached file exists set header accordin to extension, get contetn and output it. Then kill script.
	if(file_exists("../cache/".$cached_img)){
		$mtime_cached = filemtime("../cache/".$cached_img);
		$mtime_img = filemtime($file_name);
		if( $mtime_img < $mtime_cached ){ // proceed only if cahced image is newer than source image
			$type = ($ext == 'jpg') ? 'jpeg' : $ext;
			$type = 'image/'.$type;
			header('Content-Type:'.$type);
			readfile("../cache/".$cached_img);
			die();			
		}else{
			$save_img = "../cache/".$cached_img;
		}
		
	}else{
		// chached file does not exist, set flag to save it later
		$save_img = "../cache/".$cached_img;
	}
}


// Create the image object reference based on the extension - default is jpg
switch($ext){
	case "png":
		$img = imagecreatefrompng($file_name);
		$quality = 4; // lossless compression param
		break;
	case "gif":
		$img = imagecreatefromgif($file_name);
		break;
	default :
		$img = imagecreatefromjpeg($file_name);
		break;
}

if (!$img) {
	// TODO :  traduzione
	$dummy->draw("Could not create image handle");
}

// get original image sizes
$width = imageSX($img);
$height = imageSY($img);

if (!$width || !$height) {
	// TODO : generazione immagine parametrica con messaggio + traduzione
	$dummy->draw("ERROR:Invalid width or height");
}


/*** START RESIZE LOGIC 
	$width / $height : sizes of the original (source) image
	$target_width / $target_height: sizes of the output image
	$new_width / $new_height : sizes the original images will be converted to, so it fits in output image
	$src_w / $src_h : width and height of a portion of the source image (used in cutimage)
	$src_x / $src_y : coords of a portion of the source image (used in cutimage)
	$img_ratio / $t_img_ratio : aspect ratio (>1 = hor, <1 = ver, 1 = square)
***/

// calculate image width / height ratio
$img_ratio = $width / $height;


// If no width and no height is passed set the target_width and target_height values to those of the original image, no resize will be performed
if($target_width == 0 and $target_height == 0){
	$target_width = $width;
	$target_height = $height;
}

if ($target_height == 0){ 
	
	// No height param passed, only width - height will be calculated according to aspect ratio	
	if($upscale){
		// image can be upscaled, so the width to resize the orignal image to is the same as the width of the output image
		$new_width = $target_width;
	}else{
		// no upscale - if the source image width is bigger than the output width reduce it to the output width, else use source image width
		if ($width > $target_width){
			$new_width = $target_width;
		}else{
			$new_width = $width;
		}
	} // end upscale
	
	// calculate new height according to aspect ratio
	$new_height = $new_width/$img_ratio; 
	
	// create the new image handler with the calculated sizes
	$new_img = ImageCreateTrueColor($new_width, $new_height);
	
	// copy source image in new image handler performing a resize
	if (!@imagecopyresampled($new_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height)) {
		$dummy->draw("Could not resize image!");
	}	
	
}else if($target_width == 0){
	
	// No width param passed, only height - width will be calculated according to aspect ratio	
	if($upscale){
		// image can be upscaled, so the height to resize the orignal image to, is the same as the height of the output image
		$new_height = $target_height;
	}else{
		// no upscale - if the source image height is bigger than the output height reduce it to the output height, else use source image height
		if ($height > $target_height){
			$new_height = $target_height;
		}else{
			$new_height = $height;
		}
	} // end upscale
	
	// calculate new width according to aspect ratio
	$new_width = $new_height*$img_ratio;
	
	// create the new image handler with the calculated sizes
	$new_img = ImageCreateTrueColor($new_width, $new_height);
	
	// copy source image in new image handler performing a resize
	if (!@imagecopyresampled($new_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height)) {
		$dummy->draw("Could not resize image");
	}
	
}else{
	// both width and height params are passed....
	if (!$fill_color){ // no fill color passed; image will be streched/squashed or cut...
		// the sizes of the new (resized) image is set to those of the output image
		$new_width = $target_width;
		$new_height = $target_height;
		
		// create the new image handler with the calculated sizes
		$new_img = ImageCreateTrueColor($target_width, $target_height);
		
		
		if($cutimage){
			// the new image will be cut to make it fit the output size and occupy all the avilable space
			
			// let's see from where to extract image
			if($portion > 100) $portion = 100; // is percent, can't exceed 100;
			if($portion < 0) $portion = 0; // is percent, can't be lower than 0;
			$divider = $portion / 100; // from percent to fraction - used in calculation of x or y starting point
			
			$t_img_ratio = $new_width/$new_height; // aspect ratio of the new (resized) image (in this case also aspect ratio of output file)
			
			if($t_img_ratio > $img_ratio){ 
				// if new image is wider than source.
				$src_w = $width; // get the whole width of the source image
				$src_h = $width/$t_img_ratio; // calculate the height based on the new image aspect ratio
				$src_x = 0; // start at left margin of image
				$src_y = round(($height-$src_h)*$divider, 0); // start at top, go down the height of the source image and subtract the calculated new height and divide by 2 (get middel portion)
			}else if($t_img_ratio < $img_ratio){
				// if new image is heigher than source.
				$src_w = $height*$t_img_ratio; // calculate the width based on the new image aspect ratio
				$src_h = $height; // get the whole height of the source image
				$src_x = round(($width-$src_w)*$divider); // start at the left going right the width of the source image minus the calculated new width and divide by 2 (get center portion)
				$src_y = 0; // start at the top margin of image
			}else{
				// same aspect ratio between source and new / output image
				if($img_ratio > 1){
					// if the image ratio is horizontal
					$src_w = $height;
					$src_h = $height;
					$src_x = round(($width-$height)/2); // ???
					$src_y = 0;
				}else if($img_ratio < 1){
					// if the image ratio is vertical
					$src_w = $width;
					$src_h = $width;
					$src_x = 0;
					$src_y = round(($height-$width)/2); // ???
				}else{
					// square
					$src_x = 0;
					$src_y = 0;
					$src_w = $width;
					$src_h = $height;
				}
			}
			// cut out from source image and put it in new image using the calculated coords above
			// imagecopyresampled (  $dst_image ,  $src_image ,  $dst_x ,  $dst_y ,  $src_x ,  $src_y ,  $dst_w ,  $dst_h ,  $src_w ,  $src_h )
			if (!@imagecopyresampled($new_img, $img, 0, 0, $src_x, $src_y, $new_width, $new_height, $src_w, $src_h)) {
				$dummy->draw("ERROR:Could not resize image (".__LINE__.")");
			}			
			
		}else{ 
			// stretch / squash image
			// map the source image to the size of the new image
			if (!@imagecopyresampled($new_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height)) {
				$dummy->draw("ERROR:Could not resize image (".__LINE__.")");
			}			
		}
	}else{
		// got fill color to fill up the spaces left by difference in aspect ratio between source and output image
		
		// calculate the aspect ratio of the output image
		$target_ratio = $target_width / $target_height; 
				
		if ($target_ratio > $img_ratio) {
			// output image is wider than source - get total height of source image and fill left and right side with fill-color
			$new_height = $target_height;
			$new_width = $img_ratio * $target_height; // calculate the slice width multiply ratio of source image by output image height
		} else {
			// output image is heigher than source - get total width of source image and fill top and bottom with fill-color
			$new_height = $target_width / $img_ratio; // calculate the slice height dividing output width with source image ratio
			$new_width = $target_width;
		}
		
		// just in case - checking that new_height and new_width dont' exceed the target size - not realy sure if this can happen...
		if ($new_height > $target_height) {
			$new_height = $target_height;
		}
		if ($new_width > $target_width) {
			$new_height = $target_width;
		}
		
		// create the new image handler with the calculated sizes
		$new_img = ImageCreateTrueColor($target_width, $target_height);
		
		// calculate singe red, green and blue value from hew color
		$rgb = rgb2array($fill_color);
		// allocate fill-color
		$background_color = imagecolorallocate($new_img, $rgb[0], $rgb[1], $rgb[2]);

		// fill new image with fill-color
		if (!@imagefilledrectangle($new_img, 0, 0, $target_width-1, $target_height-1, $background_color)) { // Fill the image black
			$dummy->draw("ERROR:Could not fill new image");
		}
		
		// paste portion of source image (new_img) in output image (on top of fill so to say)
		if (!@imagecopyresampled($new_img, $img, ($target_width-$new_width)/2, ($target_height-$new_height)/2, 0, 0, $new_width, $new_height, $width, $height)) {
			$dummy->draw("ERROR:Could not resize image");
		}
	}
}

// If the watermark switch is true and the WATERMARK_FILE is not empty try to embed  watermark image on top of the image (must be a semi transparent PNG )
if(!empty($watermark)){

	$stamp = imagecreatefrompng('../images/'.$watermark); // TODO what if the file doesn't exist...?
	
	// the watermark original width and height
	$stamp_width 	= imagesx($stamp);
	$stamp_height 	= imagesy($stamp);
		
	// the watermark original width / height ratio
	$stamp_ratio = $stamp_width / $stamp_height; // rapporto d'aspetto delle filigrana
		
	if($stamp_ratio > 1){
		// watermark image is horizonal
		$new_stamp_width = $new_width*.6; // set watermark width to 60% of the new image width
		$new_stamp_height = $new_stamp_width/$stamp_ratio; // calculate proportioned height
	}else{
		// watermark image is vertical
		$new_stamp_height = $new_height*.6;  // set watermark height to 60% of the new image height
		$new_stamp_width = $new_stamp_height*$stamp_ratio;	// calculate proportioned width
	}
	
	// center watermark
	$stamp_x = ($new_width-$new_stamp_width)/2;
	$stamp_y = ($new_height-$new_stamp_height)/2;
	
	// create the new watermark image handler with the calculated sizes
	$new_stamp = ImageCreateTrueColor($new_stamp_width, $new_stamp_height);
	
	// set blending mode to off so the original trancparancy of the watermark image remains intact
	imagealphablending( $new_stamp, false );
	
	// Set the flag to save full alpha channel information of the watermark image (PNG)
	imagesavealpha( $new_stamp, true ); 
	
	// resize original watermark image to new sizes
	imagecopyresampled($new_stamp, $stamp, 0, 0, 0, 0, $new_stamp_width, $new_stamp_height, $stamp_width, $stamp_height);
	
	// paste watermark into new image
	imagecopy($new_img, $new_stamp, $stamp_x, $stamp_y, 0, 0, $new_stamp_width, $new_stamp_height);
}

// output image according to original extension - can be png, gif or jpeg (default)
switch($ext){
	case "png":	
		if($save_img) imagepng($new_img, $save_img, $quality); // save cached file
		header("Content-type: image/png") ;
		imagepng($new_img, null, $quality);
		break;
	case "gif":
		if($save_img) imagegif($new_img, $save_img); // save cached file
		header("Content-type: image/gif") ;
		imagegif($new_img);
		break;
	default :
		if($save_img) imagejpeg($new_img, $save_img, $quality); // save cached file
		header("Content-type: image/jpeg") ;
		imagejpeg($new_img, null, $quality);
		break;
}

/* --- FINE SCRIPT --- */

// function to transform hex rgb value in array with values for red (0), green (1) and blue (2)
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
    );
}

function parseBool($value){
	return (!$value or strtolower($value == 'false') or strtolower($value == 'off')) ? false : true;
}

?>
