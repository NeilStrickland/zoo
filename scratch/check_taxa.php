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

while (($s = mysql_fetch_object($result))) {
 $n++;
 check_taxa($s);
 flush();
}

exit;

function get_wikipedia_page($s) {
 $n = $s->genus . '_' . $s->species . '.html';
 $f = "C:/wamp/www/species/wikipedia_pages/$n";
 $u = "http://en.wikipedia.org/wiki/$n";
 if (file_exists($f)) {
  $h = file_get_contents($f);
  if (! (strpos($h,"Wikipedia does not have an article with this exact name") === false)) {
   $h = '';
  }
  return($h);
 } else {
  $h = file_get_contents($u);
  //  file_put_contents($f,$h);
  return($h);
 }
}

function check_taxa($s) {
 global  $db_species;
 $doc = new DOMDocument();

 error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
 $x = $doc->loadHTML(get_wikipedia_page($s));
 error_reporting(E_ALL ^ E_NOTICE);

 if (! $x) {
  echo <<<HTML
Could not load page for {$s->genus} {$s->species}<br/>

HTML;

  return(null);
 }

 $xpath = new DOMXPath($doc);
 $cname     = $xpath->query('//h1[@class="firstHeading"]')->item(0)->textContent;
 $taxoclass = $xpath->query('//span[@class="class"]')->item(0)->textContent;
 $order     = $xpath->query('//span[@class="order"]')->item(0)->textContent;
 $family    = $xpath->query('//span[@class="family"]')->item(0)->textContent;
 $genus     = $xpath->query('//span[@class="genus"]')->item(0)->textContent;
 $species   = $xpath->query('//span[@class="species"]')->item(0)->textContent;

 if (substr($species,1,1) == '.') {
  $species = substr($species,3);
 }

 if ($taxoclass == 'Synapsida') { $taxoclass = 'Mammalia'; }

 if (($taxoclass == $s->class &&
      $order == $s->order &&
      $family == $s->family &&
      $genus == $s->genus &&
      $species == $s->species)) {
  echo "$genus $species OK <br/>\n";
 } else {
  echo <<<HTML
<table>
 <tr>
  <td></td>
  <td>Common</td>
  <td>Class</td>
  <td>Order</td>
  <td>Family</td>
  <td>Genus</td>
  <td>Species</td>
 </tr>
 <tr>
  <td>Database</td>
  <td>$s->common_name</td>
  <td>$s->class</td>
  <td>$s->order</td>
  <td>$s->family</td>
  <td>$s->genus</td>
  <td>$s->species</td>
 </tr>
 <tr>
  <td>Wikipedia</td>
  <td>$cname</td>
  <td>$taxoclass</td>
  <td>$order</td>
  <td>$family</td>
  <td>$genus</td>
  <td>$species</td>
 </tr>
</table>

HTML;

  if ($taxoclass && $order && $family && $genus && $species && $cname &&
      strpos($taxoclass,' ') === false &&
      strpos($order,' ') === false &&
      strpos($family,' ') === false &&
      strpos($genus,' ') === false &&
      strpos($species,' ') === false
      ) {
   echo "Updating<br/><br/>\n\n";
   $cn = mysql_real_escape_string($cname);

   $query = <<<SQL
UPDATE tbl_species
SET `class`='$taxoclass',
    `order`='$order',
    `family`='$family',
    `genus`='$genus',
    `species`='$species',
    `common_name`='$cn'
WHERE id={$s->id}

SQL;

   mysql_query($query,$db_species);
  } else {
   echo "Not updating<br/><br/>\n\n";
  }
 }
}

?>
