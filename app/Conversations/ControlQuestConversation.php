<?php

namespace App\Conversations;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;

class ControlQuestConversation extends Conversation{
  protected $selUser, $Quest, $QContent;
  public function run() {
    $this->selfUser = \App\TgUser::getByTg($this->bot->getUser()->getId());
    return $this->setQuest();
  }

  public function setQuest() {
    $question = Question::create("Список анкет. Или создайте новую.");
    foreach(\App\Quest::where('user_id', $this->selfUser->id)->orderBy('id', 'desc')->get() as $Quest) {
      $question->addButton(Button::create($Quest->title)->value('select_'.$Quest->id));
    }
    $question->addButton(Button::create('Добавить анкету')->value('add'));

    $this->ask($question, function (Answer $answer) {
        if ($answer->isInteractiveMessageReply()) {
          if($answer->getValue() === 'add') {
            $this->QuestNewTitle();
            //$this->mainmenu();
          } else {
            $ans = explode('_', $answer->getValue());
            $this->Quest = \App\Quest::where('id', $ans[1])->first();

            if ($ans[0] === 'select') {
              $this->setContent();
            }
          }
        } else {
          $this->say('Чёт ни то');
        }
      });
  }

  public function QuestNewTitle() {
    $question = Question::create("Название анкеты");
    $question->addButton(Button::create("Отмена")->value('cancel'));
    $Tclass=false;
    $this->ask($question, function (Answer $answer) {
      if ($answer->isInteractiveMessageReply()) {
        if($answer->getValue() === 'cancel') {
            return $this->setQuest();
          } else {
            return $this->setQuest();
          }
        } else {
          \App\Quest::firstOrCreate([
            'user_id' => $this->selfUser->id,
            'title' => $answer->getText()
          ]);
          return $this->setQuest();
        }
    });
  }

  public function setContent() {
    $question = Question::create("Столбцы");
    foreach(\App\QContent::where('quest_id', $this->Quest->id)->orderBy('id', 'asc')->get() as $QContent) {
      $question->addButton(Button::create($QContent->title)->value('select_'.$QContent->id));
    }
    $question->addButton(Button::create('Добавить столбец')->value('add'));

    $this->ask($question, function (Answer $answer) {
        if ($answer->isInteractiveMessageReply()) {
          if($answer->getValue() === 'add') {
            $this->QContentNewTitle();
          } else {
            $ans = explode('_', $answer->getValue());
            $this->QContent = \App\QContent::where('id', $ans[1])->first();

            if ($ans[0] === 'select') {
              //$this->getQContent();
            }
          }
        } else {
          $this->say('Чёт ни то');
        }
      });
  }

  public function QContentNewTitle() {
    $question = Question::create("Название стобца");
    $question->addButton(Button::create("Отмена")->value('cancel'));
    $Tclass=false;
    $this->ask($question, function (Answer $answer) {
      if ($answer->isInteractiveMessageReply()) {
        if($answer->getValue() === 'cancel') {
            return $this->setContent();
          } else {
            return $this->setContent();
          }
        } else {
          \App\QContent::firstOrCreate([
            'user_id' => $this->selfUser->id,
            'quest_id' => $this->Quest->id,
            'title' => $answer->getText()
          ]);
          return $this->setContent();
        }
    });
  }


}
