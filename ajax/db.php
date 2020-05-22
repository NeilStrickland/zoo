<?php

require_once('../zoo.inc');

$FROG = $zoo;

$debug = get_optional_parameter('debug',0);

if (function_exists('xdebug_disable') && ! $debug) {
 xdebug_disable();
}

function send_error($s = '',$x = null) {
 global $debug;
 if ($debug) {
  echo "0: $s <br/>\r\n<pre>\r\n";
  print_r($x);
  echo "\r\n</pre>\r\n";
 } else {
  echo "0";
 }
 exit;
}

function audit_record() {
 0;
}

$commands = array(
 'load'   => 1,
 'insert' => 1,
 'update' => 1,
 'delete' => 1
);

if (! isset($_REQUEST['object_type'])) {
 send_error("no object type",$_REQUEST);
}

$ot = $_REQUEST['object_type'];

$tbl = $FROG->load_table($ot);
$fields = $tbl->fields;
$ot = $tbl->name;
$key = $tbl->primary_key;

if (! isset($_REQUEST['command'])) {
 send_error("no command",$_REQUEST);
}

$command = $_REQUEST['command'];

if (! isset($commands[$command])) {
 send_error("bad command",$_REQUEST);
}

$audit = 0;

if (isset($_REQUEST['audit']) && $_REQUEST['audit']) {
 $audit = 1;
}

if ($command == 'load' || $command == 'delete' || $command == 'update') {
 if (! isset($_REQUEST[$key])) {
  send_error("no $key",$_REQUEST);
 }

 $val = $_REQUEST[$key];

 if ($tbl->primary_key_type == 'integer') {
  $val = (int) $val; 
 } else {
  $val = $FROG->sanitise($val);
 }
}

$obj = $FROG->new_object($ot,null,0);

if ($command == 'insert' || $command == 'update') {
 if (! isset($_REQUEST['data'])) {
  send_error("no data",$_REQUEST);
 }

 $data_string = $_REQUEST['data'];

 $data = json_decode($data_string);

 foreach($data as $k => $v) {
  $obj->$k = $v;
 }
}

$is_error = false;
$eh = set_error_handler("ignore_errors");

if ($command == 'load') {
 $obj->$key = $val;
 if (! $obj->load()) { $obj->error = 1; } 
 echo json_encode($obj);
} elseif ($command == 'delete') {
 $obj->$key = $val;
 if ($obj->load()) {
  $obj->delete();
  if ($audit) {
   audit_record($obj,'ajax delete:' . json_encode($obj));
  }
 }
 echo "0";
} else if ($command == 'insert') {
 $obj->insert();
 if ($audit) {
  audit_record($obj,'ajax insert:' . json_encode($obj));
 }
 echo $key == 'id' ? $obj->id : '0';
} else if ($command == 'update') {
 $obj->$key = $val;
 if ($debug) { var_dump($obj); }
 $obj->update();
 if ($debug) { $obj->load(); var_dump($obj); }
 if ($audit) {
  audit_record($obj,'ajax update:' . json_encode($obj));
 }
 echo "0";
}


?>
