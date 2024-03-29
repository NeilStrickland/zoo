<?php

global $zoo;

$zoo->add_table(
 'taxon','taxa',		
 array(		
  'id' => array('type' => 'integer','notnull' => 'true'),
  'parent_id' => array('type' => 'integer', 'link' => 'taxon'),
  'rank' => array('type' => 'text'),
  'name' => array('type' => 'text')
 ),
 array(
  'parent_name' => array('type' => 'text'),
  'grandparent_name' => array('type' => 'text')
 ),
 <<<SQL
SELECT
 x.id,
 x.parent_id,
 x.rank,
 x.name,
 y.name AS parent_name,
 z.name AS grandparent_name
FROM tbl_taxa x
 LEFT JOIN tbl_taxa y ON x.parent_id=y.id
  LEFT JOIN tbl_taxa z ON y.parent_id=z.id
WHERE %s
 ORDER BY x.rank,x.name,x.id
SQL
);

class taxon extends frog_object {
 public $id,$parent_id,$rank,$name,$parent_name,$grandparent_name,
  $parent, $grandparent, $children, $species;
 
 static $ranks = ['kingdom','phylum','class','order','family','genus'];

 function __construct($id = null,$with_defaults=1) {
  global $zoo;

  parent::__construct($zoo,'taxon',$id,$with_defaults);
 }

 function rank_number() {
  return array_search($this->rank,taxon::$ranks);
 }

 function parent_rank() {
  $r = $this->rank_number();
  if ($r > 0) {
   return taxon::$ranks[$r-1];
  }
  return null;
 }

 function load_species() {
  global $zoo;

  $q = "x.{$this->rank}='{$this->name}'";
  $this->species = $zoo->load_where('species',$q);
  return $this->species;
 }

 function load_children() {
  global $zoo;

  $q = "x.parent_id={$this->id}";
  $this->children = $zoo->load_where('taxon',$q);
  return $this->children;
 }

 function get_parent() {
  global $zoo;

  $this->parent = null;
  if ($this->parent_id) {
   $this->parent = $zoo->load('taxon',$this->parent_id);
  }
  return $this->parent;
 }

 function find_parent() {
  global $zoo;

  $r = $this->rank_number();
  $x = new stdClass();
  $x->parent_name = null;
  $x->parent_id = null;
  $x->parent = null;
  $x->votes = [];
  $x->error = null;

  if (! $r) {
   $x->error = 'No rank';
   return $x;
  }
  $p = $this->parent_rank();
  $species = $this->load_species();
  if (! $species) {
   $x->error = 'No species';
   return $x;
  }
  foreach ($species as $s) {
   $v = $s->$p;
   if (! $v) { continue; }
   if (! isset($x->votes[$v])) {
    $x->votes[$v] = 0;
   }
   $x->votes[$v]++;
  }
  $ks = array_keys($x->votes);
  if (count($ks) == 1) {
   $x->parent_name = trim($ks[0]);
   $pp = $zoo->load_where('taxon',"x.rank='$p' AND x.name='{$x->parent_name}'");
   if (count($pp) == 1) {
    $x->parent = $pp[0];
    $x->parent_id = $pp[0]->id;
   } else if (count($pp) == 0) {
    $x->error = 'Parent not found';
   } else {
    $x->error = 'Multiple parents with same rank and name';
   }
  } else if (count($ks) == 0) {
   $x->error = 'No parents';
  } else {
   $x->error = 'Multiple parents';
  }
  return $x;
 }

 function chain() {
  $x = new stdClass();
  foreach (taxon::$ranks as $r) {
   $x->$r = '';
  }
  $t = $this;
  while ($t) {
   $r = $t->rank;
   $x->$r = $t->name;
   $t = $t->get_parent();
  }
  return $x;
 }

 static function compare_chains($a,$b) {
  foreach (taxon::$ranks as $r) {
   if ($a->$r != $b->$r) {
    return $a->$r <=> $b->$r;
   }
  }
  return 0;
 }
}

?>
