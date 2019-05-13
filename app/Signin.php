<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Signin extends Model
{
    //
    protected $table = 'app_signin';
    protected $primaryKey = 'id';
    public $timestamps = false;//关闭 自动更新时间
}
