<?php

class zoo_class extends frog {
 function make_species_index($force = 0) {

  if (isset($this->species_index) && ! $force) {
   return $this->species_index;
  }
  
  $this->all_species = $this->load_all('species');
  $this->species_index = array();

  foreach($this->all_species as $s) {
   $a = strtolower($s->genus);
   $b = strtolower($s->species);
   if (! isset($this->species_index[$a])) {
    $this->species_index[$a] = array();
   }
   $this->species_index[$a][$b] = $s;
  }

  return $this->species_index;
 }

 function load_data_sources() {
  $ss = $this->load_all('data_sources');
  $this->data_sources = array();
  foreach($ss as $s) {
   $this->data_sources[$s->id] = $s->subclass();
  }
 }
 
 function find_species($g,$s) {
  if (! isset($this->species_index)) {
   $this->make_species_index();
  }

  $gg = strtolower(trim($g));
  $ss = strtolower(trim($s));

  if (! isset($this->species_index[$gg])) {
   return null;
  }
  
  if (! isset($this->species_index[$gg][$ss])) {
   return null;
  }

  return $this->species_index[$gg][$ss];
 }
}

?>