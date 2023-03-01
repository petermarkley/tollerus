<?php
date_default_timezone_set("America/New_York");
require "scripts/php/languages/dictionary.php";
$last_id = rtrim(file_get_contents("private/languages/next_id"));
$last_id_num = dictionary::id_decode($last_id);
$next_id_num = $last_id_num + 1;
$next_id = dictionary::id_encode($next_id_num);
echo $last_id . " -> " . $next_id . "\n";
file_put_contents("private/languages/next_id",$next_id."\n");
