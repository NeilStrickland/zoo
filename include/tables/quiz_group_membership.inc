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
  'common_group' => array('type' => 'text')
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
 s.common_group
FROM tbl_quiz_group_memberships x
 LEFT JOIN tbl_quiz_groups g ON x.quiz_group_id = g.id
  LEFT JOIN tbl_species s ON x.species_id = s.id
WHERE %s
 ORDER BY s.class,s.order,s.family,s.genus,s.species,s.id,x.id
SQL
);

class quiz_group_membership extends frog_object {
 public $id,$object,$quiz_group_id,$species_id,$quiz_group_name,
  $class,$order,$family,$genus,$species,$binomial,$common_name,$common_group;
  
 function __construct($id = null,$with_defaults=1) {
  global $zoo;

  parent::__construct($zoo,'quiz_group_membership',$id,$with_defaults);
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
