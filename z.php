<?php

require_once('zoo.inc');

$file = "D:/wamp/data/zoo/scratch/fish/fbid.csv";

echo "<pre>";

if (($handle = fopen($file, "r")) !== FALSE) {
 while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
  $num = count($data);
  if ($num >= 2) {
   $id = $data[0];
   $fbid = $data[1];
   $x = $zoo->load('species',$id);
   if ($x) {
    if ($x->fishbase_id && ($x->fishbase_id != $fbid)) {
     echo "Mismatch: " . PHP_EOL;
     $x->dump();
    } else {
     $x->fishbase_id = $fbid;
     $x->save();

     $x->load_data_records();
     $found = false;
     foreach ($x->data_records as $r) {
      if ($r->data_source_id == 5) {
       $r->external_id = $fbid;
       $r->save();
       $r->dump();
       $found = true;
      }
     }

     if (! $found) {
      $r = $zoo->new_object('data_record');
      $r->species_id = $id;
      $r->data_source_id = 5;
      $r->external_id = $fbid;
      $r->save();
      $r->dump();
     }
    }
   } else {
    echo "Not found: " . $id . PHP_EOL;
   }
  }
 }
}



?>
