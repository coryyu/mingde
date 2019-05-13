<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kouxrecord extends Model
{
    //
    protected $table = 'app_kou_xxrecord';
    protected $primaryKey = 'id';
    public $timestamps = false; //关闭 自动更新时间
}
