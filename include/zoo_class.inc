<?php

class zoo_class extends frog {
 public
  $species_index,$all_species,$data_sources,$data_sources_by_code,$data_sources_by_name,
  $data_dir,
  $temp_images_dir,$image_dir,$image_url,$image_url_base,
  $temp_sounds_dir,$sound_dir,$sound_url,$sound_url_base,
  $global_data_dir,$original_images_dir,$html,$nav;

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
  $this->data_sources_by_code = array();
  $this->data_sources_by_name = array();
  
  foreach($ss as $s) {
   $x = $s->subclass();
   $this->data_sources[$s->id] = $x;
   $this->data_sources_by_code[$s->code] = $x;
   $this->data_sources_by_name[$s->name] = $x;
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

 function attach_images($cleanup = 0) {
  $ix = $this->make_species_index();
  $files = scandir($this->temp_images_dir);
  $result = array();

  $latest = null;
  $latest_file = $this->temp_images_dir . '/Latest/id.txt';

  if (file_exists($latest_file)) {
   $latest_id = (int) file_get_contents($latest_file);
   $latest = $this->load('species',$latest_id);
  }
  
  foreach($files as $f) {
   $f_full = $this->temp_images_dir . '/' . $f;
   $f0 = strtolower(str_replace('_',' ',$f));

   if (is_dir($f_full)) {
    $x = null;
    if (preg_match('/^([a-z]+) ([a-z]+)$/',$f0,$m)) {
     $g = $m[1];
     $s = $m[2];
     if (isset($ix[$g]) && isset($ix[$g][$s])) {
      $x = $ix[$g][$s];
     }
    } else if ($f == 'Latest' && $latest) {
     $x = $latest;
    }

    if ($x) {
     $subdir_files = scandir($f_full);
     $cwd = getcwd();
     chdir($f_full);
     foreach($subdir_files as $g) {
      $ext = strtolower(pathinfo($g,PATHINFO_EXTENSION));
      if ($ext == 'jpg' || $ext == 'jpeg') {
       $y = $x->add_image($g);
       $result[] = $y;
      }
     }
     chdir($cwd);

     if ($cleanup && count(scandir($f_full)) <= 2) {
      rmdir($f_full);
     }
    }     
   } else if (preg_match('/^([a-z]+) ([a-z]+) ?[0-9]*\.jpg$/',$f0,$m)) {
    $g = $m[1];
    $s = $m[2];
    if (isset($ix[$g]) && isset($ix[$g][$s])) {
     $x = $ix[$g][$s];
     $y = $x->add_image($f_full);
     $result[] = $y;
    }
   }
  }
 }

 function attach_sounds($cleanup = 0) {
  $ix = $this->make_species_index();
  $files = scandir($this->temp_sounds_dir);
  $result = array();

  $latest = null;
  $latest_file = $this->temp_sounds_dir . '/Latest/id.txt';

  if (file_exists($latest_file)) {
   $latest_id = (int) file_get_contents($latest_file);
   $latest = $this->load('species',$latest_id);
  }
  
  foreach($files as $f) {
   $f_full = $this->temp_sounds_dir . '/' . $f;
   $f0 = strtolower(str_replace('_',' ',$f));

   if (is_dir($f_full)) {
    $x = null;
    if (preg_match('/^([a-z]+) ([a-z]+)$/',$f0,$m)) {
     $g = $m[1];
     $s = $m[2];
     if (isset($ix[$g]) && isset($ix[$g][$s])) {
      $x = $ix[$g][$s];
     }
    } else if ($f == 'Latest' && $latest) {
     $x = $latest;
    }

    if ($x) {
     $subdir_files = scandir($f_full);
     $cwd = getcwd();
     chdir($f_full);
     foreach($subdir_files as $g) {
      $ext = strtolower(pathinfo($g,PATHINFO_EXTENSION));
      if ($ext == 'mp3' || $ext == 'wav') {
       $y = $x->add_sound($g);
       $result[] = $y;
      }
     }
     chdir($cwd);

     if ($cleanup && count(scandir($f_full)) <= 2) {
      rmdir($f_full);
     }
    }     
   } else if (preg_match('/^([a-z]+) ([a-z]+) ?[0-9]*\.(mp3|wav)$/',$f0,$m)) {
    $g = $m[1];
    $s = $m[2];
    if (isset($ix[$g]) && isset($ix[$g][$s])) {
     $x = $ix[$g][$s];
     $y = $x->add_image($f_full);
     $result[] = $y;
    }
   }
  }
 }

 function wiki_file_name($genus,$species) {
  return $this->data_dir . '/wikipedia_pages/binomial/' .
                         $genus . '_' . $species . '.html';
 }

 function wiki_file_contents($genus,$species) {
  $f = $this->wiki_file_name($genus,$species);
  if (file_exists($f)) {
   return file_get_contents($f);
  } else {
   return '';
  }
 }
 
 function wiki_url($genus,$species) {
  return 'https://en.wikipedia.org/wiki/' . $genus . '_' . $species;
 }
 
 function fetch_wiki($genus,$species) {
  $url = $this->wiki_url($genus,$species);
  $local_file = $this->wiki_file_name($genus,$species);

  $level = error_reporting(E_ERROR);
  $html = file_get_contents($url);
  error_reporting($level);

  if ($html === FALSE) {
   $html = '';
  }
  
  if (! (strpos($html,"Wikipedia does not have an article with this exact name") === false)) {
    $html = '';
  }

  if ($html) {
   file_put_contents($local_file,$html);
  }
  
  return $html;
 }

 function fetch_wiki_common($key) {
  $full_file = $this->data_dir . '/wikipedia_pages/common/' . $key . '.html';
  $url = 'https://en.wikipedia.org/wiki/' . $key;
  
  $level = error_reporting(E_ERROR);
  $html = file_get_contents($url);
  error_reporting($level);

  if ($html === FALSE) {
   $html = '';
  }
  
  if (! (strpos($html,"Wikipedia does not have an article with this exact name") === false)) {
    $html = '';
  }

  if ($html) {
   file_put_contents($full_file,$html);
  }
  
  return $html;
 }

 function extract_wiki_taxa($html) {
  $doc = new DOMDocument();
  $level = error_reporting(E_ERROR | E_PARSE);
  $doc->loadHTML($html);
  error_reporting($level);

  $xpath = new DOMXpath($doc);
  $box = $xpath->query("//table[@class='infobox biota']/tbody/tr");
  $taxa = new stdClass();
  $taxa->box     = $box;
  $taxa->class   = '';
  $taxa->order   = '';
  $taxa->family  = '';
  $taxa->genus   = '';
  $taxa->species = '';
  $tranks = array('Kingdom','Phylum','Class','Order','Family','Genus','Species');

  if (!is_null($box)) {
   foreach ($box as $row) {
    if ($row->childNodes && $row->childNodes->length > 1) {
     $k = $row->childNodes[0]->nodeValue;
     $v = '';
     for ($i = 1; $i < $row->childNodes->length; $i++) {
      $v = $v . $row->childNodes[$i]->nodeValue;
     }
     $v = trim($v);

     foreach ($tranks as $r) {
      if (trim($k) == $r . ':') {
       $rl = strtolower($r);
       $taxa->$rl = $v;
      }
     }
    }
   }
  }

  if ($taxa->genus && $taxa->species) {
   $p = substr($taxa->genus,0,1) . '. ';
   if (strlen($taxa->species) >= 3 && substr($taxa->species,0,3) == $p) {
    $taxa->species = substr($taxa->species,3);
   }
  }
  
  return $taxa;
 }

 function extract_wiki_common_name($genus,$species,$html) {
  $x = new stdClass();
  $x->common_name = '';
  $x->page_name = '';
  $x->infobox_header = '';
  $x->snippet = '';
  
  $doc = new DOMDocument();
  $level = error_reporting(E_ERROR | E_PARSE);
  $doc->loadHTML($html);
  error_reporting($level);

  if (! $doc) { return $x; }
  
  $xpath = new DOMXpath($doc);
  $box = $xpath->query("//h1");
  if (!is_null($box)) { 
   foreach($box as $row) {
    $x->page_name = trim($row->nodeValue);
    break;
   }
  }

  $xpath = new DOMXpath($doc);
  $box = $xpath->query("//table[@class='infobox biota']/tbody/tr/th");

  if (!is_null($box)) {
   foreach ($box as $row) {
    $x->infobox_header = trim($row->nodeValue);
    break;
   }
  }
  
  $xpath = new DOMXpath($doc);
  $box = $xpath->query("//div[@class='mw-parser-output']/p");
  
  if (!is_null($box)) {
   foreach ($box as $row) {
    $x->snippet = trim($row->nodeValue);
    if ($x->snippet) { break; }
   }
  }

  if (($x->page_name == $genus . ' ' . $species) && $x->infobox_header) {
   $x->common_name = $x->infobox_header;
  } else {
   $x->common_name = $x->page_name;
  }

  return $x;
 }

 function find_repeats() {
  $all_species = $this->load_all('species');

  $species_by_binomial = array();
  $repeats = array();

  foreach ($all_species as $s) {
   if (isset($species_by_binomial[$s->binomial])) {
    $species_by_binomial[$s->binomial][] = $s;
    $repeats[$s->binomial] = 1;
   } else {
    $species_by_binomial[$s->binomial] = array($s);
   }
  }

  return $repeats;
 }

 function resolve_repeats($ss) {
  if (! $ss) { return; }
  
  $id0 = $ss[0]->id;
  $s0 = $ss[0];
 
  foreach ($ss as $s) {
   if ($s->id < $id0) {
    $id0 = $s->id;
    $s0 = $s;
   }
  }

  foreach($ss as $s) {
   if ($s->id == $id0) { continue; }
   $fields = array('class','order','common_name','common_group');

   foreach ($fields as $f) {
    if ($s->$f && ! $s0->$f) { $s0->$f = $s->$f; }
   }

   $s->load_images();
   foreach ($s->images as $i) {
    $i->species_id = $id0;
    $i->save();
   }

   $s->load_data_records();
   foreach($s->data_records as $r) {
    $r->species_id = $id0;
    $r->save();
   }

   $mm = $this->load_where('quiz_group_memberships',"species_id={$s->id}");
   foreach($mm as $m) {
    $m->species_id = $id0;
    $m->save();
   }

   $s->delete();
  }
 } 

 function tabulate_taxa() {
  $taxa = $this->load_all('taxa');
  $taxa_index = [];
  foreach($taxa as $t) {
   $t->is_new = false;
   if (! isset($taxa_index[$t->trank])) {
    $taxa_index[$t->trank] = [];
   }
   $taxa_index[$t->trank][$t->name] = $t;
  }

  $new_taxa = [];
  $species = $this->load_all('species');
  $tranks = taxon::$tranks;
  $nr = count($tranks);
  foreach($species as $s) {
   for($k = 0; $k < $nr; $k++) {
    $r = $tranks[$k];
    $n = $s->$r;
    if ($n && ! isset($taxa_index[$r][$n])) {
     $t = $this->new_object('taxon');
     $t->trank = $r;
     $t->name = $n;
     $t->is_new = true;
     $t->save();
     $taxa_index[$r][$n] = $t;
     $taxa[] = $t;
     $new_taxa[] = $t;
    }
   }
  }

  foreach($taxa as $t) {
   $t->new_parent_id = null;
   if ($t->trank_number() > 0 && ! $t->parent_id) {
    $x = $t->find_parent();
    if (! $x->error) {
     $t->parent_id = $x->parent_id;
     $t->new_parent_id = $x->parent_id;
     $t->save();
    }
   }
  }

  return $taxa;
 }

 function taxa_tree() {
  $taxa = $this->load_all('taxa');
  $tree = [];

  foreach($taxa as $t) {
   $t->load_children();
   if (! $t->children) {
    $tree[] = $t->chain();
   }
  }

  usort($tree, array('taxon', 'compare_chains'));

  return $tree;
 }
}

?>
