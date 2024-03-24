<?php

global $zoo;

$zoo->add_table(
 'data_source','data_sources',		
 array(		
  'id' => array('type' => 'integer','notnull' => 'true'),
  'name' => array('type' => 'text'),
  'code' => array('type' => 'text'),
  'default_key' => array('type' => 'text'),
  'species_page_format' => array('type' => 'text')
 ),
 array(),
 <<<SQL
SELECT
 x.id,
 x.name,
 x.code,
 x.default_key
FROM tbl_data_sources x
WHERE %s
 ORDER BY x.name
SQL
);

class data_source extends frog_object {
 public $id,$name,$code,$default_key,$species_page_format,$species_page_formats;

 function __construct($id = null,$with_defaults=1) {
  global $zoo;

  parent::__construct($zoo,'data_source',$id,$with_defaults);
 }

 function subclass() {
  $subclass_name = 'data_source_' . $this->code;
  if (class_exists($subclass_name)) {
   $x = new $subclass_name();
   $x->id = $this->id;
   $x->name = $this->name;
   $x->code = $this->code;
   $x->default_key = $this->default_key;
   $x->species_page_format = $this->species_page_format;
   return $x; 
  } else {
   return $this;
  }
 }
 
 function species_page_url($r) {
  $this->species_page_formats = explode('|',$this->species_page_format);

  $vals = array();
  $vals['G'] = ucwords($r->genus);
  $vals['g'] = strtolower($vals['G']);
  $vals['S'] = ucwords($r->species);
  $vals['s'] = strtolower($vals['S']);
  $vals['i'] = $r->external_id;

  foreach($this->species_page_formats as $f) {
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

 function linked_binomial($r) {
  $u = $this->species_page_url($r);
  if ($u) {
   return '<a target="_blank" href="' . $u . '">' .
    $r->genus . ' ' . $r->species . '</a>';
  } else {
   return $r->genus . ' ' . $r->species;
  }
 }

 function find_external_id($G,$s) {
  // should be overridden by subclasses
  return null;
 }
}

//////////////////////////////////////////////////////////////////////

class data_source_tol extends data_source {
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

//////////////////////////////////////////////////////////////////////

class data_source_eol extends data_source {
 function __construct($id = null,$with_defaults=1) {
  parent::__construct($id,$with_defaults);
 }

 function find_external_id($G,$s) {
  $url = "http://eol.org/api/search/1.0.json?q={$G}+{$s}&page=1&exact=true";
  
  $json = file_get_contents($url);
  
  $data = json_decode($json);

  if (! (isset($data->totalResults) && $data->totalResults > 0)) {
   return null;
  }

  if (! (isset($data->results) && $data->results)) {
   return null;
  }

  // EOL often gives more than one result, but with all results pointing
  // to the same page.  This seems to be something to do with different
  // authorities and different subspecies.  If all results have the same
  // id, then we return that.  Otherwise, we give up and return null.
  
  $n = $data->totalResults;
  $id = $data->results[0]->id;
  for ($i = 1; $i < $n; $i++) {
   $j = $data->results[$i]->id; 
   if ($j != $id) {
    return null;
   }
  }

  return $id;
 }
}

//////////////////////////////////////////////////////////////////////

class data_source_ws extends data_source {
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
}

//////////////////////////////////////////////////////////////////////

class data_source_fb extends data_source {
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