<?php
session_start(); 
/**
 * This example reads a recorded game file and displays a lot of data about it,
 * in plain PHP. You can use this as a reference for building your own recorded
 * game data overview.
 *
 * It's better to use a template engine of some variety, because they will
 * normally html-escape variables for you. Here, it's done manually using the
 * `htmlentities` function, but that's easy to forget! Twig is a good choice — a
 * Twig-based example is available in the examples/tabbed/ directory. You can
 * use any template engine you want with RecAnalyst, though!
 */

require  'autoload.php';

use RecAnalyst\RecordedGame;
use RecAnalyst\Utils;
use Intervention\Image\ImageManagerStatic;


if(isset($_FILES['record']))
{
   $filename = $_FILES['record']['name'];
   $filename =fopen($_FILES["record"]["tmp_name"], 'r');
  /* $info = new SplFileInfo($filename);
  if($info->getExtension()!=".mgz");
  {
     $filename =str_replace($info->getExtension(),".mgz", $filename);
  }*/
}
else
{
    $filename =  'test.mgz';

}

// Define an alias to the htmlentities function so it's easier to type.
if (!function_exists('e')) {
    function e($val)
    {
        return html_entity_decode ($val);
    }
}

if (!function_exists('getResearchImage')) {
    function getResearchImage($research)
    {
        $path =   __DIR__ .'/recanalyst/recanalyst/resources/images/researches/' . $research->id . '.png';

        if (is_file($path)) {
            // Turn the image into a data URL.
            return ImageManagerStatic::make($path)->encode('data-url');
        }
        return '';
    }
}
 function getCivImage($player)
{                       
     $p =   __DIR__ .'/recanalyst/recanalyst/resources/images/civs/'. $player->getIDcolor().'/' . $player->getcivId() . '.png';
      if (is_file($p)) {
            // Turn the image into a data URL.
            return  ImageManagerStatic::make($p)->encode('data-url');
          }
        return '';
}
function ageLogo($nb)
{
     $p =   __DIR__ .'/recanalyst/recanalyst/resources/images/researches/'.$nb.'.png';
     if (is_file($p)) {
    return ImageManagerStatic::make($p)->encode('data-url');
    }
}
$rec = new RecordedGame($filename);

// In a real app, it's better to save the image using the ->save() method, and
// link to the stored image in your HTML page. For this example, we'll just
// inline the image as a Data URL, because it's easier.
$mapImage = $rec->mapImage()
    ->resize(300, 150)
    ->encode('data-url');

?>
<!DOCTYPE html>
<html>
<head>
    <title>RecAnalyst demo</title>
    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
          crossorigin="anonymous">
    <style>
        .Page {
            width: 1000px;
            background-color: #cacaca;
        }

        .Category {
            max-height: 540px;
            overflow-y: auto;
            padding: 20px;
        }

        .General {
            display: flex;
            justify-content: space-around;
        }

        .Teams {
            display: flex;
        }
        .Team {
            margin: 20px;
        }

        .Advancing-player {
            width: 200px;
            padding: 10px;
        }

        .Player-name {
            text-align: center;
            font-weight: bold;
        }
        .Player-civ {
            float: center;
             text-align: center;
            font-size: smaller;
        }
        .small {
            font-size: 0.75rem;
             text-align: center;
        }
        .playercivname {
            float: left;
            margin-left: 5px;
            text-align: left;
        }
        .ResearchesLine {
        }
        .ResearchesLine-player {
            float: left;
            width: 200px;
        }

        .Research {
            float: left;
            text-align: center;
            margin-left: 10px;
        }
        .Research-time {
            color: #444;
        }

        .u-playerColor {
            /* To make colours a bit more bearable: put a 50% opaque white layer on top of them. */
            background-image: linear-gradient(0deg, rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5));
        }
.tooltip {
  position: relative;
  display: inline-block;
  /*border-bottom: 1px dotted black;*/ /* If you want dots under the hoverable text */
}

/* Tooltip text */
.tooltip .tooltiptext {
  visibility: hidden;
  width: 120px;
  background-color: black;
  color: #fff;
  text-align: center;
  padding: 5px 0;
  border-radius: 6px;
 
  /* Position the tooltip text - see examples below! */
  position: absolute;
  z-index: 1;
}

/* Show the tooltip text when you mouse over the tooltip container */
.tooltip:hover .tooltiptext {
  visibility: visible;
}
    </style>
</head>
<body>

    <div class="container Page">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#general" aria-controls="general" role="tab" data-toggle="tab">
                    General
                </a>
            </li>
            <!--<li role="presentation">
                <a href="#achievements" aria-controls="achievements" role="tab" data-toggle="tab">
                    Achievements
                </a>
            </li>-->
            <li role="presentation">
                <a href="#advancing" aria-controls="advancing" role="tab" data-toggle="tab">
                    Advancing
                </a>
            </li>
            <li role="presentation">
                <a href="#chat" aria-controls="chat" role="tab" data-toggle="tab">
                    Chat
                </a>
            </li>
            <li role="presentation">
                <a href="#researches" aria-controls="researches" role="tab" data-toggle="tab">
                    Researches
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane Category active" id="general">
                <div class="General">
                    <dl class="General-info dl-horizontal">
                        <dt>Version</dt>
                        <dd><?= $rec->version()->name() ?></dd>

                        <dt>Duration</dt>
                        <dd><?= Utils::formatGameTime($rec->body()->duration) ?></dd>

                        <dt>Type</dt>
                        <dd><?= $rec->gameSettings()->gameTypeName() ?></dd>

                        <dt>Map</dt>
                        <dd><?= e($rec->gameSettings()->mapName()) ?></dd>

                        <dt>PoV</dt>
                        <dd><?= $pov ? e($pov->name) : 'Unknown' ?></dd>
                    </dl>
                    <div class="General-map">
                        <img src="<?= $mapImage ?>">
                    </div>
                </div>
                <h2>Teams</h2>
                <div class="Teams">
                    <?php foreach ($rec->teams() as $team) { ?>
                        <div class="Team">
                            <strong>Team <?= $team->index() ?></strong>
                            <?php foreach ($team->players() as $player) { ?>
                                <div class="Player">
                                    <img class="Player-img" src="<?= getCivImage($player) ?>">
                                    <strong class="Player-name" style="color: <?= $player->color() ?>">
                                        <?= e($player->name) ?>
                                    </strong>
                                
                                    <br>
                                    <span class="small"><?= e($player->civName()) ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane Category" id="achievements">
                <div class="Achievements">
                    <?php if ($rec->achievements()) { ?>
                        <?php foreach ($rec->players() as $player) { ?>
                            <strong><?= e($player->name) ?></strong>
                            <?= json_encode($player->achievements()) ?>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane Category" id="advancing">
                <div class="Advancing Teams">
                    <?php foreach ($rec->teams() as $team) { ?>
                        <div class="Advancing-team Team">
                            <strong>Team <?= $team->index() ?></strong>
                            <?php foreach ($team->players() as $player) { ?>
                                <div class="Advancing-player Player u-playerColor"
                                    style="background-color: <?= $player->color() ?>">
                                     <img class="Player-img" src="<?= getCivImage($player) ?>">
                                    <p class="Player-name">
                                        <?= e($player->name) ?> <!--<small>(<?= e($player->civName()) ?>)</small>-->
                                    </p>
                                    <ol class="list-unstyled">
                                        
                                        <li><img class="Research-img"  src="<?=ageLogo('101');?>" > <?= Utils::formatGameTime($player->feudalTime) ?></li>
                                        <li><img class="Research-img"  src="<?=ageLogo('102');?>" > <?= Utils::formatGameTime($player->castleTime) ?></li>
                                        <li><img class="Research-img"  src="<?=ageLogo('103');?>" > <?= Utils::formatGameTime($player->imperialTime) ?></li>
                                        <!--
                                        <li>Feudal: <?= Utils::formatGameTime($player->feudalTime) ?></li>
                                        <li>Castle: <?= Utils::formatGameTime($player->castleTime) ?></li>
                                        <li>Imperial: <?= Utils::formatGameTime($player->imperialTime) ?></li>-->
                                    </ol>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane Category" id="chat">
                <div class="Chat">
                    <div class="Chat-pregame">
                        <h3>Pregame</h3>
                        <?php foreach ($rec->header()->pregameChat as $message) { ?>
                            <div class="ChatMessage">
                                <span class="ChatMessage-sender"><?= e($message->player->name) ?></span>:
                                <?= e($message->msg) ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="Chat-ingame">
                        <h3>In-game</h3>
                        <?php foreach ($rec->body()->chatMessages as $message) { ?>
                            <div class="ChatMessage">
                                <span class="ChatMessage-time">
                                    <?= Utils::formatGameTime($message->time) ?>
                                </span>
                                <?php if ($message->player) { ?>
                                    <span class="ChatMessage-sender" style="color: <?= $message->player->color() ?>">
                                        <?= e($message->player->name) ?>
                                    </span>:
                                    <?= e($message->msg) ?>
                                <?php } else { ?>
                                    <em><?= e($message->msg) ?></em>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane Category" id="researches">
                <div class="Researches">
                    <?php foreach ($rec->players() as $player) { ?>
                        <div class="Researches-line clearfix ResearchesLine u-playerColor"
                            style="background-color: <?= $player->color() ?>">
                            <div class="ResearchesLine-player">
                                
                                <p class="Player-name">
                                        <?= e($player->name) ?> <!--<small>(<?= e($player->civName()) ?>)</small>-->
                                    </p>
                            </div>
                            <div class="ResearchesLine-researches">
                                <?php foreach ($player->researches() as $research) { ?>
                                    
                                    <div class="tooltip" style="    opacity: 1; z-index: auto; "><!--Research-->

                                          
                                            <img class="Research-img" src="<?= getResearchImage($research) ?>">
                                            <div class="Research-time"><?= Utils::formatGameTime($research->time) ?></div>
                                             <span class="tooltiptext"  ><?= e($research->name()) ?></span>
 
    <!--
                                            <img  class="Research-img" src="<?= getResearchImage($research) ?>" /> 
                                          <span class="tooltiptext"><?= e($research->name()) ?></span>
                                          <div class="Research-name"><?= e($research->name()) ?></div>-->

                                    </div>

                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="container">

      <form action = "" method = "POST" enctype = "multipart/form-data">
         <input type = "file" name = "record" />
         <input type = "submit"/>
            <script type="text/javascript">
                

            </script>
 
            
      </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
            crossorigin="anonymous"></script>
</body>
</html>