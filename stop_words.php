<?php

$stop_words = array();

$f = fopen("stop_words.txt", "r") or die("Unable to open Stop Words File");

while(!feof($f)) $stop_words[] = trim(fgets($f), "\n");

fclose($f);

?>