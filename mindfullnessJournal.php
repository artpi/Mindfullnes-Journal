<?php
//This file is run by CRON and creates a daily note and updates G+Photos

include("config.php");
include("Utils.php");
include("evernoteAPI.php");

$m = new Journal();
$m->createDailyNote();
$m->createDailyPhotos();


?>
