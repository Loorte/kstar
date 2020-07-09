<?php
use App\Http\Controllers\BotManController;

use BotMan\Drivers\Telegram\Extensions\Keyboard;
use BotMan\Drivers\Telegram\Extensions\KeyboardButton;

$botman = resolve('botman');

/*СТАРТ*/
$botman->hears('/start|start|START', function ($bot) {
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

  $keyboard = Keyboard::create()->type(Keyboard::TYPE_KEYBOARD)
      ->addRow(
          KeyboardButton::create('START')->callbackData('/start')
          )
      ->toArray();

  $message = "Добро пожаловать ".$bot->getUser()->getFirstName().", \nВы находитесь в главном меню сервиса по организации передержки животных\nВыберите один из пунктов меню:";

  $bot->reply($message,$keyboard);

});
