<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TableAlgo extends Model
{
    //
    protected $table = 'app_table_algo';
    protected $primaryKey = 'id';
    public $timestamps = false; //关闭 自动更新时间
}
