<?php

global $zoo;

$zoo->add_table(
 'quiz_group_membership','quiz_group_memberships',		
 array(		
  'id' => array('type' => 'integer','notnull' => 'true'),
  'quiz_group_id' => array('type' => 'integer','link' => 'quiz_group'),
  'species_id' => array('type' => 'integer','link' => 'species')
 ),
 array(
  'quiz_group_name' => array('type' => 'text'),
  'class' => array('type' => 'text'),
  'order' => array('type' => 'text'),
  'family' => array('type' => 'text'),
  'genus' => array('type' => 'text'),
  'species' => array('type' => 'text'),
  'binomial' => array('type' => 'text'),
  'common_name' => array('type' => 'text'),
  'common_group' => array('type' => 'text'),
  'eol_id' => array('type' => 'text'),
  'fishbase_id' => array('type' => 'text'),
  'wikipedia_url' => array('type' => 'text'),
  'photo' => array('type' => 'text')
 ),
 <<<SQL
SELECT
 x.id,
 x.quiz_group_id,
 g.name AS quiz_group_name,
 x.species_id,
 s.class,
 s.order,
 s.family,
 s.genus,
 s.species,
 CONCAT(s.genus,' ',s.species) AS binomial,
 s.common_name,
 s.common_group,
 s.eol_id,
 s.fishbase_id,
 s.wikipedia_url,
 s.photo
FROM tbl_quiz_group_memberships x
 LEFT JOIN tbl_quiz_groups g ON x.quiz_group_id = g.id
  LEFT JOIN tbl_species s ON x.species_id = s.id
WHERE %s
 ORDER BY s.class,s.order,s.family,s.genus,s.species,s.id,x.id
SQL
);

class quiz_group_membership extends frog_object {
 function __construct($id = null,$with_defaults=1) {
  global $zoo;

  parent::__construct($zoo,'quiz_group_membership',$id,$with_defaults);
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