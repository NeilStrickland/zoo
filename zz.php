<?php

//header('Content-type: text/plain');

require_once('zoo.inc');

$html = $zoo->wiki_file_contents('Acer','campestre');

$taxa = $zoo->extract_wiki_taxa($html);

var_dump($taxa);

?>
