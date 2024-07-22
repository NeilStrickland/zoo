<?php

require_once('include/zoo.inc');

$data_sources = $zoo->load_all('data_source');
$H = $zoo->html;

$zoo->nav->header('Data sources');
echo $zoo->nav->top_menu();

echo <<<HTML
<h1>Data sources</h1>
<br/>
<a href="data_source_info.php?command=new" target="_blank">Add a new data source</a>
<br/>
HTML;

echo $H->edged_table_start();
echo $H->spacer_row(400,50,50,50);

foreach($data_sources as $x) {
 if ($x->home_page) {
  $v = $H->popup_td('Visit',$x->home_page);
 } else {
  $v = $H->td('&nbsp;');
 }
 echo $H->tr(
  $H->td($x->name) . 
  $H->td($x->code) . 
  $H->popup_td('Edit','data_source_info.php?id=' . $x->id) .
  $v
 );
}

echo $H->edged_table_end();

$zoo->nav->footer();
