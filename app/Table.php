<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    //
    protected $table = 'app_table';
    protected $primaryKey = 'id';
    public $timestamps = false;//关闭 自动更新时间

}
