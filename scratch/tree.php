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

while ($s = mysql_fetch_object($result)) {
 $all[$s->class][$s->order][$s->family][$s->genus][$s->species] = $s;
}

echo <<<HTML
<html>
<head>
<script type="text/javascript" src="mktree.js"></script>
<link rel="stylesheet" href="mktree.css" type="text/css">
<script type="text/javascript">

function show_picture(f) {
 document.getElementById('species_picture').src = 'pictures/' + f;
}

</script>
</head>
<body>
<h1>Madagascar species</h1>
<br/><br/>
<table>
<tr>
<td width="500" valign="top">
<ul class="mktree">

HTML;

foreach($all as $class => $class_members) {
 echo <<<HTML
 <li><a href="http://en.wikipedia.org/wiki/$class">$class</a>
  <ul>

HTML;
 
 foreach($class_members as $order => $order_members) {
  echo <<<HTML
   <li><a href="http://en.wikipedia.org/wiki/$order">$order</a>
    <ul>

HTML;

  foreach($order_members as $family => $family_members) {
   echo <<<HTML
     <li><a href="http://en.wikipedia.org/wiki/$family">$family</a>
      <ul>

HTML;

   foreach($family_members as $genus => $genus_members) {
    echo <<<HTML
       <li><a href="http://en.wikipedia.org/wiki/$genus">$genus</a>
        <ul>

HTML;
    foreach($genus_members as $species_name => $species) {

     $x = "$species_name ($species->common_name) ";
     $u = wikipedia_url($species);
     if ($u) { $x .= " <a href=\"$u\" target=\"_blank\">W</a> "; }
     $u = arkiv_url($species);
     if ($u) { $x .= " <a href=\"$u\" target=\"_blank\">A</a> "; }
     $u = photo_url($species);
     if ($u) { $x .= " <a href=\"$u\">P</a> "; }

     echo <<<HTML
         <li>$x</li>

HTML;
    }
    echo <<<HTML
        </ul>
       </li>

HTML;
   }
   echo <<<HTML
      </ul>
     </li>

HTML;
  }
  echo <<<HTML
    </ul>
   </li>

HTML;
 }
 echo <<<HTML
  </ul>
 </li>
HTML;
}

echo <<<HTML
</ul>
</td>
<td width="460" valign="top">
 <div style="width: 440px; height: 270px; overflow: hidden;">
 <img id="species_picture" width="400" src="pictures/blank.jpg"/>
 </div>
</td>
</body>
</html>

HTML;

function wikipedia_url($s) {
 if (! $s->has_wikipedia_entry) { return(''); }
 return("http://en.wikipedia.org/wiki/{$s->genus}_{$s->species}"); 
}

function arkiv_url($s) {
 if (! $s->has_arkiv_entry) { return(''); }
 $c = $s->class;
 if ($c == 'Mammalia') {
  $d = 'mammals';
 } elseif ($c == 'Aves') {
  $d = 'birds';
 } elseif($c == 'Actinopterygii' || $c == 'Chondrichthyes' ) {
  $d = 'fish';
 } else {
  $d = '';
 }

 if ($d) {
  $u = 'http://www.arkive.org/species/GES/' . $d . '/' .
   $s->genus . '_' . $s->species . '/';
 } else {
  $u = '';
 }

 return($u);
}

function photo_url($s) {
 if ($s->photo) {
  return("javascript:show_picture('$s->photo')");
 } else {
  return('');
 }
}

?>
