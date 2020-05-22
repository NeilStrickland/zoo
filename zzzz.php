<?php

//header('Content-type: text/plain');

require_once('zoo.inc');


$ss = $zoo->load_where('species',"x.id > 3230");

foreach($ss as $x) {
 $g = $x->species;
 $s = $x->genus;
 $x->species = $s;
 $x->genus = $g;
 $x->save();
 echo "Fixing {$x->common_name}<br/>\r\n";
}


?>
