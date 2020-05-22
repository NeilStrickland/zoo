<?php

require_once('../zoo.inc');

$id = (int) get_required_parameter('id');
$m = $zoo->load('quiz_group_membership',$id);
if ($m) {
 $m->delete();
}

?>