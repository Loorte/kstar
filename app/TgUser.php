<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TgUser extends Model
{
  protected $fillable = [
    'telegram_id',
    'first_name',
    'last_name',
    'user_name'
  ];
}
