<?php

require_once('zoo.inc');

$groups = explode(',',get_optional_parameter('group',''));

foreach($groups as $group) {
 if ($group) {
  $q = "(common_group = '' OR common_group IS NULL) ";
  $q .= "AND common_name LIKE '% $group'";
  $ss = $zoo->load_where('species',$q);
  foreach($ss as $s) {
   $s->common_group = $group;
   $s->save();
   echo "{$s->common_name} : $group <br/>\n";
  }
 }
}

