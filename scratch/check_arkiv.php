<?php

require_once('species.inc');

$arkiv = array();

$arkiv_lines = explode("\n",file_get_contents('arkiv/list.txt'));

foreach($arkiv_lines as $line) {
 $x = trim($line);

 if($x) {
  $y = explode(' ',$x);
  if (count($y) == 2) {
   $arkiv[$y[0]][$y[1]] = 1;
  }
 }
}

$result = mysql_query("SELECT * FROM tbl_species",$db_species);

if (! $result) {
 echo("Trouble: " . mysql_error());
 die("Trouble: " . mysql_error());
}

while ($species = mysql_fetch_object($result)) {
 if (isset($arkiv[$species->genus]) && 
     isset($arkiv[$species->genus][$species->species])) {
  $y = 'Yes';

  if (! $species->has_arkiv_entry) {
   mysql_query(
    "UPDATE tbl_species SET has_arkiv_entry=1 WHERE id={$species->id}",
    $db_species
   );
  }
 } else {
  $y = 'No';

  if ($species->has_arkiv_entry) {
   mysql_query(
    "UPDATE tbl_species SET has_arkiv_entry=0 WHERE id={$species->id}",
    $db_species
   );
  }
 }

 echo "$species->genus $species->species: $y<br/>\n";
}


?>
