<?php

wikidata_lookup('Adonis Blue');

function wikidata_lookup($common_name) {
 $n = preg_replace("/[^A-Za-z0-9 -]/", '', $common_name);
 
 $q = <<<SPARQL
SELECT ?s ?sLabel ?gLabel ?fLabel ?oLabel ?cLabel
WHERE 
{
  ?s ?label "{$n}"@en .
  ?s wdt:P171/wdt:P171* ?g . ?g wdt:P105 wd:Q34740 .
  ?g wdt:P171/wdt:P171* ?f . ?f wdt:P105 wd:Q35409 .
  ?f wdt:P171/wdt:P171* ?o . ?o wdt:P105 wd:Q36602 .
  ?o wdt:P171/wdt:P171* ?c . ?c wdt:P105 wd:Q37517 
  SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],en". }
}

SPARQL;
 
 $url = 'https://query.wikidata.org/sparql';

 $user_agent = 'bot made by Neil Strickland, https://shef.ac.uk/nps';

 echo "<pre>\r\n$url\r\n</pre>\r\n<br/><br/>\r\n";

 $handle = curl_init();

 curl_setopt($handle, CURLOPT_URL, $url);
 curl_setopt($handle, CURLOPT_POST, true);
 curl_setopt($handle, CURLOPT_POSTFIELDS,
             array('query' => $q)
 );
 curl_setopt($handle, CURLOPT_USERAGENT, $user_agent);
 curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
 
 $output = curl_exec($handle);
 
 curl_close($handle);
 
 echo $output;


 $opts = [
  'http' => [
   'method' => 'GET',
   'header' => [
    'Accept: application/sparql-results+json',
    'User-Agent: ' . $user_agent,
   ],
  ],
 ];
 
 echo "<pre>\r\n";
 var_dump($output);
}
