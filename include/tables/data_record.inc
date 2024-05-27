<?php

global $zoo;

$zoo->add_table(
 'data_record','data_records',		
 array(		
  'id' => array('type' => 'integer','notnull' => true),
  'species_id' => array('type' => 'int','link' => 'species'),
  'data_source_id' => array('type' => 'int','link' => 'data_source'),
  'external_id' => array('type' => 'text')
 ),
 array(
  'data_source_name' => array('type' => 'text'),
  'data_source_code' => array('type' => 'text'),
  'data_source_species_page_format' => array('type' => 'text'),
  'species_genus' => array('type' => 'text'),
  'species_species' => array('type' => 'text'),
  'species_common_name' => array('type' => 'text')
 ),
 <<<SQL
SELECT
 x.id,
 x.species_id,
 t.genus AS species_genus, 
 t.species AS species_species, 
 t.common_name AS species_common_name,
 x.data_source_id,
 s.name AS data_source_name,
 s.code AS data_source_code,
 s.species_page_format AS data_source_species_page_format,
 x.external_id
FROM tbl_data_records x
 LEFT JOIN tbl_species t ON x.species_id = t.id
  LEFT JOIN tbl_data_sources s ON x.data_source_id=s.id
WHERE %s
 ORDER BY s.name, t.genus, t.species
 
SQL
);

class data_record extends frog_object {
 public $id,$species_id,$species_genus,$species_species,$species_common_name,
  $data_source_id,$data_source_name,$data_source_code,$data_source_species_page_format,
  $external_id;

 function __construct($id = null,$with_defaults=1) {
  global $zoo;

  parent::__construct($zoo,'data_record',$id,$with_defaults);
 }

 function subclass() {
  $subclass_name = 'data_record_' . $this->data_source_code;
  if (class_exists($subclass_name)) {
   $x = new $subclass_name();

   $x->id = $this->id;
   $x->species_id = $this->species_id;
   $x->species_genus = $this->species_genus;
   $x->species_species = $this->species_species;
   $x->species_common_name = $this->species_common_name;
   $x->data_source_id = $this->data_source_id;
   $x->data_source_name = $this->data_source_name;
   $x->data_source_code = $this->data_source_code;
   $x->external_id = $this->external_id;
   
   return $x; 
  } else {
   return $this;
  }
 }
 
 function species_page_url($r) {
  $vals = array();
  $vals['G'] = ucwords($r->genus);
  $vals['g'] = strtolower($vals['G']);
  $vals['S'] = ucwords($r->species);
  $vals['s'] = strtolower($vals['S']);
  $vals['i'] = $r->external_id;

  $formats = explode('|',$this->data_source_species_page_format);
  foreach($formats as $f) {
   $ok = true;
   $u = $f;
   foreach($vals as $k => $v) {
    if (strpos($f,"#$k") !== false) {
     if ($v) {
      $u = str_replace("#$k",$v,$u);
     } else {
      $ok = false;
      break;
     }
    }
   }

   if ($ok) {
    return($u);
   }
  }

  return null;
 }

 function image_url() {
  return null;
 }
 
 function linked_binomial($r) {
  $u = $this->species_page_url($r);
  if ($u) {
   return '<a target="_blank" href="' . $u . '">' .
    $r->genus . ' ' . $r->species . '</a>';
  } else {
   return $r->genus . ' ' . $r->species;
  }
 }
}

class data_record_tol extends data_record {
 function __construct($id = null,$with_defaults=1) {
  parent::__construct($id,$with_defaults);
 }

 function find_external_id($G,$s) {
  $url = "http://tolweb.org/onlinecontributors/app" .
   "?service=external&page=xml/GroupSearchService&group={$G}%20{$s}";

  $xml = file_get_contents($url);
  if (! $xml) { return null; }
  $nodes = new SimpleXMLElement($xml);
  if ($nodes['COUNT'] != 1) {
   return null;
  }
  $node = $nodes->NODE[0];
  return $node['ID'];
 }
}

class data_record_eol extends data_record {
 function __construct($id = null,$with_defaults=1) {
  parent::__construct($id,$with_defaults);
 }

 function find_external_id($G,$s) {
  $url = "http://eol.org/api/search/1.0.json?q={$G}+{$s}&page=1&exact=true";
  
  $json = file_get_contents($url);
  $data = json_decode($json);

  if (! (isset($data->totalResults) && $data->totalResults == 1)) {
   return null;
  }

  if (! (isset($data->results) && $data->results)) {
   return null;
  }

  $result = $data->results[0];
  return $result->id;
 }

 function image_url() {
  if ($this->external_id) {
   return 'http://www.eol.org/pages/' .
    $this->external_id . '/media?type=image';
  } else {
   return null;
  }
 }
}

class data_record_w extends data_record {
 function __construct($id = null,$with_defaults=1) {
  parent::__construct($id,$with_defaults);
 }

 function image_url() {
  $id = $this->external_id;
  if (! $id) {
   $id = urlencode($this->species_genus + '_' + $this->species_species);
  }

  return 'https://commons.wikimedia.org/wiki/' . $id;
 }
}

class data_record_ws extends data_record {
 function __construct($id = null,$with_defaults=1) {
  parent::__construct($id,$with_defaults);
 }

 function find_external_id($G,$s) {
  $url = "https://species.wikimedia.org/w/api.php?action=opensearch&" .
   "format=json&search={$G}+{$s}&redirects=resolve";

  $json = file_get_contents($url);
  $data = json_decode($json);

  if (! (count($data) == 4)) {
   return null;
  }

  $titles = $data[1];
  $urls = $data[3];

  if (! ($titles && ($titles[0] = "$G $s"))) {
   return null;
  }

  $url = $urls[0];

  $prefix = "https://species.wikimedia.org/wiki/";
  $n = strlen($prefix);
  
  if (! (strlen($url) >= $n && substr($url,0,$n) == $prefix)) {
   return null;
  }

  $id = substr($url,$n);

  return $id;
 }

 function image_url() {
  $id = $this->external_id;
  if (! $id) {
   $id = urlencode($this->species_genus + '_' + $this->species_species);
  }

  return 'https://commons.wikimedia.org/wiki/' . $id;
 }
}

class data_record_fb extends data_record {
 function __construct($id = null,$with_defaults=1) {
  parent::__construct($id,$with_defaults);
 }

 function find_external_id($G,$s) {
  $url = "http://www.fishbase.org/Nomenclature/ScientificNameSearchList.php?" .
   "Language=English&" .
   "crit1_fieldname=SYNONYMS.SynGenus&crit1_fieldtype=CHAR&crit1_operator=EQUAL&" .
   "crit1_value={$G}&" .
   "crit2_fieldname=SYNONYMS.SynSpecies&crit2_fieldtype=CHAR&crit2_operator=EQUAL&" .
   "crit2_value={$s}";
  
  $json = file_get_contents($url);
  $data = json_decode($json);

  if (! (count($data) == 4)) {
   return null;
  }

  $titles = $data[1];
  $urls = $data[3];

  if (! ($titles && ($titles[0] = "$G $s"))) {
   return null;
  }

  $url = $urls[0];

  $prefix = "https://species.wikimedia.org/wiki/";
  $n = strlen($prefix);
  
  if (! (strlen($url) >= $n && substr($url,0,$n) == $prefix)) {
   return null;
  }

  $id = substr($url,$n);

  return $id;
 }
}

?>
