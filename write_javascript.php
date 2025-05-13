<?php

require_once('include/zoo.inc');

$s = $zoo->javascript_declaration();
file_put_contents('js/objects_auto.js', $s);
echo "<pre>" . PHP_EOL . $s . PHP_EOL . "</pre>" . PHP_EOL;

?>
