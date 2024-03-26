<?php

class species_suggester extends frog_suggester {
 function __construct() {
  global $zoo;
  $this->frog = $zoo;
  $this->type = 'species';
  $this->table_name = 'tbl_species x';
  $this->descriptor_query = <<<SQL
SELECT CONCAT(x.genus, ' ' , x.species, ' (',x.common_name,')') 
FROM tbl_species x WHERE x.id = %s LIMIT 0,1
SQL
; }
 
 function query($where, $n) {
  return <<<SQL
SELECT
 x.id AS k,
 CONCAT(x.genus, ' ' , x.species, ' (',x.common_name,')') AS v
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
SELECT CONCAT(x.name, ' (',x.rank,')') 
FROM tbl_taxa x WHERE x.id = %s LIMIT 0,1
SQL
; }
 
 function get_suggestions() {
  $s = $this->search_term;
  $n = 10;
  $q = <<<SQL
SELECT
 x.id AS k,
 CONCAT(x.name, ' (',x.rank,')') AS v
FROM {$this->table_name}
WHERE x.name LIKE '%$s%'
ORDER BY x.rank,x.name
LIMIT 0,$n

SQL;

  $n -= $this->add_suggestions($q);
 }
}