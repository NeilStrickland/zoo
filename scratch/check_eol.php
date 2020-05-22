<?php

set_time_limit(0);

require_once('species.inc');
require_once('JSON.inc');

$query = <<<SQL
SELECT * FROM tbl_species 

SQL;

$result = mysql_query($query,$db_species);

if (! $result) {
 echo("Trouble: " . mysql_error());
 die("Trouble: " . mysql_error());
}

$all = array();
$parents = array();
$n = 0;

while (($s = mysql_fetch_object($result)) && $n<999) {
 if (! $s->eol_id) {
  $n++;
  check_eol($s);
  flush();
 }
}

exit;

function check_eol($s) {
 global  $db_species;
 $json = new Services_JSON();

 $url = "http://eol.org/api/search/1.0/" . 
  $s->genus . '+' . $s->species . '.json';

 $x = $json->decode(file_get_contents($url));

 $found = 0;

 foreach($x->results as $y) {
  if ($y->title == $s->genus + ' ' + $s->species) {
   $found = 1;
   echo "$s->genus $s->species: $y->id <br/>\n";
   $query = <<<SQL
UPDATE tbl_species SET eol_id={$y->id} WHERE id={$s->id}
SQL;
   mysql_query($query,$db_species);
   break;
  }
 }

 if (!$found) {
   echo "$s->genus $s->species: not found <br/>\n";
 }
}

?>
