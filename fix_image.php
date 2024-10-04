<?php

require_once('include/zoo.inc');

$params = get_params();

if ($params->command == 'apply_fix') {
 apply_fix($params);
} else {
 choose_fix($params);
}

exit;

//////////////////////////////////////////////////////////////////////

function get_params() {
 global $zoo;
 $params = new stdClass();

 $params->missing_file = 0;
 
 $params->standard_aspect_ratio = 1.3333;
 $params->standard_height = 600;
 $params->standard_width = round($params->standard_aspect_ratio *
				 $params->standard_height);

 $r_min = $params->standard_aspect_ratio - 0.01;
 $r_max = $params->standard_aspect_ratio + 0.01;
 
 $params->id = (int) get_optional_parameter('id',0);

 $w = <<<SQL
x.width IS NULL OR x.height IS NULL OR
x.width = 0 OR x.height = 0 OR
x.width < $r_min * x.height OR x.width > $r_max * x.height
   
SQL;
  
 $params->bad_images =
 $zoo->load_where_ordered('images',$w,'x.id');
 
 $ni = 0;
 $pi = 0;
 $id = $params->id;

 foreach($params->bad_images as $x) {
  if ($x->id < $id && (($pi == 0) || ($x->id > $pi))) {
   $pi = $x->id;
  }

  if ($x->id > $id && (($ni == 0) || ($x->id < $ni))) {
   $ni = $x->id;
  }
 }

 $params->next_image = $ni;
 $params->previous_image = $pi;

 if ($params->id) {
  $params->image = $zoo->load('image',$params->id);
  if (! $params->image) {
   error_page('Image does not exist');
   exit;
  }
  $params->image_name = $params->image->binomial;
  if ($params->image->common_name) {
   $params->image_name .= ' (' . $params->image->common_name . ')';
  }
  
  $params->image_url  = $params->image->url();
  $params->image_file = $params->image->full_file_name();

  if (! file_exists($params->image_file)) {
   $params->image->delete();
   $params->missing_image = 1;
   error_page('The image file does not exist so the image record was deleted');
   exit;
  }
  
  $params->image->set_size(1);
  $params->image->load_object();
 } else {

  if (! $params->bad_images) {
   no_bad_images_page($params);
   exit;
  }

  $params->id = $params->bad_images[0]->id;
  
  $params->bad_images_by_id =
   make_index($params->bad_images,'id');

  if (! isset($params->bad_images_by_id[$params->id])) {
   $params->id = $params->bad_images[0]->id;
  }
 
  $params->image = $params->bad_images_by_id[$params->id];
  $params->image->set_size(1);
  $params->image->load_object();

  $params->image_url  = $params->image->url();
  $params->image_file = $params->image->full_file_name();


 }
 
 $params->command = get_restricted_parameter('command',
					     array('load_image','apply_fix'),
					     'load_image');
 return $params;
}

//////////////////////////////////////////////////////////////////////

function choose_fix($params) {
 global $zoo;

 preshrink($params);
 $image = $params->image;
 $img = $image->object;

 // width and height of the uploaded image
 $w0 = imagesx($img);
 $h0 = imagesy($img);

 // We need to end up with an image where the aspect ratio 
 // (width/height) is 4/3.  First find the original aspect ratio.
 $aspect_ratio = $w0 * 1.0 / $h0;

 // We will give the user a box containing the original image 
 // (which may have any aspect ratio) and a cropping frame
 // (which is constrained to have aspect ration 4/3)
 // $ww = width of box
 // $hh = height of box
 // $w = initial width of cropping frame
 // $h = initial height of cropping frame
 // $x0 = left offset of original image in box
 // $y0 = top offset of original image in box
 // $x = initial left offset of original image in box
 // $y = initial top offset of cropping frame in box

 $ar = 1.33333333;
 $is_thin = false;
 $is_fat = false;
 
 if ($aspect_ratio < $ar) {
  // The image is too thin.
  $is_thin = true;
  $ww = max($w0,$h0) + 50;
  $hh = $ww;
  $w  = $w0;
  $h  = round($w0/$ar);
  $x0 = round(($ww - $w0)/2);
  $y0 = round(($hh - $h0)/2);
  $x  = round(($ww - $w)/2);
  $y  = round(($hh - $h)/2);
 } else {
  // The image is too fat.
  $is_fat = true;
  $ww = max($w0,$h0) + 50;
  $hh = $ww;
  $h  = $h0;
  $w  = round($h0*$ar);  
  $x0 = round(($ww - $w0)/2);
  $y0 = round(($hh - $h0)/2);
  $x  = round(($ww - $w)/2);
  $y  = round(($hh - $h)/2);
 }

 $ar0 = round($aspect_ratio,3);
 $n = count($params->bad_images);
 
 echo <<<HTML
<html>
<head>
  <link rel="stylesheet" href="css/zoo.css" TYPE="text/css"/>
  <script type="text/javascript" src="js/frog.js"></script>
  <script type="text/javascript" src="js/fix_image.js"></script>
</head>
<body onload="fixer.init($x,$y,$w,$h,$x0,$y0,$w0,$h0,$ww,$hh,$ar,{$params->next_image},{$params->previous_image})">
<div id="main_div">
<h1>Editing image: {$params->image_name} ($n left)</h1>
<br/>
<table class="edged">
 <tr>
  <td style="width:50px">{$image->id}</td>
  <td style="width:300px">{$image->binomial}</td>
  <td style="width:200px">{$w0}/{$h0} = {$ar0}</td>
 </tr>
</table>
<table>
 <tr>
HTML;

 if ($params->previous_image) {
  $pi = $params->previous_image;
  echo <<<HTML
  <td class="command" onclick="fixer.load_image($pi)" style="width:100px;">Previous</td>
   
HTML;
 }
 
 if ($params->next_image) {
  $ni = $params->next_image;
  echo <<<HTML
  <td class="command" onclick="fixer.load_image($ni)" style="width: 100px">Next</td>
   
HTML;
 }
 if ($is_thin) {
  echo <<<HTML
  <td class="command" onclick="fixer.use_top()    " style="width:100px;">Top</td>
  <td class="command" onclick="fixer.use_vmiddle()" style="width:100px;">Middle</td>
  <td class="command" onclick="fixer.use_bottom() " style="width:100px;">Bottom</td>
  <td class="command" onclick="fixer.use_vouter() " style="width:100px;">Outer</td>
  <td class="command" onclick="fixer.apply_fix()  " style="width:100px;">Apply</td>

HTML;
 } else {
  echo <<<HTML
  <td class="command" onclick="fixer.use_left()   " style="width:100px;">Left</td>
  <td class="command" onclick="fixer.use_hmiddle()" style="width:100px;">Middle</td>
  <td class="command" onclick="fixer.use_right()  " style="width:100px;">Right</td>
  <td class="command" onclick="fixer.use_houter() " style="width:100px;">Outer</td>
  <td class="command" onclick="fixer.apply_fix()  " style="width:100px;">Apply</td>

HTML;
 }
 
 echo <<<HTML
 </tr>
</table>
<br/>
<div id="resize_div" class="main">
 <div id="middle_div">
  <img id="main_image" src="{$params->image_url}" alt="image to edit"
    style="position:absolute; width:{$w0}px; height:{$h0}px; left:{$x0}px; top:{$y0}px; z-index:1;"
  />
  <div id="glass_div" class="glass">
  </div>
 </div>
</div>

<form id="main_form" action="fix_image.php" method="post">
<input type="hidden" name="command" value="load_image">
<input type="hidden" name="id" value="{$params->id}">
<input type="hidden" name="crop_x" value="0">
<input type="hidden" name="crop_y" value="0">
<input type="hidden" name="crop_w" value="0">
<input type="hidden" name="crop_h" value="0">
</form>
</div>
<br/><br/>
<table>
 <tr>
  <td class="command" onclick="fixer.delete_image()">Delete image</td>
 </tr>
</table>
</body>
</html>

HTML;
}

//////////////////////////////////////////////////////////////////////

function preshrink($params) {
 $img = $params->image;
 $io = $img->object;
 
 $w0 = imagesx($io);
 $h0 = imagesy($io);

 $max_width = 1000.0;
 $max_height = 0.75 * $max_width;
 $ratio = max($w0 / $max_width, $h0 / $max_height);
 if ($ratio > 1) {
  $w1 = (int) ($w0 / $ratio);
  $h1 = (int) ($h0 / $ratio);
  $scaled_image = imagescale($io,$w1,$h1);
  $img->width = $w1;
  $img->height = $h1;
  $img->save();
  imagejpeg($scaled_image,$params->image_file);
  $img->object = $scaled_image;
 }
}

//////////////////////////////////////////////////////////////////////

function apply_fix($params) {
 global $zoo;

 $img = $params->image->object;
 
 $w0 = imagesx($img);
 $h0 = imagesy($img);

 $cx = (int) get_optional_parameter('crop_x',0);
 $cy = (int) get_optional_parameter('crop_y',0);
 $cw = (int) get_optional_parameter('crop_w',$w0);
 $ch = (int) get_optional_parameter('crop_h',$h0);

 if ($cx < 0 || $cx+$cw > $w0 || $cy < 0 || $cy+$ch > $h0) {
  # pad the image
 }

 if ($ch >= $params->standard_height) {
  $h1 = $params->standard_height;
  $w1 = $params->standard_width;
 } else {
  $h1 = $ch;
  $w1 = $cw;
 }
 
 $cropped_image = imagecreatetruecolor($w1,$h1);
 imagecopyresampled($cropped_image,$img,0,0,$cx,$cy,$w1,$h1,$cw,$ch);

 $new_file_name = "pictures/image_{$params->id}_fixed.jpg";
 $new_url = $new_file_name;
 
 imagejpeg($cropped_image,$params->image_file);
 $params->image->width  = $w1;
 $params->image->height = $h1;
 $params->image->save();
 $u = $params->image_url . '?t=' . time();

 $pi = $params->previous_image;
 $ni = $params->next_image;

 echo <<<HTML
<html>
<head>
 <link rel="stylesheet" href="css/zoo.css" TYPE="text/css"/>
 <script type="text/javascript">
var previous_image = $pi;
var next_image = $ni;

function skip_to(i) {
 document.location = 'fix_image.php?id=' + i;
}

function skip_next() { skip_to(next_image); }
function skip_previous() { skip_to(previous_image); }
 
function skip() {
 if (next_image) {
  skip_next();
  return;
 }
 if (previous_image) {
  skip_previous();
  return;
 }
}
 
function init() {
 document.body.onkeydown = function(e) {
    if (e.key == "Enter") { skip(); }
 };
}
 </script>
</head>
<body onload="init()">
<div id="main_div">
<h1>Edited image: {$params->image_name}</h1>
<br/>
<table>
 <tr>
HTML;

 $pi = 0;
 $ni = 0;
 
 if ($params->previous_image) {
  $pi = $params->previous_image;
  echo <<<HTML
  <td class="command" onclick="skip_previous()" style="width:100px;">Previous</td>
   
HTML;
 }
 
 if ($params->next_image) {
  $ni = $params->next_image;
  echo <<<HTML
  <td class="command" onclick="skip_next()" style="width: 100px">Next</td>
   
HTML;
 }

 echo <<<HTML
 </tr>
</table>

<br/><br/>
<img src="$u"/>
</body>
</html>
  
HTML;
}

//////////////////////////////////////////////////////////////////////

function no_bad_images_page($params) {
 echo <<<HTML
No bad images
HTML;
}

?>
