<?php

require_once('include/zoo.inc');
require_once('include/util.inc');

$photos = $zoo->load_all('photos');

ob_start();

foreach($photos as $p) {
 if ($p->lat && $p->lng) { continue; }
 if (preg_match('/IMG_[0-9][0-9][0-9][0-9].JPG/', $p->file_name)) {
  continue;
 }
 $f = $p->full_file_name();
 if (!file_exists($f)) {
  echo "File not found: $f<br/>\n";
  ob_flush();
  flush();
  continue;
 }
 $ll = get_gps($f);
 if ($ll === false) {
  echo "No GPS data: $f<br/>\n";
  ob_flush();
  flush();
  continue;
 }
 $p->lat = $ll[0];
 $p->lng = $ll[1];
 $p->save();
 echo "Set GPS data for {$p->filename} to {$p->lat},{$p->lng}<br/>\n";
 ob_flush();
 flush();
}