<?php
use Discord\Discord;
use Discord\WebSockets\Event;
use Discord\WebSockets\Intents;
require_once('./vendor/autoload.php');

$env = parse_ini_file('.env');
$key = $env['KEY'];

$discord = new Discord(['token'=>$key]);

function fateDice($turns) {
    $result = array();
    $number = 0;
    for($i = 0; $i < $turns; $i++) {
        $rollValue = floor((mt_rand() / mt_getrandmax()) * 3) - 1;
        echo $rollValue;
        $text = '';
        if($rollValue == -1) {
            $text = '-';
            $number--;
        }
        else if($rollValue == 0) {
            $text = '0';
        }
        else if($rollValue == 1) {
            $text = '+';
            $number++;
        }
        array_push($result, $text);
    }
    $resultStr = '[ '. implode(',', $result) . ' ]';
    return array(
        'text' => $resultStr,
        'number' => (int)$number
    );
}

function randomDice($turns, $max) {
    $result = array();
    for($i = 0; $i < $turns; $i++) {
        $rollValue = floor((mt_rand() / mt_getrandmax()) * $max) + 1;
        array_push($result, $rollValue);
    }
    return '[ '. implode(',', $result) . ' ]';
}

$discord->on('ready', function(Discord $discord) {
    $discord->on('message', function($message, $discord) {
        if(str_starts_with($message->content, '!')) {
            $fate = '/[0-9]*dF/';
            $bonus = '/a[0-9]*/';
            $random = '/[0-9]*d[0-9]*/';
            if(preg_match($fate, $message->content, $matches)) {
                $turns = explode('dF', $matches[0])[0];
                $result = fateDice($turns);
                $resultNumber = $result['number'];
                $resultText = $result['text'];
                if(preg_match($bonus, $message->content, $matches2)) {
                    $numberBonus = (int)explode('a', $matches2[0])[1];
                    if($numberBonus <= 2) {
                        $resultNumber--;
                    }
                    else if($numberBonus >= 5 && $numberBonus <= 7) {
                        $resultNumber++;
                    }
                    else if($numberBonus == 8) {
                        $resultNumber += 2;
                    }
                    $message->reply("Résultat : $resultText \nTotal (avec bonus d'attribut) : $resultNumber");
                }
                else {
                    $message->reply("Résultat : $resultText \nTotal (sans bonus d'attribut) : $resultNumber");
                }
            }
            else if(preg_match($random, $message->content, $matchesRandom)) {
                $tmp = explode('d', $matchesRandom[0]);
                $randomResult = randomDice((int)$tmp[0], (int)$tmp[1]);
                $message->reply("Résultat : $randomResult");
            }
        }
    });
});

$discord->run();
