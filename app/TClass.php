<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TClass extends Model
{
    protected $fillable = [
      'user_id',
      'title'
    ];
}
