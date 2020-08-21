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




  //------УПРАВЛЕНИЕ КЛАССОМ

  public function run() {
    $this->selfUser = \App\TgUser::getByTg($this->bot->getUser()->getId());
    return $this->setTClass();
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

  public function setTClass() {
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
              $this->setStudent();
            }
          }
        } else {
          $this->say('Чёт ни то');
        }
      });
  }

  public function setStudent() {
    $question = Question::create("Выбери ученика или добавьте нового");
      foreach(\App\Student::where('tclass_id', $this->TClass->id)->orderBy('fio')->get() as $Student) {
        $question->addButton(Button::create($Student->fio)->value('select_'.$Student->id));
      }
      $question->addButton(Button::create('Добавить ученика')->value('add'));
      $question->addButton(Button::create('<< Назад')->value('back'));

      $this->ask($question, function (Answer $answer) {
          if ($answer->isInteractiveMessageReply()) {
            if($answer->getValue() === 'add') {
              $this->StudentNewFio();
            } else if($answer->getValue() === 'back') {
              $this->setTClass();
            } else {
              $ans = explode('_', $answer->getValue());
              $this->Student = \App\Student::where('id', $ans[1])->first();

              if ($ans[0] === 'select') {
                $this->changeQuest();
              }
            }
          } else {
            $this->say('Чёт ни то');
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
              return $this->setStudent();
            } else {
              return $this->setStudent();
            }
          } else {
            \App\Student::firstOrCreate([
              'user_id' => $this->selfUser->id,
              'tclass_id' => $this->TClass->id,
              'fio' => $answer->getText()
            ]);
            return $this->setStudent();
          }
      });
    }

  public function changeQuest() {
    $question = Question::create("Отметьте анкеты необходимые для заполнения");
      foreach(\App\Quest::where('user_id', $this->selfUser->id)->get() as $Quest) {
      $Vs = \App\StudentVsQuest::where('quest_id', $Quest->id)->where('student_id', $this->Student->id)->first();
      $question->addButton(Button::create($Quest->title.(!is_null($Vs)?" ✅":""))->value('edit_'.$Quest->id));
    }
    $question->addButton(Button::create('<< Назад')->value('back'));
    $this->ask($question, function (Answer $answer) {
      if ($answer->isInteractiveMessageReply()) {
        if($answer->getValue() === "back") {
          $this->setStudent();
        } else {
          $ans = explode('_', $answer->getValue());

          $Vs = \App\StudentVsQuest::where('quest_id', $ans[1])->where('student_id', $this->Student->id)->first();
          if(is_null($Vs)) { //Если нету
            \App\StudentVsQuest::create([
              'student_id' => $this->Student->id,
              'quest_id' => $ans[1],
              'user_id' => $this->selfUser->id
            ]);
          } else {
            \App\StudentVsQuest::where('quest_id', $ans[1])->where('student_id', $this->Student->id)->delete();
          }
          $this->changeQuest();
        }
      }
    });
  }
}
