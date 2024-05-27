<?php

require_once('frog/suggester.inc');
require_once('../include/zoo.inc');
require_once('../include/suggester.inc');

if (! (isset($_REQUEST['type']) && $_REQUEST['type'])) {
 exit;
}

$type = $_REQUEST['type'];

$suggester = null;

$sc = $type . '_suggester';

if (class_exists($sc)) {
 $suggester = new $sc;
}

if ($suggester) {
 $suggester->run();
}
