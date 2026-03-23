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
    $v = $p->name . ' (' . $p->trank . ')';
   }
  } else if (is_array($opts)) {
   if (isset($opts['taxon']) && isset($opts['rank'])) {
    $v = $opts['taxon'] . ' (' . $opts['rank'] . ')';
   }
  }
  return $this->ajax_selector('taxon',$field_name,$id,$v,$opts);
 }

 function ranked_taxon_selector($field_name,$name='',$trank='family',$opts=null) {
  return $this->ajax_selector($trank,$field_name,$name,$name,$opts);
 }

 function kingdom_selector($field_name,$name='',$opts=null) {
  return $this->ranked_taxon_selector($field_name,$name,'kingdom',$opts);
 }

 function phylum_selector($field_name,$name='',$opts=null) {
  return $this->ranked_taxon_selector($field_name,$name,'phylum',$opts);
 }

 function class_selector($field_name,$name='',$opts=null) {
  return $this->ranked_taxon_selector($field_name,$name,'class',$opts);
 }

 function order_selector($field_name,$name='',$opts=null) {
  return $this->ranked_taxon_selector($field_name,$name,'order',$opts);
 }

 function family_selector($field_name,$name='',$opts=null) {
  return $this->ranked_taxon_selector($field_name,$name,'family',$opts);
 }

 function trank_selector($field_name,$trank='',$opts=null) {
  $tranks = ['','kingdom','phylum','class','order','family','genus'];
  return $this->selector($field_name,$tranks,$trank,$opts);
 }

 function location_selector($field_name,$location='',$opts=null) {
  return $this->ajax_selector('location',$field_name,$location,$location,$opts);
 }

 function quiz_group_selector($field_name,$id='',$opts=null) {
  $v = '';
  if ($id) {
   if (is_object($id)) {
    $p = $id;
   } else {
    $p = $this->frog->load('quiz_group',$id);
   }
   if ($p) {
    $v = $p->name;
   }
  }
  return $this->ajax_selector('quiz_group',$field_name,$id,$v,$opts);
 }

 function photo_dir_selector($field_name,$name='',$opts=null) {
  global $zoo;
  $oo = ['include_unused' => false, 'include_ignored' => false, 
         'include_unavailable' => false, 'show_counts' => false];

  foreach($oo as $k => $v) {
   if (! isset($opts[$k])) {
    $opts[$k] = $oo[$k];
   }
  }

  if (! isset($zoo->all_photo_dirs)) {
   $zoo->load_photo_dirs();
  }

  if ($opts['show_counts']) {
   $zoo->collate_photo_dirs();
  }

  $dirs = [];
  foreach($zoo->all_photo_dirs as $name => $d) {
   if ($d->ignore && ! $opts['include_ignored']) {
    continue;
   }

   if (! $opts['include_unavailable'] && ! $d->is_available()) {
    continue;
   }

   $dirs[$name] = $d;
  }

  if ($opts['include_unused']) {
   foreach($zoo->fs_photo_dirs as $name => $d) {
    if ($d->ignore && ! $opts['include_ignored']) {
     continue;
    }

    if (! isset($dirs[$name])) {
     $dirs[$name] = $d;
    }
   }
  }

  $vals = [];

  foreach($dirs as $name => $d) {
   $display = $name;
   if ($opts['show_counts'] && isset($d->photo_count)) {
    $display .= ' (' . $d->photo_count . ' | ' . $d->unclassified_count . ')';
   }
   $vals[$name] = $display;
  }

  $opts['mode'] = 'direct';
  $opts['empty_option'] = true;
  return $this->selector($field_name,$vals,$name,$opts);
 }
}