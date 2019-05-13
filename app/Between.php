<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Between extends Model
{
    //
    protected $table = 'app_between';
    protected $primaryKey = 'id';
    public $timestamps = false; //关闭 自动更新时间
}
