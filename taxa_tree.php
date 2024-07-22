<?php

require_once('include/zoo.inc');
$N = $zoo->nav;
$H = $zoo->html;

$ret = $zoo->tabulate_taxa();
$tree = $zoo->taxa_tree();

$u = new stdClass();
foreach(taxon::$tranks as $r) {
 $u->$r = '';
}

$N->header('Taxa');
echo $N->top_menu();

echo <<<HTML
<h1>Taxa</h1>

HTML;

echo $H->edged_table_start();

foreach($tree as $x) {
 $s = '';
 foreach(taxon::$tranks as $r) {
  $s0 = ($x->$r == $u->$r) ? '' : $x->$r;
  $s .= $H->td($s0);
 }
 echo $H->tr($s);
 $u = $x;
}

echo $H->edged_table_end();
$N->footer();