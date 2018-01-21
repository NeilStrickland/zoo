<?php

global $zoo;
require_once('zoo.inc');
$id = (int) get_required_parameter('id');
$image = $zoo->load('image',$id);
if (!$image) { exit; }
header('Content-Type: image/jpeg');
readfile($image->full_file_name());

?>
