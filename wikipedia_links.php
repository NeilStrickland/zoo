<?php

require_once('species.inc');

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

while (($s = mysql_fetch_object($result)) && $n < 3) {
 echo <<<HTML
<a href="http://en.wikipedia.org/wiki/{$s->genus}_{$s->species}">
$s->genus $s->species
</a><br/>

HTML;
}


?>
