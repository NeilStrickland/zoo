<?php

global $zoo;

$zoo->add_table(
 'sound','sounds',		
 array(		
  'id' => array('type' => 'integer','notnull' => 'true'),
  'species_id' => array('type' => 'integer','link' => 'species'),
  'file_name' => array('type' => 'text'),
  'extension' => array('type' => 'text')
 ),
 array(
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
 x.species_id,
 x.file_name,
  x.extension,
 s.class,
 s.`order`,
 s.family,
 s.genus,
 s.species,
 (s.genus || ' ' || s.species) AS binomial,
 s.common_name,
 s.common_group
FROM tbl_sounds x
 LEFT JOIN tbl_species s ON x.species_id = s.id
WHERE %s
 ORDER BY s.class,s.family,s.genus,s.species,s.id,x.id
SQL
);

class sound extends frog_object {
 public $id,$object,$species_id,$file_name,$extension,
  $class,$order,$family,$genus,$species,$binomial,$common_name,$common_group;

 function __construct($id = null,$with_defaults=1) {
  global $zoo;

  parent::__construct($zoo,'sound',$id,$with_defaults);
 }

 function full_file_name() {
  global $zoo;
  return $zoo->sound_dir . '/' . $this->file_name;
 }

 function mime_type() {
  switch ($this->extension) {
   case 'mp3': return 'audio/mpeg';
   case 'ogg': return 'audio/ogg';
   case 'wav': return 'audio/wav';
   default: return 'audio/mpeg';
  }
 }

 function url() {
  return "/zoo/send/send_sound.php?id={$this->id}";
 }

 function audio($id = null) {
  if (!$id) { $id = 'sound_' . $this->id; }
  return '<audio controls id="' . $id . '"><source src="' . $this->url() . '"/></audio>';
 }
}

?>
