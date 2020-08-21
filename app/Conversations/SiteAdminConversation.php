<?php

namespace App\Conversations;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;

class SiteAdminConversation extends Conversation
{
    /**
     * Start the conversation.
     *
     * @return mixed
     */

    protected $user_id;
    public function setUserId($user_id) {
      return $this->user_id = $user_id;
    }
    public function getUserId() {
      return $this->user_id;
    }
    protected $q = null;
    public function setQ($q) {
      return $this->q = $q;
    }
    public function getQ() {
      return $this->q;
    }
    public function setIsAdmin() {
      $user = \App\TgUser::where('id', $this->getUserId())->first();
      $user->is_admin = !$user->is_admin;
      $user->save();
      return true;
    }



    public function run()
    {
      return $this->mainmenu();
    }

    public function mainmenu() {
      $User = \App\TgUser::getByTg($this->bot->getUser()->getId());
      $question = Question::create("Настройка сервиса");
      if($User->is_admin) {
        $question->addButtons([
                Button::create('Пользователи')->value('users')
            ]);
      }
      $this->ask($question, function (Answer $answer) {
          if ($answer->isInteractiveMessageReply()) {
            if ($answer->getValue() === 'users') {
              $this->users();
            }
          } else {
            $this->say("Ok!");
          }
        });
    }

    public function users() {
      if(!is_null($this->getQ())) {
        $users = \App\TgUser::where('user_name', 'like', "%".$this->getQ()."%")->orWhere('first_name', 'like', "%".$this->getQ()."%")->orWhere('last_name', 'like', "%".$this->getQ()."%")->get();
      } else {
        $users = \App\TgUser::get();
      }

      $question = Question::create("Пользователи /site_admin");
      foreach($users as $user) {
        $question->addButton(Button::create($user->first_name."[".($user->user_name ?? $user->telegram_id)."]")->value('edit_'.$user->id));
      }

      if(!is_null($this->getQ())) {
        $question->addButton(Button::create('Сбросить фильтр')->value('reset'));
      }

      $question->addButton(Button::create('<< Назад')->value('back'));
      $this->ask($question, function (Answer $answer) {
          if ($answer->isInteractiveMessageReply()) {
            if($answer->getValue() === 'back') {
              $this->setQ(null);
              $this->mainmenu();
            } else if($answer->getValue() === 'reset') {
              $this->setQ(null);
              $this->users();
            } else {
              $ans = explode('_', $answer->getValue());
              $this->setUserId($ans[1]);

              if ($ans[0] === 'edit') {
                $this->getUser();
              }
            }
          } else {
            $this->setQ($answer->getText());
            $this->users();
          }
        });
    }


    public function getUser() {
      $user_id = $this->getUserId();
      $user = \App\TgUser::where('id', $user_id)->first();
      $message = "Логин: $user->user_name";
      $question = Question::create($message);

      if($user->is_admin)
        $question->addButton(Button::create('Роль администратора ✅')->value('is_admin'));
      else {
        $question->addButton(Button::create('Роль администратора ☑️')->value('is_admin'));
      }

      $question->addButton(Button::create('<< Назад')->value('back'));

      $this->ask($question, function (Answer $answer) {
          if ($answer->isInteractiveMessageReply()) {
            if($answer->getValue() === 'is_admin') {
              $this->setIsAdmin();
              $this->getUser();
            } else if($answer->getValue() === "back") {
                $this->users();
            }

          }
      });
    }
}
