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
 public $id,$name,$members,$members_by_id,$members_by_species_id,$members_by_binomial;
 
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

  if (isset($this->members_by_species_id[$species_id])) {
   return 0;
  } else {
   $m = $zoo->new_object('quiz_group_membership');
   $m->quiz_group_id = $this->id;
   $m->species_id = $species_id;
   $m->save();
   $this->load_members();
   return 1;
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

 // Returns 0 if not found, 1 if already present, 2 if new
 function add_member_by_name($g,$s) {
  global $zoo;
  
  if (! isset($this->members)) {
   $this->load_members();
  }

  $x = $zoo->find_species($g,$s);
  if ($x) {
   $y = $this->add_member($x->id);
   return 1 + $y; 
  } else {
   return 0;
  }
 }

 function add_members_from_file($file) {
  $x = new stdClass;
  $x->not_found = array();
  $x->already_present = array();
  $x->newly_added = array();

  $handle = fopen($file,'r');

  if ($handle === FALSE) {
   return null;
  }
  
  $lines = explode("\n",file_get_contents($file));
  while(($fields = fgetcsv($handle)) !== FALSE) {
   if (count($fields) >= 2) {
    $y = new stdClass;
    $y->genus = $fields[0];
    $y->species = $fields[1];
    $y->status = $this->add_member_by_name($y->genus,$y->species);

    if ($y->status == 0) {
     $x->not_found[] = $y;
    } else if ($y->status == 1) {
     $x->already_present[] = $y;
    } else {
     $x->newly_added[] = $y;
    }
   }
  }

  return $x;
 }
}

?>
