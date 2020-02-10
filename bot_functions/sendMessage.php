<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 22.11.2018
 * Time: 13:56
https://api.telegram.org/bot701374217:AAHBjH23Ljb3-3QsIRpF_qb6ZhU2r62EyfM/setWebhook?url=https://mingaleev99.000webhostapp.com/bot_functions/sendMessage.php
 */
define('BOT_TOKEN', '701374217:AAHBjH23Ljb3-3QsIRpF_qb6ZhU2r62EyfM');
define('API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');
define('GROUP_INVITE', 'https://t.me/joinchat/AAAAAE6T7s_0YO8UFs3Klg');
define('GROUP_ID', -1001318317775);

include_once 'functions.php';
include_once '../connection.php';

$last_films = array();

$connection = mysqli_connect($host,$user,$password,$database) or die("Ошибка при соединении ".mysqli_error($link));

//получение последних добавленных фильмов и их запись в переменную
$results = mysqli_query($connection, "SELECT * from last_films order by id") or die("Error to load list");
if ($results){
    for ($i = 0; $i < mysqli_num_rows($results); ++$i) {
        global $last_films;
        $last_films[] = mysqli_fetch_row($results)[1];
    }
}

//принимаем запрос от бота(то что напишет в чате пользователь)
$content = file_get_contents('php://input');
//превращаем из json в массив
$update = json_decode($content, TRUE);
//получаем id пользователя
$chat_id = $update['message']['chat']['id'];
//получаем текст запроса
$text = $update['message']['text'];
// получаем id чата с пользователем

switch ($text){
    case "/start":
        showKeyboard($chat_id);
        break;
    case "Список последних добавленных фильмов":
        sendLastAddedFilms();
        break;
    case "тест":
        initBot()->sendMessage($chat_id, "Все работает как и положено!");
        $last_added_films = array(
            'name' => [],
            'link' => [],
            'img_url' => []
        );
        $last_added_films['name'][] = 'Лолка';
        $last_added_films['link'][] = 'http://filmitorrent.org/triller/3195-motylek-2017.html';
        $last_added_films['img_url'][] = 'http://filmitorrent.org/engine/cache/kp_rating/939411.gif';
        notifyBot($last_added_films);
        break;
    default:
        inviteToGroup($text,GROUP_INVITE, $chat_id);
}

//запись в лог
teleToLog($update);

function notifyBot($film_name, $film_link, $film_rate){
    $bot = initBot();

    $message = "Новый фильм: \n".$film_name."\nСсылка:\n".$film_link;
    $bot->sendMessage(GROUP_ID, $message);
    if (!empty($film_rate)) {
        $media = new \TelegramBot\Api\Types\InputMedia\ArrayOfInputMedia();
        $media->addItem(new \TelegramBot\Api\Types\InputMedia\InputMediaPhoto($film_rate, "Рейтинг фильма: " . $film_name . " на кинопоиске"));
        $bot->sendMediaGroup(GROUP_ID, $media);
    }
    $bot->run();
}

function d_notifyBot($error){
    $bot = initBot();
    $bot->sendMessage(355353616, $error);
    $bot->run();

}

function initBot(){
    include_once 'bot_lib/vendor/autoload.php';
    return $bot = new \TelegramBot\Api\Client(BOT_TOKEN);
}

function sendLastAddedFilms(){
    global $chat_id;
    global $last_films;
    if (!empty($last_films)){
        sendLastFilms($chat_id, $last_films);
    } else{
        sendLastFilms($chat_id, ["Новых фильмов не выходило"]);
    }
}