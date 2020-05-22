<?php

require_once('../zoo.inc');

$image_id = (int) get_required_parameter('image_id');
$i = $zoo->load('image',$image_id);
if ($i) {
 $i->delete();
}

?>