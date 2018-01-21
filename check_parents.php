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

while ($s = mysql_fetch_object($result)) {
 $all[$s->class][$s->family][$s->genus][$s->species] = $s;

 check_parent('order','class',$s);
 check_parent('family','order',$s);
 check_parent('genus','family',$s);
}

echo <<<HTML
<table>

HTML;

foreach($parents as $k => $v) {
 $k2 = $k;

 if (count($v) > 1) {
  foreach($v as $h => $w) {
   $h2 = $h;

   foreach($w as $s) {
    echo <<<HTML
 <tr>
  <td><b>$k2</b></td>
  <td><em>$h2</em></td>
  <td><a href="http://en.wikipedia.org/wiki/{$s->genus}_{$s->species}">$s->genus $s->species</a></td>
 </tr>

HTML;

    $h2 = '';
   }
   $k2 = '';
  }
 }
}

echo <<<HTML
</table>

HTML;

exit;

function check_parent($level,$parent_level,$s) {
 global $parents;

 $key = $level . '_' . $s->$level;
 $parents[$key][$s->$parent_level][] = $s;
}

?>
