<?php

global $zoo;

$zoo->add_table(
 'species','species',		
 array(		
  'id' => array('type' => 'integer','notnull' => 'true'),
  'class' => array('type' => 'text'),
  'order' => array('type' => 'text'),
  'family' => array('type' => 'text'),
  'genus' => array('type' => 'text'),
  'species' => array('type' => 'text'),
  'common_name' => array('type' => 'text'),
  'common_group' => array('type' => 'text'),
  'eol_id' => array('type' => 'text'),
  'wikipedia_url' => array('type' => 'text'),
  'fishbase_id' => array('type' => 'text'),
  'photo' => array('type' => 'text')
 ),
 array(
  'binomial' => array('type' => 'text')
 ),
 <<<SQL
SELECT
 x.id,
 x.class,
 x.order,
 x.family,
 x.genus,
 x.species,
 CONCAT(x.genus,' ',x.species) AS binomial,
 x.common_name,
 x.common_group,
 x.eol_id,
 x.wikipedia_url,
 x.fishbase_id,
 x.photo
FROM tbl_species x
WHERE %s
 ORDER BY x.class,x.order,x.family,x.genus,x.species,x.id
SQL
);

class species extends frog_object {
 function __construct($id = null,$with_defaults=1) {
  global $zoo;

  parent::__construct($zoo,'species',$id,$with_defaults);
 }

 function fishbase_url() {
  if ($this->fishbase_id) {
   return 'http://www.fishbase.org/summary/'.
    $this->fishbase_id;
  } else {
   return '';
  }
 }

 function eol_url() {
  if ($this->eol_id) {
   return 'http://www.eol.org/pages/'.
    $this->eol_id;
  } else {
   return '';
  }
 }

 function info_url() {
  if ($this->fishbase_id) {
   return $this->fishbase_url();
  } else if ($this->eol_id) {
   return $this->eol_url();
  } else if ($this->wikipedia_url) {
   return $this->wikipedia_url;
  } else {
   return '';
  }
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

  $this->images = $zoo->load_where('images',"species_id={$this->id}");
 }
}

?>