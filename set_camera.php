<?php

require_once('include/zoo.inc');
require_once('include/util.inc');

$photos = $zoo->load_where('photos','camera IS NULL');

ob_start();

foreach($photos as $p) {
 if ($p->camera) { continue; }
 $f = $p->full_file_name();
 if (!file_exists($f)) {
  echo "File not found: $f<br/>\n";
  ob_flush();
  flush();
  continue;
 }
 $p->get_exif();
 if ($p->camera) {
  echo "Camera for $f is {$p->camera}<br/>\n";
  $p->save();
  ob_flush();
  flush();
 } else {
  echo "No camera data: $f<br/>\n";
  ob_flush();
  flush();
 }
}