<?php

require_once('../include/zoo.inc');

$sound_id = (int) get_required_parameter('sound_id');
$x = $zoo->load('sound',$sound_id);
if ($x) {
 $x->delete();
}

?>
