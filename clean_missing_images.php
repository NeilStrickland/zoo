<?php

require_once('include/zoo.inc');

$all_images = $zoo->load_all('images');

chdir($zoo->image_dir);

foreach($all_images as $i) {
 if (! file_exists($i->file_name)) {
  echo "{$i->id},{$i->file_name}<br/>\n";
  $i->delete();
 }
}



?>
