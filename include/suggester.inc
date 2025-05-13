<?php

class species_suggester extends frog_suggester {
 function __construct() {
  global $zoo;
  $this->frog = $zoo;
  $this->type = 'species';
  $this->table_name = 'tbl_species x';
  $this->descriptor_query = <<<SQL
SELECT (x.genus || ' ' || x.species || ' (' || x.common_name || ')') 
FROM tbl_species x WHERE x.id = %s LIMIT 0,1
SQL
; }
 
 function query($where, $n) {
  return <<<SQL
SELECT
 x.id AS k,
 (x.genus || ' ' || x.species || ' (' || x.common_name || ')') AS v
FROM {$this->table_name}
WHERE $where
ORDER BY x.genus,x.species
LIMIT 0,$n
SQL;
 }

 function get_suggestions() {
  $s = $this->search_term;
  $t = ''; $u = '';

  $n = 10;

  $n -= $this->add_suggestions($this->query("x.common_name LIKE '%$s%'", $n));
  if ($n) {
   if (strpos($s,' ') !== false) {
    $s = explode(' ',$s);
    $genus = $s[0];
    $species = $s[1];
    $n -= $this->add_suggestions($this->query("x.genus='{$genus}' AND x.species LIKE '%{$species}%'", $n));
   } else {
    $n -= $this->add_suggestions($this->query("x.genus LIKE '%$s%'", $n));
    if ($n) {
     $n -= $this->add_suggestions($this->query("x.species LIKE '%$s%'", $n));
    }
   }
  }
 }
}

class taxon_suggester extends frog_suggester {
 function __construct() {
  global $zoo;
  $this->frog = $zoo;
  $this->type = 'taxon';
  $this->table_name = 'tbl_taxa x';
  $this->descriptor_query = <<<SQL
SELECT (x.name || ' (' || x.trank || ')') 
FROM tbl_taxa x WHERE x.id = %s LIMIT 0,1
SQL
; }
 
 function get_suggestions() {
  $s = $this->search_term;
  $n = 10;
  $q = <<<SQL
SELECT
 x.id AS k,
 (x.name || ' (' || x.trank || ')') AS v
FROM {$this->table_name}
WHERE x.name LIKE '%$s%'
ORDER BY x.trank,x.name
LIMIT 0,$n

SQL;

  $n -= $this->add_suggestions($q);
 }
}

class ranked_taxon_suggester extends frog_suggester {
 var $trank;

 function __construct($trank) {
  global $zoo;
  $this->frog = $zoo;
  $this->type = 'taxon';
  $this->trank = $trank;  
  $this->table_name = 'tbl_taxa x';
  $this->descriptor_query = <<<SQL
SELECT x.name 
FROM tbl_taxa x WHERE x.id = %s AND x.trank='{$trank}' LIMIT 0,1
SQL
; }
 
 function get_suggestions() {
  $s = $this->search_term;
  $n = 10;
  $q = <<<SQL
SELECT
 x.name AS k,
 x.name AS v
FROM {$this->table_name}
WHERE x.name LIKE '%$s%' AND x.trank='{$this->trank}'
ORDER BY x.name
LIMIT 0,$n

SQL;

  $n -= $this->add_suggestions($q);
 }
}

class kingdom_suggester extends ranked_taxon_suggester {
 function __construct() {
  parent::__construct('kingdom');
 }
}

class phylum_suggester extends ranked_taxon_suggester {
 function __construct() {
  parent::__construct('phylum');
 }
}

class class_suggester extends ranked_taxon_suggester {
 function __construct() {
  parent::__construct('class');
 }
}

class order_suggester extends ranked_taxon_suggester {
 function __construct() {
  parent::__construct('order');
 }
}

class family_suggester extends ranked_taxon_suggester {
 function __construct() {
  parent::__construct('family');
 }
}

class location_suggester extends frog_suggester {
 function __construct() {
  global $zoo;
  $this->frog = $zoo;
 }
 
 function get_suggestions() {
  $s = $this->search_term;
  $n = 10;
  $q = <<<SQL
SELECT location as k, location as v FROM 
(SELECT DISTINCT location FROM tbl_photos WHERE location IS NOT NULL)
WHERE location LIKE '%$s%'
ORDER BY location
LIMIT 0,$n

SQL;

  $n -= $this->add_suggestions($q);
 }
}
