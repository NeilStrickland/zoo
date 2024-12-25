<?php

require_once('include/zoo.inc');

$quiz_id = 41;
$quiz = $zoo->load('quiz_group',$quiz_id);
$quiz->load_members();
$create = 1;
$f = $zoo->data_dir . '/scratch/birds/egypt.html';
$d = new DOMDocument();
@ $d->loadHTMLFile($f);
$x = new DOMXPath($d);
$t = $x->query('//div[@class="table-responsive"]/table[@class="table"]/tr');

$old_species_index = make_index($zoo->load_all('species'),'binomial');

$order = '';
$family = '';
$old_species = [];
$new_species = [];

foreach($t as $r) {
 $tds = $x->query('td',$r);
 if (! $tds) {
  continue;
 }
 $td = $tds[0];
 if ($td->getAttribute('colspan') == 3) {
  $s = $td->nodeValue;
  $s = trim(str_replace('&nbsp;',' ',htmlentities($s)));
  if (preg_match('/^([A-Z]+): ([A-Z][a-z]+)$/',$s,$m)) {
   $order = ucwords(strtolower($m[1]));
   $family = $m[2];
  }
 } else if (count($tds) >= 2){
  $z = $zoo->new_object('species');
  $z->kingdom = 'Animalia';
  $z->phylum = 'Chordata';
  $z->class = 'Aves';
  $z->order = $order;
  $z->family = $family;
  $z->common_name = $tds[0]->nodeValue;
  $z->binomial = $tds[1]->nodeValue;
  $aa = $x->query('a',$tds[1]);
  if ($aa) {
   $z->extra->url = $aa[0]->getAttribute('href');
   if (preg_match("/^species.jsp\\?avibase_id=([A-Z0-9])+$/",$z->extra->url,$m)) {
    $z->extra->avibase_id = $m[1];
   } else {
    $z->extra->avibase_id = '';
   }
  }
  if (preg_match("/^([A-Z][a-z]+) ([a-z]+)$/",$z->binomial,$m)) {
   $z->genus = $m[1];
   $z->species = $m[2];
  } else {
   $z->genus = '';
   $z->species = '';
  }
  if ($z->binomial && isset($old_species_index[$z->binomial])) {
   $z->extra->old_species = $old_species_index[$z->binomial];
   $z->extra->old_species->load_data_records();
   $oid = $z->extra->old_species->id;
   if (! isset($z->extra->old_species->date_records_by_code['ab'])) {
    $dr = new data_record_ab();
    $dr->species_id = $oid;
    $dr->data_source_id = 6;
    $dr->external_id = $z->extra->avibase_id;
    $dr->save();
    $z->extra->old_species->load_data_records();
   }
   $old_species[] = $z;
   if (! isset($quiz->members_by_species_id[$oid])) {
    $quiz->add_member($oid);
   }
  } else {
   if ($create) {
    $z->save();
    $z->load();
    $dr = new data_record_ab();
    $dr->species_id = $z->id;
    $dr->data_source_id = 6;
    $dr->external_id = $z->extra->avibase_id;
    $dr->save();
    $z->load_data_records();
    $quiz->add_member($z->id);
   }
   $new_species[] = $z;
  }
 }
}

echo "New species: " . count($new_species) . "<br/>\n";
foreach($new_species as $z) {
 echo $z->common_name . ' (' . $z->binomial . ")<br/>\n";
}

echo "<hr/>\n";

echo "Old species: " . count($old_species) . "<br/>\n";
foreach($old_species as $z) {
 echo $z->common_name . ' (' . $z->binomial . ")<br/>\n";
}