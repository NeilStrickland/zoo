<?php

require_once('include/zoo.inc');

$photos = $zoo->load_all('photos');

foreach($photos as $p) {
 if (! preg_match('/^([A-Z][a-z]+) ([a-z]+)( [0-9]+)?\.(JPG|jpg)$/', $p->file_name, $m)) {
  continue;
 }
 $genus = $m[1];
 $species = $m[2];
 $binomial = $genus . ' ' . $species;

 $xx = $zoo->load_where('species',"genus='$genus' AND species='$species'");
 if (count($xx) == 0) {
  echo <<<HTML
  Species not found for photo {$p->id} at {$p->dir}/{$p->file_name}
  &nbsp;&nbsp; (<a href="https://www.google.com/search?q=$binomial" target="_blank">Search</a>)
  &nbsp;&nbsp; (<a href="species_info.php?command=new&genus=$genus&species=$species" target="_blank">Create</a>)
  <br/>
  
HTML;

  continue;
 }
 $named_species = $xx[0];

 $ss = $p->load_species();
 $found = false;
 if ($ss !== null) {
  foreach($ss as $s) {
   if ($s->genus == $genus && $s->species == $species) {
    $found = true;
    break;
   }
  }
 }

 if (! $found) {
  $p->add_species($named_species->id);
  echo "Added species for photo {$p->id} at {$p->dir}/{$p->file_name}<br/>\n";
 }
}