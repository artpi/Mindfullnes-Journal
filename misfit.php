<?php

date_default_timezone_set('Europe/Warsaw');

Class Misfit {
    private $token;

    function __construct($t) {
        $this->token = $t;
    }

    function format($seconds) {
        $minutes = floor($seconds / 60);
        $min = $minutes % 60;
        $hour = floor(($minutes-$min)/60);
        return $hour.":".str_pad($min, 2, '0', STR_PAD_LEFT);
    }

    function translateSleep($sl) {
        $start = strtotime($sl['startTime']);
        $duration = $sl['duration'];
        $end = $start + $duration;
        $types = array();

        for($i=count($sl['sleepDetails']) -1; $i>=0; $i--) {
            $t = strtotime($sl['sleepDetails'][$i]['datetime']);
            $diff = $end-$t;
            $types[$sl['sleepDetails'][$i]['value']] += $diff;
            $end = $t;
        }

        return array(
            'date'  => date("d.m.Y", $start+$duration),
            'start' => date("H:i", $start),
            'end'   => date("H:i", $start+$duration),
            'total' => $this->format($types[2]+$types[3]),
            'deep'  => $this->format($types[3])
        );

    }

    function getSleep($id) {
        $s = file_get_contents("https://api.misfitwearables.com/move/resource/v1/user/me/activity/sleeps/".$id."?access_token=".$this->token);
        $sleep=json_decode($s, true);
        return $this->translateSleep($sleep);
    }

    function getGoal($id) {
        $s = file_get_contents("https://api.misfitwearables.com/move/resource/v1/user/me/activity/goals/".$id."?access_token=".$this->token);
        $goal=json_decode($s, true);
        $goal['date'] = date("d.m.Y", strtotime($goal['date']));
        return $goal;
    }

    function endPoint($payload) {
        $data = json_decode($payload, true);
        if(strlen($data['Message'])>0 && $data['Type'] == 'Notification') {
            $msg = json_decode($data['Message'], true);
            for($i=0; $i<count($msg); $i++) {
                if($msg[$i]['type'] == 'sleeps' && $msg[$i]['action'] == 'created') {
                    $sleep = $this->getSleep($msg[$i]['id']);
                    updateJournal($sleep['date'], array(
                            array("[SLEEP_TOTAL]", $sleep['total']),
                            array("[SLEEP_DEEP]", $sleep['deep']),
                            array("[SLEEP_START]", $sleep['start']),
                            array("[SLEEP_END]", $sleep['end']),
                        ));
                } else if($msg[$i]['type'] == 'goals') {
                    $g = $this->getGoal($msg[$i]['id']);
                    if($g['date'] == date("d.m.Y", time() - 24*3600)) {
                        //only from yesterday
                        updateJournal($g['date'], array(
                            array('[ACTIVITY]', "<b>".$g['points']."</b> / ".$g['targetPoints'])
                        )); 
                    }

                } else {
                    file_put_contents($msg[$i]['id'].".txt", $data);
                }
            }
        } else if($data['Type'] == 'SubscriptionConfirmation') {
            file_get_contents($data['SubscribeURL']);
        } else {
            print("No message!");
        }
    }

}

include('../Utils.php');
include('../evernote/artpi_Evernote.php');
$j = new Journal();


function updateJournal($date, $data) {
    global $j;
    $j->insertData($date, $data);
}

$payload = file_get_contents("php://input");
$m = new Misfit("fFvH6uyWIW9WoSAvSBil4EtOhsNA090YpFv9AkgFOUrrM0JzrzD3mspsER074VJ23HmRGiuNSLUCrddgZXKAbK1a12cwHxN4kDyTDphXD8Dt25zYeakjv81zoFAW5V5LdxrhQkTYXRlijv69XAUmpyIfCx0QNqFUY1rKms4nBiuRNpAwwEnZYLluLaLUYZZYHx8Pbgcbu2gWI7aRHYu4pE4aoHrZAHPiGcJDMszVRSiKLblogimI3uw6TXchj6q");
$m->endPoint($payload);


?>
