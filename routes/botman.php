<?php
use App\Http\Controllers\BotManController;

use BotMan\Drivers\Telegram\Extensions\Keyboard;
use BotMan\Drivers\Telegram\Extensions\KeyboardButton;

use App\Conversations\SiteAdminConversation;
use App\Conversations\StudentsConversation;
use App\Conversations\ControlQuestConversation;

$botman = resolve('botman');

$botman->hears('/start {user_id}', function ($bot, $user_id=0) {
  $sel_user = \App\TgUser::getByTg($bot->getUser()->getId());
  if(is_null($sel_user)) {
    $parent_user = \App\TgUser::where('id', $user_id)->first();
    if(!is_null($parent_user)) {
      \App\TgUser::create([
        'telegram_id' => $bot->getUser()->getId(),
        'first_name' => $bot->getUser()->getFirstName(),
        'last_name' => $bot->getUser()->getLastName(),
        'user_name' => $bot->getUser()->getUserName(),
        'parent_id' => $parent_user->id,
        ],
        $parent_user
      );
    } else { //Ели пользователь не найден то создаём коренного юзера
      \App\TgUser::updateOrCreate(
        [
          'telegram_id' => $bot->getUser()->getId(),
        ],
        [
          'first_name' => $bot->getUser()->getFirstName(),
          'last_name' => $bot->getUser()->getLastName(),
          'user_name' => $bot->getUser()->getUserName(),
        ]
      );


    }
    \App\TgUser::fixTree();
  }

});

$botman->hears('/start|START', function ($bot) {
  $User = \App\TgUser::updateOrCreate(
    [
      'telegram_id' => $bot->getUser()->getId(),
    ],
    [
      'first_name' => $bot->getUser()->getFirstName(),
      'last_name' => $bot->getUser()->getLastName(),
      'user_name' => $bot->getUser()->getUserName(),
    ]
  );

  \App\TgUser::fixTree(); //Фиксируем юзера
  $keyboard = Keyboard::create()->type(Keyboard::TYPE_KEYBOARD)
      ->addRow(
        KeyboardButton::create('Ученики')->callbackData('/students'),
        KeyboardButton::create('Анкеты')->callbackData('/config_quest'),
      )
      ->addRow(
        KeyboardButton::create('START')->callbackData('/start'),
      );
  $sel_user = \App\TgUser::getByTg($bot->getUser()->getId());

  if(!is_null($sel_user) && $sel_user->is_admin) {
      $keyboard = $keyboard->addRow(
          KeyboardButton::create('Настройка сервиса')->callbackData('/site_admin')
        );
  }
  $keyboard = $keyboard->toArray();

  $message = "Добро пожаловать ".$bot->getUser()->getFirstName();

  $bot->reply($message, $keyboard);

});

$botman->hears('/site_admin|Настройка сервиса', function($bot) {
  $User = \App\TgUser::getByTg($bot->getUser()->getId());
  if($User->is_admin)
    $bot->startConversation(new SiteAdminConversation);
  else {
    $bot->reply("Доступ запрещён");
  }
})->stopsConversation();

$botman->hears('/students|Ученики', function($bot) {
  //$User = \App\TgUser::getByTg($bot->getUser()->getId());
  //if($User->is_admin)
    $bot->startConversation(new StudentsConversation);
  /*else {
    $bot->reply("Доступ запрещён");
  }*/
})->stopsConversation();

$botman->hears('/config_quest|Анкеты', function($bot) {
  //$User = \App\TgUser::getByTg($bot->getUser()->getId());
  //if($User->is_admin)
    $bot->startConversation(new ControlQuestConversation);
  /*else {
    $bot->reply("Доступ запрещён");
  }*/
})->stopsConversation();
