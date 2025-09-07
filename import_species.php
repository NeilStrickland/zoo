<?php

require_once('include/zoo.inc');

$params = get_params();
if ($params->command == 'select_file') {
 select_file_page($params);
} else if ($params->command == 'prepare_import') {
 handle_upload($params);
 parse_file($params);
 prepare_import_page($params);
} else if ($params->command == 'import') {
 do_import($params);
 report_page($params);
} else {
 error_page("Invalid command: $params->command");
}

exit;

function get_params() {
 global $zoo;
 $params = new stdClass();
 $params->col_types = ['','genus','species','binomial','common_name','external_id','family','order','class','phylum','kingdom'];
 $params->col_type_index = [
  'taxon_name' => 'binomial',
  'common name' => 'common_name',
  'taxon_common_name' => 'common_name',
  'url' => 'external_id' 
 ];
 foreach ($params->col_types as $c) {
  if ($c) {
   $params->col_type_index[$c] = $c;
  }
 }

 $params->command = get_restricted_parameter('command', ['select_file','prepare_import','import'], 'select_file');
 $tree = [];
 $taxa = $zoo->load_where('taxa', "x.trank NOT IN ('genus','species')");
 $tranks = [];
 foreach($taxa as $t) {
  $tree[] = [$t->name, $t->trank, $t->parent_name];
  $tranks[$t->name] = $t->trank;
 }
 $params->tree = $tree;
 $params->tranks = $tranks;
 $params->col_type = [];
 for ($i = 0; $i < 100; $i++) {
  $params->col_type[$i] = get_restricted_parameter('col_type' . $i, $params->col_types, '');
 }
 foreach (['kingdom','phylum','class','order','family'] as $p) {
  $pi = $p . '_id';
  $pn = $p . '_name';
  $params->$pi = (int) get_optional_parameter($pi,0);
  $params->$p = null;
  $params->$pn = '';
  if ($params->$pi) {
   $params->$p = $zoo->load('taxa',$params->$pi);
   if ($params->$p) {
    $params->pn = $params->$p->name;
   } else {
    $params->$pi = 0;
   }
  }
 }
 $params->data_source_id = (int) get_optional_parameter('data_source_id',0);
 $params->data_source = null;
 if ($params->data_source_id) {
  $params->data_source = $zoo->load('data_sources',$params->data_source_id);
  if (! $params->data_source) {
   $params->data_source_id = 0;
  }
 }

 $params->quiz_group_id = (int) get_optional_parameter('quiz_group_id',0);
 $params->quiz_group = null;
 if ($params->quiz_group_id) {
  $params->quiz_group = $zoo->load('quiz_groups',$params->quiz_group_id);
  if (! $params->quiz_group) {
   $params->quiz_group_id = 0;
  }
 }

 return $params;
}

function select_file_page($params) {
 global $zoo;
 $N = $zoo->nav;
 $H = $zoo->html;

 $N->header('Import Species',['widgets' => ['tabber','autosuggest']]);
 echo $N->top_menu();

 $i = $H->file_input('species_file',['size' => 50]);
 $b = $H->command_button('Upload', 'document.main_form.submit();');

 echo <<<HTML
 <h1>Import species</h1>
<form name="main_form" action="import_species.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="command" value="prepare_import">
$i <br/>
$b
</form>

HTML;

 $N->footer();
}

function handle_upload($params) {
 global $zoo;

 if (!isset($_FILES['species_file'])) {
  error_page("No file uploaded");
  exit;
 }

 $f = $zoo->data_dir . '/raw/tmp_species.csv';
 move_uploaded_file($_FILES['species_file']['tmp_name'], $f);
}

function read_lines($params) {
 global $zoo;

 $f = $zoo->data_dir . '/raw/tmp_species.csv';
 $fh = fopen($f,'r');
 if (!$fh) {
  error_page("Could not open file");
  exit;
 }
 $params->lines = [];
 $params->num_cols = 0;
 while ($x = fgetcsv($fh)) {
  $n = count($x);
  if ($n > $params->num_cols) {
   $params->num_cols = $n;
  }
  if ($n > 0) {
   $params->lines[] = $x;
  }
 }
 fclose($fh); 

 $params->has_header = false;
 $params->header_line = null;

 if ($params->lines) {
  $first_line = array_map('trim',array_map('strtolower', $params->lines[0]));
  if ((in_array('genus',$first_line) && in_array('species', $first_line)) || 
      in_array('binomial',$first_line) || 
      in_array('taxon_name',$first_line)) {
   $params->has_header = true;
   $params->header_line = $first_line;
   array_pop($params->lines);
  }
 }
}

function parse_file($params) {
 read_lines($params);
 $params->cols = [];
 $params->col_type = [];
 for ($i = 0; $i < $params->num_cols; $i++) {
  $params->cols[] = [];
  $params->col_type[] = '';
 }

 if ($params->has_header) {
  for ($i = 0; $i < count($params->header_line); $i++) {
   $c = $params->header_line[$i];
   if ($c && isset($params->col_type_index[$c])) {
    $params->col_type[$i] = $params->col_type_index[$c];
   }
  }
 }

 foreach($params->lines as $line) {
  $n = count($line);
  for ($i = 0; $i < $params->num_cols; $i++) {
   $params->cols[$i][] = ($i < $n) ? $line[$i] : '';
  }
 }

 if (! $params->has_header) {
  $votes = [];
  for ($i = 0; $i < $params->num_cols; $i++) {
   $votes[$i] = [];
   foreach($params->col_types as $t) {
    $votes[$i][$t] = 0;
   }

   foreach($params->cols[$i] as $c) {
    if (! $c) {
     continue;
    }
    $n = count(explode(' ',$c));
    if (preg_match('/^[A-Z][-a-z]+ [-a-z]+$/',$c)) {
     $votes[$i]['binomial']++;
    } else if (preg_match('/^[-A-Za-z ]+$/',$c) && $n > 2) {
     $votes[$i]['common_name']++;
    } else if (preg_match('/^[-A-Z0-9]+$/',$c)) {
     $votes[$i]['external_id']++;
    } else if (isset($params->tranks[$c])) {
     $votes[$i][$params->tranks[$c]]++;
    } else if (preg_match('/^[A-Z][-a-z]+$/',$c)) {
     $votes[$i]['genus']++;
    } else if (preg_match('/^[-a-z]+$/',$c)) {
     $votes[$i]['species']++;
    }
   }

   $top_voted = '';
   $top_vote = 0;
   $num_votes = 0;
   foreach($votes[$i] as $t => $v) {
    $num_votes += $v;
    if ($v > $top_vote) {
     $top_vote = $v;
     $top_voted = $t;
    }
   }
   if ($top_vote > 0.8 * $num_votes) {
    $params->col_type[$i] = $top_voted;
   }
  }
 }
}

function prepare_import_page($params) {
 global $zoo;
 $N = $zoo->nav;
 $H = $zoo->html;

 $N->header('Import Species',['widgets' => ['tabber','autosuggest']]);
 echo $N->top_menu();

 echo <<<HTML
 <h1>Import species</h1>
<form name="main_form" action="import_species.php">
<input type="hidden" name="command" value="import">

HTML;
 $n = count($params->cols);
 $m = min(8,count($params->lines));

 echo $H->edged_table_start();
 for ($i = 0; $i < $m; $i++) {
  echo "<tr>";
  for ($j = 0; $j < $n; $j++) {
   echo $H->td($params->cols[$j][$i]);
  }
  echo "</tr>";
 }
 echo $H->edged_table_end();
 echo "<br/>";
 echo $H->edged_table_start();
 echo $H->row($H->bold('Phylum:'), $H->taxon_selector('phylum_id'));
 echo $H->row($H->bold('Class:'), $H->taxon_selector('class_id'));
 echo $H->row($H->bold('Order:'), $H->taxon_selector('order_id'));
 echo $H->row($H->bold('Family:'), $H->taxon_selector('family_id'));
 echo $H->row($H->bold('Data source:'), $H->data_source_selector('data_source_id'));
 echo $H->row($H->bold('Quiz:'), $H->quiz_group_selector('quiz_group_id'));

 for ($i = 0; $i < $n; $i++) {
  echo $H->row($H->bold('Column ' . $i . ':'), $H->selector('col_type' . $i, $params->col_types, $params->col_type[$i]));
 }
 echo $H->tr($H->td('') . $H->command_td('Submit', 'document.main_form.submit();'));
 echo $H->edged_table_end();

 echo <<<HTML
</form>
HTML;

 $N->footer();
}

function do_import($params) {
 global $zoo;

 read_lines($params);
 $params->old_species = [];
 $params->new_species = [];
 $params->new_records = [];
 foreach ($params->lines as $x) {
  if (! $x) {
   continue;
  }
  $s = $zoo->new_object('species');
  $s->external_id = '';
  foreach(['kingdom','phylum','class','order','family'] as $p) {
   $pn = $p . '_name';
   $s->$p = $params->$pn;
  }
  for ($i = 0; $i < count($x); $i++) {
   $t = $params->col_type[$i];
   $v = $x[$i];
   if (in_array($t,['genus','species','common_name','external_id','family','order','class','phylum','kingdom'])) {
    $s->$t = $v;
   } else if ($t == 'binomial') {
    $a = explode(' ',$v);
    if (count($a) >= 2) {
     $s->genus = $a[0];
     $s->species = $a[1];
    }
   }
  }

  if (! ($s->genus && $s->species)) { 
   continue;
  }
  if ($params->data_source && $s->external_id) {
   $s->extra->data_record = $zoo->new_object('data_record');
   $s->data_source_id = $params->data_source->id;
   $s->extra->data_record->external_id = $s->external_id;
  }

  $s->extra->old = null;
  if ($s->genus && $s->species) {
   $ss = $zoo->load_where('species',"x.genus = '$s->genus' AND x.species = '$s->species'");
   if ($ss) {
    $s->extra->old = $ss[0];
    $s->extra->old->extra->data_record = null;
    $s->id = $s->extra->old->id;
   }

   if ($params->data_source_id && $s->extra->old) {
    $rr = $zoo->load_where('data_records',"x.data_source_id = $params->data_source_id AND x.species_id = '{$s->extra->old->id}'");
    if ($rr) {
     $s->extra->old->extra->data_record = $rr[0];
    }
   }
  }

  if ($s->extra->old) {
   $params->old_species[] = $s;
   if ($params->data_source && $s->extra->data_record && ! $s->extra->old->extra->data_record) {
    $s->extra->data_record->species_id = $s->extra->old->id;
    $s->extra->data_record->save();
    $params->new_records[] = $s;
   }
  } else {
   $s->save();
   if ($params->data_source && $s->external_id) {
    $s->extra->data_record->species_id = $s->id;
    $s->extra->data_record->save();
   }
   $params->new_species[] = $s;
  }

  if ($params->quiz_group && $s->id) {
   $params->quiz_group->add_member($s->id);
  }
 }
}

function report_page($params) {
 global $zoo;
 $N = $zoo->nav;
 $H = $zoo->html;

 $N->header('Import Species',['widgets' => ['tabber','autosuggest']]);
 echo $N->top_menu();

 echo <<<HTML
 <h1>Import report</h1>
 <br/>

HTML;

 if ($params->new_species) {
  echo <<<HTML
<h2>New species</h2>

HTML;
  echo $H->edged_table_start();
  echo $H->tr($H->td('Genus') . $H->td('Species') . $H->td('common_name'));
  foreach($params->new_species as $s) {
   echo $H->tr($H->td($s->genus) . $H->td($s->species) . $H->td($s->common_name));
  }
  echo $H->edged_table_end();
 } else {
  echo "<p>No new species</p>";
 }

 if ($params->new_records) {
  echo <<<HTML
<h2>New data records</h2>

HTML;
  echo $H->edged_table_start();
  echo $H->tr($H->td('Genus') . $H->td('Species') . $H->td('common_name') . $H->td('external_id'));
  foreach($params->new_records as $s) {
   echo $H->tr($H->td($s->genus) . $H->td($s->species) . $H->td($s->common_name) . $H->td($s->external_id));
  }
  echo $H->edged_table_end();
 }

 $N->footer();
}