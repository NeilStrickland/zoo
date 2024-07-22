<?php

global $zoo;

$zoo->add_table(
 'photo_species','photo_speciess',		
 array(		
  'id' => array('type' => 'integer','notnull' => 'true'),
  'photo_id' => array('type' => 'integer','link' => 'photo'),
  'species_id' => array('type' => 'integer','link' => 'species')
 ),
 array(
  'photo_dir' => array('type' => 'text'),
  'photo_file_name' => array('type' => 'text'),
  'photo_date' => array('type' => 'text'),
  'photo_location' => array('type' => 'text'),
  'photo_lat' => array('type' => 'float'),
  'photo_lng' => array('type' => 'float'),
  'photo_description' => array('type' => 'text'),
  'class' => array('type' => 'text'),
  'order' => array('type' => 'text'),
  'family' => array('type' => 'text'),
  'genus' => array('type' => 'text'),
  'species' => array('type' => 'text'),
  'binomial' => array('type' => 'text'),
  'common_name' => array('type' => 'text'),
  'common_group' => array('type' => 'text')
 ),
 <<<SQL
SELECT
 x.id,
 x.photo_id,
 p.dir AS photo_dir,
 p.file_name AS photo_file_name,
 p.date AS photo_date,
 p.location AS photo_location,
 p.lat AS photo_lat,
 p.lng AS photo_lng,
 p.description AS photo_description,
 x.species_id,
 s.class,
 s.`order`,
 s.family,
 s.genus,
 s.species,
 (s.genus || ' ' || s.species) AS binomial,
 s.common_name,
 s.common_group
FROM tbl_photo_speciess x
 LEFT JOIN tbl_photos p ON x.photo_id = p.id
  LEFT JOIN tbl_species s ON x.species_id = s.id
WHERE %s
 ORDER BY p.date,p.file_name,p.id,x.id
SQL
);

class photo_species extends frog_object {
 public $id,$object,$photo_id,$photo_dir,$photo_file_name,$photo_date,$photo_location,$photo_lat,$photo_lng,$photo_description,
  $species_id,$class,$order,$family,$genus,$species,$binomial,$common_name,$common_group;
  
 function __construct($id = null,$with_defaults=1) {
  global $zoo;

  parent::__construct($zoo,'photo_species',$id,$with_defaults);
 }

 function wiki_url() {
  global $zoo;

  return $zoo->wiki_url($this->genus,$this->species);
 }

 function info_url() {
  // Should be improved to use data records
  return $this->wiki_url();
 }

 function linked_binomial() {
  $u = $this->info_url();
  if ($u) {
   return '<a target="_blank" href="' . $u . '">' .
    $this->binomial . '</a>';
  } else {
   return $this->binomial;
  }
 }

 function linked_species() {
  $u = $this->info_url();
  if ($u) {
   return '<a target="_blank" href="' . $u . '">' .
    $this->species . '</a>';
  } else {
   return $this->species;
  }
 }
}

?>
