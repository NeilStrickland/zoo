<?php

global $zoo;
require_once('../include/zoo.inc');
$id = (int) get_required_parameter('id');
$image = $zoo->load('image',$id);
if (!$image) { exit; }
header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=3600');
readfile($image->full_file_name());

?>
