<?php

global $zoo;
require_once('../include/zoo.inc');
$id = (int) get_required_parameter('id');
$debug = get_optional_parameter('debug',0) ? 1 : 0;
$photo = $zoo->load('photo',$id);
if (!$photo) {
 echo "No photo with id={$id}"; 
 exit; 
}
$f = $photo->full_file_name();
if (! file_exists($f)) {
 echo "No file {$f}";
 exit;
}
if ($debug) {
 echo "File: $f\n";
 exit;
}
header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=3600');
readfile($photo->full_file_name());

?>
