<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QContent extends Model {
  protected $fillable = [
    'user_id',
    'quest_id',
    'title'
  ];
}
