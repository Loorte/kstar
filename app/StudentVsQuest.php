<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentVsQuest extends Model {
  protected $fillable = [
    'user_id',
    'student_id',
    'quest_id'
  ];
}
