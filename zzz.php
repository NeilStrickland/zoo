<?php

require_once('zoo.inc');
set_time_limit(1200);
ob_end_flush();

$q0 = $zoo->load('quiz_group',6);
$q0->load_members();

foreach($q0->members_by_species_id as $id => $m) {
 $s = $zoo->load('species',$id);
 $s->load_data_records();
 echo "<br/><br/>$id: $s->binomial ($s->common_name)<br/>\n";
 $s->find_data_records();
 $s->load_data_records();

 foreach($s->data_records as $i => $r) {
  echo "({$r->data_source_name}:{$r->external_id}) ";
 }
 flush();
}


?>
