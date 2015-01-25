<?php
include("config.php");
include("Utils.php");
include("evernoteAPI.php");

$m = new Journal();
$m->createDailyNote();
$m->createDailyPhotos();


?>
