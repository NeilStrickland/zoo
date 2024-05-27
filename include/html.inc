<?php

class zoo_html extends frog_html {
 function __construct() {
  global $zoo;

  parent::__construct($zoo);

  $this->icons_url = 'icons';
 }

 function data_source_selector($field_name,$id='',$opts=null) {
  global $zoo;

  $sources = $zoo->load_all('data_source');
  $oo = ['mode' => 'indirect', 'index' => 'id', 'display' => 'name', 'empty_option' => true]; 
  return $this->selector($field_name,$sources,$id,$oo);
 }

 function species_selector($field_name,$id='',$opts=null) {
  $v = '';
  if ($id) {
   if (is_object($id)) {
    $p = $id;
   } else {
    $p = $this->frog->load('species',$id);
   }
   if ($p) {
    $v = $p->binomial;
    if ($p->common_name) {
     $v .= ' (' . $p->common_name . ')';
    }
   }
  }
  return $this->ajax_selector('species',$field_name,$id,$v,$opts);
 }

 function taxon_selector($field_name,$id='',$opts=null) {
  $v = '';
  if ($id) {
   if (is_object($id)) {
    $p = $id;
   } else {
    $p = $this->frog->load('taxon',$id);
   }
   if ($p) {
    $v = $p->name . ' (' . $p->rank . ')';
   }
  }
  return $this->ajax_selector('taxon',$field_name,$id,$v,$opts);
 }
}