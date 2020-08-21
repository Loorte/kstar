<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentVsQContent extends Model {
  protected $fillable = [
    'user_id',
    'student_id',
    'qcontent_id',
    'quest_id',
    'answer',
    'is_answer'
  ];
}
