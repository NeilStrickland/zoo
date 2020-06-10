<?php

require_once('include/zoo.inc');

$images = $zoo->load_all('images');
$images_by_file_name = make_index($images,'file_name');

$ix = $zoo->make_species_index();

$temp_images_dir     = $zoo->data_dir . '/temp_images';
$original_images_dir = $zoo->data_dir . '/original_images';
$images_dir          = $zoo->data_dir . '/images';

$files = scandir($temp_images_dir);

foreach($files as $f) {
 $ff = $temp_images_dir . '/' . $f;
 $f0 = strtolower(str_replace('_',' ',$f));

 if (is_dir($ff)) {
  if (preg_match('/^([a-z-]+) ([a-z-]+)$/',$f0,$m)) {
   $g = $m[1];
   $s = $m[2];
   if (isset($ix[$g]) && isset($ix[$g][$s])) {
    $x = $ix[$g][$s];
    $files1 = scandir($ff);
    foreach ($files1 as $f1) {
     if (preg_match('/^.*\.jpe?g$/',strtolower($f1))) {
      add_image($x,$ff . '/' . $f1);
     }
    }
   }
  }
 } else {
  if (preg_match('/^([a-z]+) ([a-z]+) ?[0-9]*\.jpg$/',$f0,$m)) {
   $g = $m[1];
   $s = $m[2];
   if (isset($ix[$g]) && isset($ix[$g][$s])) {
    $x = $ix[$g][$s];
    add_image($x,$ff);
   }
  }
 }
}

function add_image($x,$file) {
 global $zoo;

 $temp_images_dir     = $zoo->data_dir . '/temp_images';
 $original_images_dir = $zoo->data_dir . '/original_images';
 $images_dir          = $zoo->data_dir . '/images';

 $i = $zoo->new_object('image');
 $i->species_id = $x->id;
 $i->save();
 $i->file_name = 'image_' . $i->id . '.jpg';
 $i->save();
 rename($file,
	       $images_dir . '/' . $i->file_name);
 copy(  $images_dir . '/' . $i->file_name,
        $original_images_dir . '/' . $i->file_name);
 $i->set_size();
 echo "$file : {$x->binomial} : {$x->common_name} : {$i->file_name}<br/>\n";
}

?>
