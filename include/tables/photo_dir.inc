<?php

global $zoo;

$zoo->add_table(
 'photo_dir','photo_dirs',		
 array(		
  'id' => array('type' => 'integer','notnull' => 'true'),
  'name' => array('type' => 'text'),
  'reference_page' => array('type' => 'text'),
  'ignore' => array('type' => 'boolean'),
 ),
 array(
 ),
 <<<SQL
SELECT
 x.id,
 x.name,
 x.reference_page,
 x.ignore
FROM tbl_photo_dirs x
WHERE %s
 ORDER BY x.name
SQL
);

class photo_dir extends frog_object {
 public $id,$name,$reference_page,$ignore,
  $photos,$ignored_photos,$unclassified_photos,$photos_by_name,$photos_by_id,
  $photo_count,$unclassified_count,
  $species,$species_by_id,$species_by_binomial,$species_counts;

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

 function load_photos($scan = false) {
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

  if ($scan) {
   $this->scan_photos();
  }
 }

 function scan_photos() {
  global $zoo;

  if (! isset($this->photos_by_id)) {
   $this->load_photos();
  }

  $dir = $this->full_name();
  $photos0 = scandir($dir);
  $new_photos = [];
  foreach ($photos0 as $f) {
   $ff = $dir . '/' . $f;
   $e = strtolower(pathinfo($ff, PATHINFO_EXTENSION));
   if (is_file($ff) && ($e == 'jpg' || $e == 'jpeg')) {
    if (! isset($this->photos_by_name[$f])) {
     $p = $zoo->new_object('photo');
     $p->dir = $this->name;
     $p->file_name = $f;
     $p->get_exif();
     $p->save();
     $new_photos[] = $p;
    }
   }
  }

  if (count($new_photos) > 0) {
   $this->load_photos();
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
