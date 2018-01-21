<?php

require_once('zoo.inc');

$photos = get_optional_parameter('photos','');

$query = "SELECT * FROM tbl_species";
if ($photos == 'with') {
 $query .= " WHERE (photo IS NOT NULL AND photo <> '')";
} elseif ($photos == 'without') {
 $query .= " WHERE (photo IS NULL OR photo = '')";
} 

$all_species = $zoo->load_all('species');

echo <<<HTML
<html>
<head>
<style type="text/css">

td {
 vertical-align: top;
 border: 1px solid #EEEEEE;
}

</style>
</head>
<body>
<table>

HTML;

foreach($all_species as $species) {
 $url="http://www.eol.org/search?q={$species->genus}+{$species->species}";

 if ($species->photo) {
  $img = "<img width=\"100\" src=\"pictures/{$species->photo}\"/>";
 } else {
  $img = '&nbsp;';
 }
 echo <<<HTML
 <tr>
  <td>$species->id</td>
  <td><a href="$url">{$species->common_name}</a></td>
  <td>$species->fishbase_id</td>
  <td>$species->genus $species->species</td>
  <td>$img</td>
 </tr>

HTML;
}

echo <<<HTML
</table>
</body>
</html>
HTML;

?>
