<?php

require_once('include/zoo.inc');

$quiz_groups = $zoo->load_all('quiz_group');
$H = $zoo->html;

$zoo->nav->header('Quizzes');
echo $zoo->nav->top_menu();

echo <<<HTML
<h1>Quizzes</h1>

HTML;

echo $H->edged_table_start();
echo $H->spacer_row(400,50,50,50);

foreach($quiz_groups as $q) {
 $n = count($q->load_members());
 echo $H->tr(
  $H->td($q->name) . 
  $H->td($n) . 
  $H->popup_td('Edit','quiz_group_info.php?id=' . $q->id) .
  $H->popup_td('Try','quiz.php?id=' . $q->id)
 );
}

echo $H->edged_table_end();

$zoo->nav->footer();
