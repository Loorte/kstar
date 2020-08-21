<?php

namespace App\Conversations;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;

class StudentsConversation extends Conversation {
  /*
  Управление Классом
  */
  //Объекты
  protected
    $TClass,
    $Student,
    $selfUser;

  //Функции
  public function setTClass($TClass) {
    return $this->TClass = $TClass;
  }
  public function getTClass() {
    $question = Question::create("Выбери ученика или добавьте нового");
    foreach(\App\Student::where('tclass_id', $this->TClass->id)->orderBy('fio')->get() as $Student) {
      $question->addButton(Button::create($Student->fio)->value('select_'.$Student->id));
    }
    $question->addButton(Button::create('Добавить ученика')->value('add'));

    $this->ask($question, function (Answer $answer) {
        if ($answer->isInteractiveMessageReply()) {
          if($answer->getValue() === 'add') {
            $this->StudentNewFio();
            //$this->mainmenu();
          } else {
            $ans = explode('_', $answer->getValue());
            //$this->setUserId($ans[1]);

            if ($ans[0] === 'select') {
              $this->getTClass();
            }
          }
        } else {
          $this->say('Чёт ни то');
        }
      });
  }

  public function TClassNewTitle() {
    $question = Question::create("Введите название нового класса");
    $question->addButton(Button::create("Отмена")->value('cancel'));
    $Tclass=false;
    $this->ask($question, function (Answer $answer) {
      if ($answer->isInteractiveMessageReply()) {
        if($answer->getValue() === 'cancel') {
            return $this->setTClassID();
          } else {
            return $this->setTClassID();
          }
        } else {
          \App\TClass::firstOrCreate([
            'user_id' => $this->selfUser->id,
            'title' => $answer->getText()
          ]);
          return $this->setTClassID();
        }
    });
  }


  public function StudentNewFio() {
    $question = Question::create("Введите Фамилию Имя Отчество");
    $question->addButton(Button::create("Отмена")->value('cancel'));
    $Tclass=false;
    $this->ask($question, function (Answer $answer) {
      if ($answer->isInteractiveMessageReply()) {
        if($answer->getValue() === 'cancel') {
            return $this->getTClass();
          } else {
            return $this->getTClass();
          }
        } else {
          \App\Student::firstOrCreate([
            'user_id' => $this->selfUser->id,
            'tclass_id' => $this->TClass->id,
            'fio' => $answer->getText()
          ]);
          return $this->getTClass();
        }
    });
  }

  //------УПРАВЛЕНИЕ КЛАССОМ

  public function run() {
    $this->selfUser = \App\TgUser::getByTg($this->bot->getUser()->getId());
    return $this->setTClassID();
  }

  public function setTClassID() {
    $question = Question::create("Выбери класс или создай новый");
    foreach(\App\TClass::where('user_id', $this->selfUser->id)->orderBy('title')->get() as $TClass) {
      $question->addButton(Button::create($TClass->title)->value('select_'.$TClass->id));
    }
    $question->addButton(Button::create('Добавить класс')->value('add'));

    $this->ask($question, function (Answer $answer) {
        if ($answer->isInteractiveMessageReply()) {
          if($answer->getValue() === 'add') {
            $this->TClassNewTitle();
            //$this->mainmenu();
          } else {
            $ans = explode('_', $answer->getValue());
            $this->TClass = \App\TClass::where('id', $ans[1])->first();

            if ($ans[0] === 'select') {
              $this->getTClass();
            }
          }
        } else {
          $this->say('Чёт ни то');
        }
      });
  }
}
