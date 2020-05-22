<?php

require_once('species.inc');

$result = mysql_query("SELECT * FROM tbl_species ORDER BY genus,species",$db_species);

if (! $result) {
 echo("Trouble: " . mysql_error());
 die("Trouble: " . mysql_error());
}

$all_species = array();

$search_url = "http://en.wikipedia.org/wiki/";

$n = 0;

echo <<<HTML
<pre>

HTML;

while (($species = mysql_fetch_object($result))) {
 $f = $species->genus . '_' . $species->species . '.html';
 if (! file_exists("wikipedia_pages/$f")) {
  $u = $search_url . $species->genus . '_' . $species->species;
  // $t = file_get_contents($u);
  // echo "$t\n";
  echo "curl -o $f $u >> w.txt\n";
 }
}

echo <<<HTML
<pre>

HTML;

?>
