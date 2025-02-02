<?php

global $zoo;

$zoo->add_table(
 'photo','photos',		
 array(		
  'id' => array('type' => 'integer','notnull' => 'true'),
  'dir' => array('type' => 'text'),
  'file_name' => array('type' => 'text'),
  'date' => array('type' => 'text'),
  'location' => array('type' => 'text'),
  'lat' => array('type' => 'float'),
  'lng' => array('type' => 'float'),
  'description' => array('type' => 'text'),
 ),
 array(
 ),
 <<<SQL
SELECT
 x.id,
 x.dir,
 x.file_name,
 x.date,
 x.location,
 x.lat,
 x.lng,
 x.description
FROM tbl_photos x
WHERE %s
 ORDER BY x.date,x.file_name,x.id
SQL
);

class photo extends frog_object {
 public $id,$dir,$file_name,$date,$location,$lat,$lng,$description,
  $species,$species_by_species_id;

 function __construct($id = null,$with_defaults=1) {
  global $zoo;

  parent::__construct($zoo,'photo',$id,$with_defaults);
 }

 function full_file_name() {
  global $zoo;
  return $zoo->public_pictures_dir . '/' . $this->dir . '/' . $this->file_name;
 }
 
 function url() {
  return "/zoo/send/send_photo.php?id={$this->id}";
 }

 function img() {
  return '<img src="' . $this->url() . '"/>';
 }

 function small_img() {
  return '<img width="180" src="' . $this->url() . '"/>';
 }

 function tiny_img() {
  return '<img width="90" src="' . $this->url() . '"/>';
 }

 function load_species() {
  global $zoo;

  $this->species = [];
  if ($this->id) {
   $this->species = $zoo->load_where('photo_species',"x.photo_id={$this->id}");
  }
  $this->species_by_species_id = make_index($this->species,'species_id');
 }

 function add_species($species_id) {
  global $zoo;

  if (! isset($this->species)) {
   $this->load_species();
  }

  if (! isset($this->species_by_species_id[$species_id])) {
   $x = $zoo->new_object('photo_species');
   $x->photo_id = $this->id;
   $x->species_id = $species_id;
   $x->save();
   $x->load();
   $this->species[] = $x;
   $this->species_by_species_id[$species_id] = $x;
  }
 }

 function parse_species() {
  global $zoo;
  $x = strtolower($this->description);
  $x = strtr($x,',',' + ');
  $x = strtr($x,"'","\\'");
  $x = array_map('trim',explode('+',$x));
  foreach($x as $y) {
   $ss = $zoo->load_where('species',"LOWER(x.common_name)='{$y}'");
   $w = explode(' ',$y);
   if (! $ss && count($w) == 2) {
    $ss = $zoo->load_where('species',"LOWER(x.genus)='{$w[0]}' AND LOWER(x.species)='{$w[1]}'");
   }
   if (count($ss) == 1) {
    $this->add_species($ss[0]->id);
   }
  }
 }
}

?>
