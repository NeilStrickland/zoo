<?php

require_once('include/zoo.inc');

$all_species = $zoo->load_all('species');

$species_by_binomial = array();
$repeats = array();

foreach ($all_species as $s) {
 if (isset($species_by_binomial[$s->binomial])) {
  $species_by_binomial[$s->binomial][] = $s;
  $repeats[$s->binomial] = 1;
 } else {
  $species_by_binomial[$s->binomial] = array($s);
 }
}

$n = count($repeats);

echo "$n repeats <br/><br/><br/>" . PHP_EOL;

foreach($repeats as $b => $x) {
 $ss = $species_by_binomial[$b];
 echo $b . ': ';
 foreach ($ss as $s) {
  echo $s->id . ' ' ;
 }
 echo '<br/>' . PHP_EOL;
 resolve($ss);
}

function resolve($ss) {
 global $zoo;
 
 $id0 = $ss[0]->id;
 $s0 = $ss[0];
 
 foreach ($ss as $s) {
  if ($s->id < $id0) {
   $id0 = $s->id;
   $s0 = $s;
  }
 }

 foreach($ss as $s) {
  if ($s->id == $id0) { continue; }
  $fields = array('class','order','common_name','common_group');

  foreach ($fields as $f) {
   if ($s->$f && ! $s0->$f) { $s0->$f = $s->$f; }
  }

  $s->load_images();
  foreach ($s->images as $i) {
   $i->species_id = $id0;
   $i->save();
  }

  $s->load_data_records();
  foreach($s->data_records as $r) {
   $r->species_id = $id0;
   $r->save();
  }

  $mm = $zoo->load_where('quiz_group_memberships',"species_id={$s->id}");
  foreach($mm as $m) {
   $m->species_id = $id0;
   $m->save();
  }

  $s->delete();
 }

 $ss0 = array();
 foreach ($ss as $s) { $ss0[] = $s->strip_parent(); }
 echo "<pre>"; var_dump($ss0);
}

?>
