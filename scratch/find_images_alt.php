<?php

require_once('zoo.inc');

$class = get_optional_parameter('class','');

$query = "(photo IS NULL OR photo = '')";

if ($class) {
 $query .= ' AND class="' . $class . '"';
}

$all_species = $zoo->load_where('species',$query);

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
 $eol_url="http://www.eol.org/search?q={$species->genus}+{$species->species}";
 $wikipedia_url = "http://en.wikipedia.org/wiki/{$species->genus}_{$species->species}";
 $google_url = "http://images.google.co.uk/images?q={$species->genus}+{$species->species}";
 $arkive_url = "http://www.arkive.org/search.html?client=images_only&q=" . 
               $species->genus . '+' . $species->species .
               '&btnG=Go&getfields=*&output=xml_no_dtd&sort=date%3AD%3AL%3Ad1&' . 
               'filter=0&num=20&oe=utf8&ie=utf8&site=default_collection';
 $ispecies_url="http://darwin.zoology.gla.ac.uk/~rpage/ispecies/?q=" . 
                $species->genus . '+' . $species->species . '&submit=Go';

 echo <<<HTML
 <tr>
  <td>$species->id</td>
  <td>$species->common_name</td>
  <td>$species->genus $species->species</td>
  <td><a target="_blank" href="$eol_url">EOL</a></td>
  <td><a target="_blank" href="$wikipedia_url">Wikipedia</a></td>
  <td><a target="_blank" href="$google_url">Google</a></td>
  <td><a target="_blank" href="$arkive_url">Arkive</a></td>
  <td><a target="_blank" href="$ispecies_url">iSpecies</a></td>
 </tr>

HTML;
}

echo <<<HTML
</table>
</body>
</html>
HTML;

?>
