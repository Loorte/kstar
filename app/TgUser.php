<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class TgUser extends Model
{
  use NodeTrait;

  protected $fillable = [
    'telegram_id',
    'first_name',
    'last_name',
    'user_name',
    'parent_id',
    '_lft',
    '_rgt',
    'is_admin'
  ];

  //Вернуть юзера по боту
  public static function getByTg($telegram_id) {
    return self::where('telegram_id', $telegram_id)->first();
  }

  //Пользователи в дерерве
  public static function GetTreesLine($id, $line) {
        //self::fixTree();
    $result = self::withDepth()->find($id);
    $level = $result->depth+$line;
    return self::withDepth()
        ->whereRaw("(select count(1) - 1 from `tg_users` as `_d` where `tg_users`.`_lft` between `_d`.`_lft` and `_d`.`_rgt`) < $level")
        ->orderBy('id', 'desc')
        ->descendantsOf($id);
  }

  //Количество в дереве
  public static function getTreesLineCount($id, $line) {
    $des = self::GetTreesLine($id, $line);
    return $des->count();
  }
}
