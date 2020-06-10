<?php

require_once('../include/zoo.inc');

$species_id = (int) get_required_parameter('species_id');
$url = get_required_parameter('url');
$i = $zoo->new_object('image');
$i->species_id = $species_id;
$i->save();
$i->file_name = 'image_' . $i->id . '.jpg';
$i->save();
$f = $zoo->image_dir . '/' . $i->file_name;
file_put_contents($f,file_get_contents($url));
echo $i->id;

?>
