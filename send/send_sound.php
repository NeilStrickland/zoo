<?php

global $zoo;
$debug = 1;
require_once('../include/zoo.inc');
$id = (int) get_required_parameter('id');
if (! $id) {
 if ($debug) { echo "No id"; }
 exit;
}
$sound = $zoo->load('sound',$id);
if (!$sound) { 
 if ($debug) { echo "No sound"; }
 exit; 
}
$f = $sound->full_file_name();
if (! file_exists($f)) {
 if ($debug) { echo "No file"; }
 exit;
}
header('Content-Type: ' . $sound->mime_type());
header('Cache-Control: public, max-age=3600');
readfile($sound->full_file_name());

?>
