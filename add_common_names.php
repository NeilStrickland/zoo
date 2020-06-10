<?php

require_once('include/zoo.inc');

$species = $zoo->load_where('species',"(x.common_name IS NULL OR x.common_name='')");

$species0 = array();

foreach ($species as $s) {
 $x = new StdClass();
 $x->id = $s->id;
 $x->genus = $s->genus;
 $x->species = $s->species;
 if (! file_exists($s->wiki_file_name())) {
  $s->fetch_wiki();
 }
 
 $html = $s->wiki_file_contents();
 $y = $zoo->extract_wiki_common_name($s->genus,$s->species,$html);
 $x->wiki_common_name = $y->common_name;

 if ($x->wiki_common_name == '' || $x->wiki_common_name == $x->genus . ' ' . $x->species) {
  $x->snippet = $y->snippet;
 } else {
  $x->snippet = '';
 }
 
 $species0[] = $x;
}

$script = 'var species_list0 = ' . json_encode($species0) . ';';

echo <<<HTML
<html>
 <head>
  <title>Common names</title>
  <link rel="stylesheet" href="/js/tabber/tabber.css" TYPE="text/css" MEDIA="screen"/>
  <link rel="stylesheet" href="zoo.css" TYPE="text/css"/>
  <script type="text/javascript" src="frog.js"></script>
  <script type="text/javascript" src="objects_auto.js"></script>
  <script type="text/javascript" src="zoo.js"></script>
  <script type="text/javascript">
$script

function init() {
 var species_list = [];
 var species_table = document.getElementById('species_table');

 for(var i in species_list0) {
  var s = Object.create(zoo.species);
  species_list.push(s);
  
  var s0 = species_list0[i]; 
  s.munch(s0);

  s.tr = document.createElement('tr');
  s.binomial_td = document.createElement('td');
  s.binomial_td.style.width = '200px';
  s.binomial_td.innerHTML = s.genus + ' ' + s.species;
  s.tr.appendChild(s.binomial_td);

  s.wiki_td = document.createElement('td');
  s.wiki_td.style.width = '50px';
  s.tr.appendChild(s.wiki_td);
  s.wiki_a = document.createElement('a');
  s.wiki_td.appendChild(s.wiki_a);
  s.wiki_a.href = 'https://en.wikipedia.org/wiki/' + s.genus + '_' + s.species;
  s.wiki_a.innerHTML = 'Wiki';
  s.wiki_a.target = '_blank';

  s.google_td = document.createElement('td');
  s.google_td.style.width = '50px';
  s.tr.appendChild(s.google_td);
  s.google_a = document.createElement('a');
  s.google_td.appendChild(s.google_a);
  s.google_a.href = 'https://google.com/search?q=' + s.genus + '+' + s.species;
  s.google_a.innerHTML = 'Google';
  s.google_a.target = '_blank';

  s.wiki_cn_td = document.createElement('td');
  s.wiki_cn_td.style.width = '200px';
  s.tr.appendChild(s.wiki_cn_td);
  s.wiki_cn_a = document.createElement('a');
  s.wiki_cn_td.appendChild(s.wiki_cn_a);
  s.wiki_cn_a.innerHTML = s.wiki_common_name;
  s.wiki_cn_a.target = '_blank';

  s.common_td = document.createElement('td');
  s.common_td.style.width = '200px';
  s.tr.appendChild(s.common_td);
  s.input = document.createElement('input');
  s.common_td.appendChild(s.input);
  set_handlers(s);

  species_table.appendChild(s.tr);

  if (s.snippet) {
   s.snippet_tr = document.createElement('tr');
   s.snippet_td = document.createElement('td');
   s.snippet_td.colSpan = 5;
   s.snippet_td.style.width = '700px';
   s.snippet_td.innerHTML = s.snippet;
   s.snippet_tr.appendChild(s.snippet_td);
   species_table.appendChild(s.snippet_tr);   
  }
 }

 var n = species_list.length;
 var c = document.getElementById('species_count');
 if (n == 0) { 
  c.innerHTML = 'No species without common names';
 } else if (n == 1) {
  c.innerHTML = 'One species without a common name';
 } else {
  c.innerHTML = '' + n + ' species without common names';
 }
}

function set_handlers(s) {
 s.input.onchange = function() {
  s.common_name = s.input.value;
  s.save();
 }

 s.wiki_cn_a.onclick = function() {
  s.common_name = s.wiki_common_name;
  s.input.value = s.common_name;
  s.save();
 }
}

  </script>
 </head>
 <body>
  <h1>Common names</h1>
  <br/>
  <span id="species_count"></span>
  <table class="edged" id="species_table">
  </table>
  <script type="text/javascript">init();</script>
 </body>
</html>
HTML
;


?>
