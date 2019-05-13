<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IntegralConfig extends Model
{
    //
    protected $table = 'app_integral_config';
    protected $primaryKey = 'id';
    public $timestamps = false; //关闭 自动更新时间
}
