<?php
/*
Plugin Name: Process Image After Upload
Plugin URI: https://github.com/PlethoraLabs/process-image-after-upload
Description: Simple plugin to automatically process uploaded images... <a href="options-general.php?page=process-after-upload">Settings</a>
Author: PlethoraThemes
Version: 0.1.0
Author URI: http://plethorathemes.com
*/

$PLUGIN_VERSION = '0.1.0';

// DEFAULT PLUGIN VALUES
if ( get_option('ple-piau_version') != $PLUGIN_VERSION ) {
  add_option('ple-piau_version', $PLUGIN_VERSION, '','yes');
  add_option('ple-piau_autolevels', 'yes', '','yes');
  add_option('ple-piau_sharpen', 'yes', '','yes');

  // add_option('ple-piau_resizeupload_width', '1200', '', 'yes');
  // add_option('ple-piau_resizeupload_quality', '90', '', 'yes');
}

add_action('admin_menu', 'add_options_menu');
add_action('wp_handle_upload', 'processImage');

function add_options_menu(){
	add_options_page(
		'Process Image after Upload',
		'Process Image after Upload',
		'manage_options',
		'process-after-upload',
		'render_options_page'
	);
} 

function render_options_page(){

  if(isset($_POST['ple-piau_options_update'])) {

    $autolevels_enabled = trim(esc_sql($_POST['yesno']));
    $sharpen_enabled    = trim(esc_sql($_POST['sharpen']));
    // $max_width          = trim(esc_sql($_POST['maxwidth']));
    // $compression_level  = trim(esc_sql($_POST['quality']));
    // $max_width          = ($max_width == '') ? 0 : $max_width;
    // $max_width          = (ctype_digit(strval($max_width)) == false) ? get_option('ple-piau_resizeupload_width') : $max_width;
    // $compression_level = ($compression_level == '') ? 1 : $compression_level;
    // $compression_level = (ctype_digit(strval($compression_level)) == false) ? get_option('ple-piau_resizeupload_quality') : $compression_level;
    // if ( $compression_level < 1) { $compression_level = 1; } else if ( $compression_level > 100 ) { $compression_level = 100; }

    // update_option('ple-piau_resizeupload_width',$max_width);
    // update_option('ple-piau_resizeupload_quality',$compression_level);
    update_option('ple-piau_autolevels', ($autolevels_enabled == 'yes')? 'yes' : 'no' ); 
    update_option('ple-piau_sharpen', ($sharpen_enabled == 'yes')? 'yes' : 'no' ); 

    echo('<div id="message" class="updated fade"><p><strong>Options have been updated.</strong></p></div>');
  }

  // GET OPTIONS AND SHOW SETTINGS FORM
  $autolevels_enabled = get_option('ple-piau_autolevels');
  $sharpen_enabled    = get_option('ple-piau_sharpen');
  // $compression_level  = intval(get_option('ple-piau_resizeupload_quality'));
  // $max_width          = get_option('ple-piau_resizeupload_width');

  require(plugin_dir_path(__FILE__ ) . 'style.php');
  require(plugin_dir_path(__FILE__ ) . 'options.php');

} 

function processImage($image_data){

  $allowed_types = Array('image/jpeg', 'image/jpg', 'image/png');

  // GET SETTINGS
  $autolevels_enabled       = ( get_option('ple-piau_autolevels') == 'yes' ) ? true : false;
  $sharpen_enabled          = ( get_option('ple-piau_sharpen') == 'yes' ) ? true : false;
  $compression_level        = get_option('ple-piau_resizeupload_quality');
  $max_width                = get_option('ple-piau_resizeupload_width')==0 ? false : get_option('ple-piau_resizeupload_width');

  if ( !$autolevels_enabled || !in_array( $image_data['type'], $allowed_types) ) return $image_data;

  // PROCESS IMAGE
  $imagePath   = $image_data['file'];
  $im = ( $image_data['type'] == 'image/png' ) ? imagecreatefrompng($imagePath) : imagecreatefromjpeg($imagePath);
  $im_size     = getimagesize($imagePath);
  $im_width    = $im_size[0];
  $im_height   = $im_size[1];
  $im_data_RGB = getRGBImageData($im);
  $im_data_RGB = autoLevels($im_data_RGB);
  $gd          = putImage($im_data_RGB, $im_width, $im_height);

  // SHARPEN
  // http://adamhopkinson.co.uk/blog/2010/08/26/sharpen-an-image-using-php-and-gd/
  if ( $sharpen_enabled ){

    // (1) SHARPEN FILTER
    // $sharpen = array( array(0.0, -1.0, 0.0), array(-1.0, 5.0, -1.0), array(0.0, -1.0, 0.0) );

    // (2) MORE SUBTLE SHARPEN FILTER
    // $sharpen = array( array( 0, -2,  0), array(-2, 11, -2), array( 0, -2,  0) );

    // EVEN MORE SUBTLE SHARPEN FILTER WITH LESS ARTIFACTS PRODUCED
    $sharpen = array(
      array( 0, -2,  0),
      array(-2, 18, -2),
      array( 0, -2,  0)
    );

    // calculate the sharpen divisor
    $divisor = array_sum(array_map('array_sum', $sharpen));
    // apply the matrix
    imageconvolution($gd, $sharpen, $divisor, 0);
  }

  if ( $image_data['type'] == 'image/png' ){
    imagepng($gd, $imagePath);
  } else {
    imagejpeg($gd, $imagePath);
  }
  return $image_data;
}

function getRGBImageData($im){

  $im_width    = imagesx($im);
  $im_height   = imagesy($im);
  $im_length   = $im_width * $im_height;
  $string_data = '';

  for ( $h = 0; $h < $im_height; $h++) { 
    for ( $w = 0; $w < $im_width; $w++) { 
      // GET PIXEL VALUES
      $rgb = imagecolorat($im, $w, $h);
      $r   = ($rgb >> 16) & 0xFF;
      $g   = ($rgb >> 8) & 0xFF;
      $b   = $rgb & 0xFF;
      $string_data .= pack('I', $r);
      $string_data .= pack('I', $g);
      $string_data .= pack('I', $b);
    }
  }
  return $string_data;

}

function autoLevels($im_data){

  $pixelNum = strlen($im_data);

  // INITIALIZE BRIGHTNESS FOR LEVELS
  $redMax   = 0; 
  $redMin   = 255;
  $greenMax = 0; 
  $greenMin = 255;
  $blueMax  = 0; 
  $blueMin  = 255;

  $counter = 0;
  for ( $i = 0; $i < $pixelNum/12; $i++ ) { 
    $r = getS($counter, $im_data);
    $g = getS($counter+1, $im_data);
    $b = getS($counter+2, $im_data);
    if ( $r > $redMax ){ $redMax = $r; }
    if ( $r < $redMin ){ $redMin = $r; }
    if ( $g > $greenMax ){ $greenMax = $g; }
    if ( $g < $greenMin ){ $greenMin = $g; }
    if ( $b > $blueMax ){ $blueMax = $b; }
    if ( $b < $blueMin ){ $blueMin = $b; }
    $counter = $counter + 3;
  }

  $counter = 0;
  $debug = 0;
  $im_data_new = '';
  for( $i = 0; $i < $pixelNum/12; $i++ ){
    // MAP COLORS TO 0 - 255 RANGE
    $r = (getS($counter, $im_data) - $redMin) * (255 / ($redMax - $redMin));
    $g = (getS($counter+1, $im_data) - $greenMin) * (255 / ($greenMax - $greenMin));
    $b = (getS($counter+2, $im_data) - $blueMin) * (255 / ($blueMax - $blueMin));
    $im_data_new .= pack('I', $r);
    $im_data_new .= pack('I', $g);
    $im_data_new .= pack('I', $b);
    $counter = $counter + 3;
    $debug++;
  }  
  return $im_data_new;

}

function putImage($imageData, $im_width, $im_height){
  $gd        = imagecreatetruecolor($im_width, $im_height);
  $counter   = 0;
  for ( $h = 0;  $h < $im_height; $h++ ) { 
    for ( $w = 0;  $w < $im_width; $w++ ) { 
      $color = imagecolorallocate($gd, getS($counter, $imageData), getS($counter+1, $imageData), getS($counter+2, $imageData) ); 
      imagesetpixel( $gd, $w, $h, $color );
      $counter = $counter + 3;
    }
  }
  return $gd;
}

function getS($num, $data){
  return current(unpack('I', substr($data, $num * 4, 4)));
}

// NEEDS TESTING
function getRGB($x, $y, $data, $im_height){

  $data_len = strlen($data);
  $col_length = $data_len / 3;
  $start = ( ( $x - 1 ) * 3 ) + ( ( $y - 1 ) * $col_length );
  $r = getS( $start, $data);
  $g = getS( $start+1, $data);
  $b = getS( $start+2, $data);
  return Array( $r, $g, $b );

}