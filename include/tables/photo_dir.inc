<?php

global $zoo;

$zoo->add_table(
 'photo_dir','photo_dirs',		
 array(		
  'id' => array('type' => 'integer','notnull' => 'true'),
  'name' => array('type' => 'text')
 ),
 array(
 ),
 <<<SQL
SELECT
 x.id,
 x.name
FROM tbl_photo_dirs x
WHERE %s
 ORDER BY x.name
SQL
);

class photo_dir extends frog_object {
 public $id,$name,
  $photos,$ignored_photos,$unclassified_photos,$photos_by_name,$photos_by_id,
  $species,$species_by_id,$species_by_binomial;

 function __construct($id = null,$with_defaults=1) {
  global $zoo;

  parent::__construct($zoo,'photo_dir',$id,$with_defaults);
 }

 function full_name() {
  global $zoo;
  return $zoo->public_pictures_dir . '/' . $this->name;
 }
 
 function is_available() {
  return is_dir($this->full_name());
 }

 function load_photos() {
  global $zoo;

  $photos = $zoo->load_where('photos',"x.dir='{$this->name}'");
  $this->photos = [];
  $this->photos_by_name = [];
  $this->photos_by_id = [];
  $this->ignored_photos = [];

  foreach($photos as $p) {
   $p->species = [];
   if ($p->ignore) {
    $this->ignored_photos[] = $p;
   } else{
    $this->photos[] = $p;
    $this->photos_by_name[$p->file_name] = $p;
    $this->photos_by_id[$p->id] = $p; 
   }
  }
 }

 function load_species() {
  global $zoo;
  if (! isset($this->photos)) {
   $this->load_photos();
  }

  $ids = array_keys($this->photos_by_id);
  $ids = implode(',',$ids);
  $pss = $zoo->load_where('photo_species',"x.photo_id IN ({$ids})");
  $this->species_by_id = [];

  foreach($pss as $p) {
   $this->photos_by_id[$p->photo_id]->species[] = $p;
   if (isset($this->species_by_id[$p->species_id])) {
    $s = $this->species_by_id[$p->species_id];
    $s->extra->photos[] = $p;
   } else {
    $s = $zoo->load('species',$p->species_id);
    $this->species_by_id[$p->species_id] = $s;
    $s->extra->photos = [$p];
   }
  }
  $this->species = array_values($this->species_by_id);
  $this->species_by_binomial = make_index($this->species,'binomial');

  $this->unclassified_photos = [];
  foreach($this->photos as $p) {
   if (! $p->species) {
    $this->unclassified_photos[] = $p;
   }
  }
 }
}

?>
