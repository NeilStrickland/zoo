<?php

global $zoo;

$zoo->add_table(
 'quiz_group','quiz_groups',		
 array(		
  'id' => array('type' => 'integer','notnull' => 'true'),
  'name' => array('type' => 'text')
 ),
 array(),
 <<<SQL
SELECT
 x.id,
 x.name
FROM tbl_quiz_groups x
WHERE %s
 ORDER BY x.name,x.id
SQL
);

class quiz_group extends frog_object {
 function __construct($id = null,$with_defaults=1) {
  global $zoo;

  parent::__construct($zoo,'quiz_group',$id,$with_defaults);
 }

 function load_members() {
  global $zoo;
  
  $this->members =
   $zoo->load_where('quiz_group_memberships',
		    "x.quiz_group_id={$this->id}");

  $this->members_by_id =
   make_index($this->members,'id');

  $this->members_by_species_id =
   make_index($this->members,'species_id');

  $this->members_by_binomial = array();
  foreach ($this->members as $m) {
   $this->members_by_binomial[strtolower($m->binomial)] = $m;
  }

  $ids = array();
  foreach ($this->members as $m) {
   $ids[] = $m->species_id;
   $m->images = array();
  }

  $ids = implode(',',$ids);
  if ($ids) {
   $images = $zoo->load_where('images',"species_id IN ($ids)");
   foreach($images as $i) {
    $this->members_by_species_id[$i->species_id]->images[] = $i;
   }
  }

  return $this->members;
 }

 function add_member($species_id) {
  global $zoo;
  
  if (! isset($this->members)) {
   $this->load_members();
  }

  if (! isset($this->members_by_species_id[$species_id])) {
   $m = $zoo->new_object('quiz_group_membership');
   $m->quiz_group_id = $this->id;
   $m->species_id = $species_id;
   $m->save();
   $this->load_members();
  }
 }

 function remove_member($species_id) {
  global $zoo;
  
  if (! isset($this->members)) {
   $this->load_members();
  }

  if (isset($this->members_by_species_id[$species_id])) {
   $m = $this->members_by_species_id[$species_id];
   $m->delete();
   $this->load_members();
  }  
 }

 function add_member_by_name($g,$s) {
  global $zoo;
  
  if (! isset($this->members)) {
   $this->load_members();
  }

  $x = $zoo->find_species($g,$s);
  if ($x) {
   $this->add_member($x->id);
   return 1;
  } else {
   return 0;
  }
 }

 function add_members_from_file($file) {
  $lines = explode("\n",file_get_contents($file));
  foreach($lines as $line) {
   $fields = explode(",",trim($line));
   if (count($fields) == 2) {
    $this->add_member_by_name($fields[0],$fields[1]);
   }
  }
 }
}

?>