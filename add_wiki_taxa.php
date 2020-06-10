<?php

require_once('include/zoo.inc');

$command = get_restricted_parameter('command',array('preview','save'),'preview');
$fetch   = get_optional_parameter('fetch',0) ? 1 : 0;

$species = $zoo->load_where('species',"(x.family IS NULL OR x.family='')");

$species0 = array();

foreach($species as $s) {
 $html = $s->wiki_file_contents();
 if ($fetch && ! $html) {
  $html = $zoo->fetch_wiki($s->genus,$s->species);
 }
 
 if (! $html) { continue; }
 $s->taxa = $zoo->extract_wiki_taxa($html);
 $species0[] = $s;
}

$species = $species0;

echo <<<HTML
<html>
 <head>
  <title>Wiki taxa</title>
  <link rel="stylesheet" href="/js/tabber/tabber.css" TYPE="text/css" MEDIA="screen"/>
  <link rel="stylesheet" href="css/zoo.css" TYPE="text/css"/>
 </head>
 <body>
  <h1>Wiki taxa</h1>
  <table class="edged">

HTML
  ;

foreach($species as $s) {
 $t = $s->taxa;

 if ($command == 'save' && $t && ($t->family || $t->order || $t->class)) {
  if (! $s->family) { $s->family = $t->family; }
  if (! $s->order ) { $s->order  = $t->order; }
  if (! $s->class ) { $s->class  = $t->class; }
  $s->save();
 }
 
 echo <<<HTML
   <tr>
    <td width="200">{$s->genus} {$s->species}</td>
    <td width="100">{$s->family}</td>
    <td width="100">{$s->order}</td>
    <td width="100">{$s->class}</td>
    <td width="100">{$t->family}</td>
    <td width="100">{$t->order}</td>
    <td width="100">{$t->class}</td>
   </tr>
HTML
  ;
}

echo <<<HTML
  </table>
 </body>
</html>
HTML
;

?>
