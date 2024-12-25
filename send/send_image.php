<?php

global $zoo;
require_once('../include/zoo.inc');

$debug = ((int) get_optional_parameter('debug',0)) ? 1 : 0;
if ($debug) {
 echo "Debugging<br/>\n";
}
$id = (int) get_required_parameter('id');
if ($debug) {
 echo "id=$id<br/>\n";
}
$image = $zoo->load('image',$id);
if (!$image) { 
 if ($debug) {
  echo "Image $id not found<br/>\n";
 }
 exit; 
}
if ($debug) {
 echo "Image $id found<br/>\n";
 $image->dump();
}
$f = $image->full_file_name();
if (!file_exists($f)) {
 if ($debug) {
  echo "File $f not found<br/>\n";
 }
 exit;
}
if ($debug) {
 echo "Sending $f<br/>\n";
} else {
header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=3600');
readfile($f);
}

?>
