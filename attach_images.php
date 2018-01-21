<?php

require_once('zoo.inc');

$images = $zoo->load_all('images');
$images_by_file_name = make_index($images,'file_name');

$ix = $zoo->make_species_index();

$files = scandir($zoo->data_dir . '/temp_images');
chdir($zoo->image_dir);

foreach($files as $f) {
 $f0 = strtolower(str_replace('_',' ',$f));

 if (preg_match('/^([a-z]+) ([a-z]+) ?[0-9]*\.jpg$/',$f0,$m)) {
  $g = $m[1];
  $s = $m[2];
  if (isset($ix[$g]) && isset($ix[$g][$s])) {
   $x = $ix[$g][$s];

   $i = $zoo->new_object('image');
   $i->species_id = $x->id;
   $i->save();
   $i->file_name = 'image_' . $i->id . '.jpg';
   $i->save();
   rename('temp/' . $f,$i->file_name);
   echo "$f : {$x->binomial} : {$x->common_name} : {$i->file_name}<br/>\n";
  }
 }
}



?>
