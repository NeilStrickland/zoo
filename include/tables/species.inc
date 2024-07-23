<?php

global $zoo;

$zoo->add_table(
 'species','species',		
 array(		
  'id' => array('type' => 'integer','notnull' => 'true'),
  'kingdom' => array('type' => 'text'),
  'phylum' => array('type' => 'text'),
  'class' => array('type' => 'text'),
  'order' => array('type' => 'text'),
  'family' => array('type' => 'text'),
  'genus' => array('type' => 'text'),
  'species' => array('type' => 'text'),
  'common_name' => array('type' => 'text'),
  'common_group' => array('type' => 'text')
 ),
 array(
  'binomial' => array('type' => 'text')
 ),
 <<<SQL
SELECT
 x.id,
 x.kingdom,
 x.phylum,
 x.class,
 x.`order`,
 x.family,
 x.genus,
 x.species,
 (x.genus || ' ' || x.species) AS binomial,
 x.common_name,
 x.common_group
FROM tbl_species x
WHERE %s
 ORDER BY x.class,x.`order`,x.family,x.genus,x.species,x.id
SQL
);

class species extends frog_object {
 public $id,$object,$kingdom,$phylum,$class,$order,$family,$genus,$species,
  $binomial,$common_name,$common_group,$images,$sounds,$quiz_groups,
  $data_records,$data_records_by_source,$data_records_by_code,$species_id,
  $external_id,$photos;
  
 function __construct($id = null,$with_defaults=1) {
  global $zoo;

  parent::__construct($zoo,'species',$id,$with_defaults);
 }

 function descriptor() {
  if ($this->genus && $this->species) {
   $s =  $this->genus . ' ' . $this->species;
   if ($this->common_name) {
    $s .= ' (' . $this->common_name . ')';
   }
   return $s;
  } else if ($this->common_name) {
   return $this->common_name;
  } else if ($this->id) {
   return 'Species ' . $this->id;
  } else {
   return 'New species';
  }
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

 function load_images() {
  global $zoo;

  if ($this->id) {
   $this->images = $zoo->load_where('images',"species_id={$this->id}");
  } else {
   $this->images = [];   
  } 
  return $this->images;
 }

 function load_sounds() {
  global $zoo;

  if ($this->id) {
   $this->sounds = $zoo->load_where('sounds',"species_id={$this->id}");
  } else {
   $this->sounds = [];
  }
  return $this->sounds;
 }

 function load_quiz_groups() {
  global $zoo;

  if ($this->id) {
   $this->quiz_groups = $zoo->load_where('quiz_group_membership',"species_id={$this->id}");
  } else {
   $this->quiz_groups = [];
  }
  return $this->quiz_groups;
 }

 function load_data_records() {
  global $zoo;

  if ($this->id) {
   $rr = $zoo->load_where('data_records',"species_id={$this->id}");
   $this->data_records = array();
   foreach($rr as $r) {
    $this->data_records[] = $r->subclass();
   }
  } else {
   $this->data_records = [];
  }
  
  $this->data_records_by_source =
   make_index($this->data_records,'data_source_id');
  $this->data_records_by_code =
   make_index($this->data_records,'data_source_code');

  return $this->data_records;
 }

 function load_photos() {
  global $zoo;

  if ($this->id) {
   $this->photos = $zoo->load_where('photo_species',"species_id={$this->id}");
  } else {
   $this->photos = [];   
  } 
  return $this->photos;
 }

 function load_associated() {
  $this->load_images();
  $this->load_sounds();
  $this->load_quiz_groups();
  $this->load_data_records();
  $this->load_photos();
 }

 function add_data_record($data_source_id,$external_id) {
  global $zoo;
  
  if (! isset($this->data_records)) {
   $this->load_data_records();
  }
  
  $rr = $this->data_records;
  $this->data_records = array();
  $found = false;
  foreach($rr as $r) {
   if ($r->data_source_id == $data_source_id) {
    if ($r->external_id != $external_id) {
     $r->external_id = $external_id;
     $r->save();
    }
    $found = true;
   }
   $this->data_records[] = $r;
   $this->data_records_by_source[$data_source_id] = $r;
  }

  if (! $found) {
   $r = $zoo->new_object('data_record');
   $r->species_id = $this->id;
   $r->data_source_id = $data_source_id;
   $r->external_id = $external_id;
   $r->save();
   $r->load();
   $this->data_records_by_source[$data_source_id] = $r;
  }
 }

 function find_data_records() {
  global $zoo;

  if (! isset($this->data_records)) {
   $this->load_data_records();
  }

  foreach($zoo->data_sources as $source_id => $x) {
   if (! isset($this->data_records[$x->id])) {
    $i = $x->find_external_id($this->genus,$this->species);
    if ($i) {
     $this->add_data_record($source_id,$i);
    }
   }
  }
 }

 function add_image($filename) {
  global $zoo;
  
  $i = $zoo->new_object('image');
  $i->species_id = $this->id;
  $i->save();
  $i->file_name = 'image_' . $i->id . '.jpg';
  $i->save();
  $ext = strtolower(pathinfo($filename,PATHINFO_EXTENSION));
  if ($ext == 'jpg' || $ext == 'jpeg') {
   rename($filename,$zoo->image_dir . '/' . $i->file_name);
  } else if ($ext == 'webp') {
   $im = imagecreatefromwebp($filename);
   imagejpeg($im,$zoo->image_dir . '/' . $i->file_name);
   imagedestroy($im);
   unlink($filename);
  }

  copy($zoo->image_dir . '/' . $i->file_name,
       $zoo->original_images_dir . '/' . $i->file_name);
  $y = new stdClass();
  $y->original_location = $filename;
  $y->species_id = $this->id;
  $y->species_binomial = $this->binomial;
  $y->species_common_name = $this->common_name;
  $y->image_id = $i->id;
  return $y;
 }

 function add_sound($filename) {
  global $zoo;
  
  $i = $zoo->new_object('sound');
  $i->species_id = $this->id;
  $i->save();
  $ext = strtolower(pathinfo($filename,PATHINFO_EXTENSION));
  $i->file_name = 'sound_' . $i->id . '.' . $ext;
  $i->save();
  rename($filename,$zoo->sound_dir . '/' . $i->file_name);
  $y = new stdClass();
  $y->original_location = $filename;
  $y->species_id = $this->id;
  $y->species_binomial = $this->binomial;
  $y->species_common_name = $this->common_name;
  $y->image_id = $i->id;
  $y->file_name = $i->file_name;
  return $y;
 }

 function wiki_file_name() {
  global $zoo;

  return $zoo->wiki_file_name($this->genus,$this->species);
 }
 
 function wiki_file_contents() {
  global $zoo;

  return $zoo->wiki_file_contents($this->genus,$this->species);  
 }

 function wiki_url() {
  global $zoo;

  return $zoo->wiki_url($this->genus,$this->species);
 }

 function fetch_wiki() {
  global $zoo;

  return $zoo->fetch_wiki($this->genus,$this->species);
 }

}

?>
