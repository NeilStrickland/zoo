<?php

require_once('species.inc');

$result = mysql_query("SELECT * FROM tbl_species",$db_species);

if (! $result) {
 echo("Trouble: " . mysql_error());
 die("Trouble: " . mysql_error());
}

$all_species = array();

echo <<<HTML
<table>

HTML;

while ($species = mysql_fetch_object($result)) {
 if (! $species->photo) {
  if (file_exists("$photos_dir/{$species->id}.jpg")) {
   $species->photo = $species->id . '.jpg';
  } elseif (file_exists("$photos_dir/{$species->id}.jpeg")) {
   $species->photo = $species->id . '.jpeg';
  } elseif (file_exists("$photos_dir/{$species->id}.gif")) {
   $species->photo = $species->id . '.gif';
  }

  if ($species->photo) {
   echo <<<HTML
 <tr>
  <td>{$species->common_name}</td>
  <td>{$species->photo}</td>
 </tr>

HTML;

   mysql_query(
    "UPDATE tbl_species SET photo='{$species->photo}' WHERE id={$species->id}",
    $db_species
   );
  }
 }
}

echo <<<HTML
</table>

HTML;

?>
