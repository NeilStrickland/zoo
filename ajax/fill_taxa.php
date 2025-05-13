<?php

require_once('../include/zoo.inc');

$genus   = get_optional_parameter('genus','');
$family  = get_optional_parameter('family','');
$order   = get_optional_parameter('order','');
$class   = get_optional_parameter('class','');
$phylum  = get_optional_parameter('phylum','');
$kingdom = get_optional_parameter('kingdom','');

$taxa = $zoo->load_all('taxa');
$taxa_index = [];
$taxa_by_id = [];
foreach($taxa as $t) {
 $taxa_by_id[$t->id] = $t;
 if (! isset($taxa_index[$t->trank])) {
  $taxa_index[$t->trank] = [];
 }
 $taxa_index[$t->trank][$t->name] = $t;
}

$chain = new stdClass();
$chain->genus   = $genus;

$tranks = ['genus', 'family', 'order', 'class', 'phylum', 'kingdom'];
$taxa = [$genus, $family, $order, $class, $phylum, $kingdom];

for($i = 1; $i < 6; $i++) {
 $trank = $tranks[$i];
 $srank = $tranks[$i-1];
 if ($taxa[$i]) {
  $chain->$trank = $taxa[$i];
 } else if ($chain->$srank && isset($taxa_index[$srank][$chain->$srank])) {
  $chain->$trank = $taxa_index[$srank][$chain->$srank]->parent_name;
 }
}

echo json_encode($chain);