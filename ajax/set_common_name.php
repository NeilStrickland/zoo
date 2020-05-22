<?php

require_once('../zoo.inc');

$species_id = (int) get_required_parameter('species_id');
$common_name = get_safe_required_parameter('common_name');

$s = $zoo->load('species',$species_id);

if (! $s) { exit; }

$s->common_name = $common_name;
$s->save();

?>
