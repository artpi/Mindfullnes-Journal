<?php
set_include_path(get_include_path() . PATH_SEPARATOR . EVERNOTE_API_PATH);


use EDAM\Types\Data, EDAM\Types\Note, EDAM\Types\Resource, EDAM\Types\ResourceAttributes;
use EDAM\Error\EDAMUserException, EDAM\Error\EDAMErrorCode;
use Evernote\Client;

require_once 'autoload.php';
require_once 'Evernote/Client.php';
require_once 'packages/Errors/Errors_types.php';
require_once 'packages/Types/Types_types.php';
require_once 'packages/Limits/Limits_constants.php';

class artpi_Evernote {
    private $token = EVERNOTE_TOKEN;
    public $client;

    function init() {
        if(!isset($this->client)) {
            $this->client = new Evernote\Client(array(
                'token' => $this->token,
                'sandbox' => false
            ));
        }
    }

    function getNoteBySearch($words) {
        $this->init();
        $filter = new \EDAM\NoteStore\NoteFilter();
        $filter->words = $words;

        try {
            $n = $this->client->getNoteStore()->findNotes($filter, 0, 1);

            if(count($n->notes) > 0) {
                $note = $n->notes[0];
                $note->content = $this->client->getNoteStore()->getNoteContent($note->guid);
                return $note;
            } else {
                return false;
            }

        } catch (EDAMUserException $edue) {
            print "EDAMUserException[".$edue->errorCode."]: " . $edue;
        } catch (EDAMNotFoundException $ednfe) {
            print "EDAMNotFoundException: Invalid parent notebook GUID";
        }
    }

    function updateNote($note) {
        try {
            $this->client->getNoteStore()->updateNote($note);
        } catch (EDAMUserException $edue) {
            print "EDAMUserException[".$edue->errorCode."]: " . $edue;
        } catch (EDAMNotFoundException $ednfe) {
            print "EDAMNotFoundException: Invalid parent notebook GUID";
        }

    }

    function appendMedia($note, $url, $attr="") {
        $image = file_get_contents($url);
        $hash = md5($image,1);
        $data = new Data();
        $data->size = strlen($image);
        $data->bodyHash = $hash;
        $data->body = $image;
        
        $r = new Resource();
        $r->data = $data;
        $r->mime = "image/png";
        $note->resources[]=$r;

        return '<en-media '.$attr.' type="image/png" hash="'.md5($image,0).'" />';
    }

    function getNotesByTag($tagGuid, $count) {
        $this->init();
        $filter = new \EDAM\NoteStore\NoteFilter();
        $filter->tagGuids = array($tagGuid);
        $n = $this->client->getNoteStore()->findNotes($filter, 0, $count);
        return $n->notes;
    }

    function getNoteLink($note) {
        $this->init();
        $user = $this->client->getUserStore()->getUser();
        $url = "evernote:///view/".$user->id."/".$user->shardId."/".$note->guid."/".$note->guid."/";

        return("<a href='".$url."'>".$note->title."</a>");
    }

}




class Journal extends artpi_Evernote {
    public $calendar;
    public $picasa;
    public $db;
    public $note;
    public $newNoteNotebook = EVERNOTE_NOTEBOOK;
    public $templateNote = EVERNOTE_JOURNAL_TEMPLATE;
    public $todoTag = EVERNOTE_TODO_TAG;
    public $newNoteTag = EVERNOTE_TODO_TAG;
    public $googleCalendars;


    function __construct() {
        $this->db = new PDO(DB, DB_USER, DB_PASS);
        $this->googleCalendars = explode(";", JOURNAL_GOOGLE_CALENDARS );
        $this->calendar = new Calendar();
        $this->calendar->auth($this->db, 'calendar');

        $this->picasa = new Picasa();
        $this->picasa->auth($this->db, 'picasa');

    }

    function updateNote() {
        try {
            $this->client->getNoteStore()->updateNote($this->note);
        } catch (EDAMUserException $edue) {
            print "EDAMUserException[".$edue->errorCode."]: " . $edue;
            print("/nPropably wrong enml:/n");
            print($this->note->content);
        } catch (EDAMNotFoundException $ednfe) {
            print "EDAMNotFoundException: Invalid parent notebook GUID";
        }
    }

    function replace($source, $target) {
        $this->note->content = str_replace($source, $target, $this->note->content);
    }

    function createDailyNote() {
        $day = date('d.m.Y');

        $this->init();
        $this->note = $this->client->getNoteStore()->copyNote($this->templateNote, $this->newNoteNotebook);
        $this->db->exec("INSERT INTO journalNotes(guid, day) VALUES ('".$this->note->guid."', '".$day."');");


        $this->note->content = $this->client->getNoteStore()->getNoteContent($this->note->guid);
        $this->note->tagGuids[] = $this->newNoteTag;
        $this->note->title = "Journal ".$day;

        $this->insertQuote();

        $this->insertCalendar(date('Y-m-d'));

        $this->insertTodo();

        //echo $this->note->content;
        $this->updateNote();
    }

    function insertQuote() {
        $n = $this->getNotesByTag('00be54ac-cbaa-47a1-ae10-a547af8441ec', 1000);
        $index = rand(0,count($n)-1);
        $cytat = trim(strip_tags($this->client->getNoteStore()->getNoteContent($n[$index]->guid)));
        $this->replace("[CYTAT]", $cytat);
    }

    function insertCalendar($day) {
        $events = $this->calendar->getMultipleCalendars($this->googleCalendars, $day, $day);
        $out="";
        foreach ($events as $key => $event) {
            $out.="<li><en-todo/><a href='".$event['htmlLink']."'>".date("H:i", strtotime($event['start']['dateTime']))." ".$event['summary']."</a></li>\n";
        }

        if(strlen($out) > 3) {
            $out .= "<li><en-todo/></li>
</ol>";
            $this->replace("<li><en-todo/></li>
</ol>", $out);
        }

        
    }

    function insertTodo() {
        $out="";
        $n = $this->getNotesByTag($this->todoTag, 100);
        for ($i=0; $i < count($n); $i++) { 
            $value = $n[$i];
            $out.="<li><en-todo/>".$this->getNoteLink($value)."</li>";
        }

        if(strlen($out) > 3) {
            $out .= "<li><en-todo/></li>
</ol>";
            $this->replace("<li><en-todo/></li>
</ol>", $out);
        }
    }

    function insertPhotos($photos) {
        $im = '';
        for ($i=0; $i < count($photos); $i++) { 
            $this->db->exec("INSERT INTO journal_g_photos(id, time, note) VALUES ('".$photos[$i]['id']."', '".$photos[$i]['time']."', '".$this->note->guid."');");
            $im .= '<a href="'.$photos[$i]['url'].'">'.$this->appendMedia($this->note, $photos[$i]['src'], 'style="float:left;margin:5px;margin-left:10px;height:160px"').'</a>';
        }

        if(stristr($this->note->content, "<h3>Photos</h3>")) {
            $this->note->content = preg_replace("#<h3>Photos<\/h3><div>(.*?)<\/div>#is", "<h3>Photos</h3><div>$1".$im."</div>", $this->note->content);
        } else {
            $this->note->content = str_replace("</en-note>", "<h3>Photos</h3><div>".$im."</div></en-note>", $this->note->content);
        }

        $this->updateNote();

    }

    function createDailyPhotos() {
        $p = $this->picasa->getNewPhotos(20);

        $photos = array();

        for ($i=0; $i < count($p); $i++) { 
            $day = date("d.m.Y",$p[$i]['time']);
            $cnt = $this->db->query("SELECT COUNT(id) AS cnt FROM `journal_g_photos` WHERE id='".$p[$i]['id']."';")->fetch();
            if($cnt['cnt']=='0') {

                if(!isset($photos[$day])) {
                    $photos[$day] = array();
                }

                $photos[$day][] = $p[$i];
            }
        }


        foreach ($photos as $key => $value) {
            $this->note = $this->getNoteBySearch('intitle:"Journal '.$key.'"');
            $this->insertPhotos($value);
        }
    }

    function insertData($date, $data) {
        $this->init();
        $this->note = $this->getNoteBySearch('intitle:"Journal '.$date.'"');
        for ($i=0; $i < count($data); $i++) { 
            $this->replace($data[$i][0], $data[$i][1]);
        }
        $this->updateNote();

        //print($this->note->content);
    }


}

?>
