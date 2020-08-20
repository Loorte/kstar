<?php
use App\Http\Controllers\BotManController;

use BotMan\Drivers\Telegram\Extensions\Keyboard;
use BotMan\Drivers\Telegram\Extensions\KeyboardButton;

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
          KeyboardButton::create('START')->callbackData('/start')
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
