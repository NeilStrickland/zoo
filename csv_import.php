<?php

require_once('zoo.inc');

$g = $zoo->load_where('quiz_group',"name='Jamaica fish'")[0];

$file = "D:/wamp/data/zoo/scratch/fish/jamaica.csv";

echo "<pre>";

if (($handle = fopen($file, "r")) !== FALSE) {
 while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
  $num = count($data);
  if ($num >= 9) {
   $x = $zoo->new_object('species');
   $x->common_name        = $data[0];
   $x->stj_file_name      = $data[1];
   $x->genus              = $data[2];
   $x->species            = $data[3];
   $x->fishbase_id        = $data[4];
   $x->fishbase_family_id = $data[5];
   $x->order              = $data[6];
   $x->class              = $data[7];
   $x->family             = $data[8];
   $x->save();
   $y = $zoo->new_object('data_record');
   $y->species_id = $x->id;
   $y->data_source_id = 5;
   $y->external_id = $x->fishbase_id;
   $y->save();
   $g->add_member($x->id);
   echo $x->common_name . PHP_EOL;
   //   $x0 = clone($x); $x0->parent = null; var_dump($x0); echo PHP_EOL;
  }
 }
}



?>
