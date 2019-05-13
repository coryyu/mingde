<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Search extends Model
{
    //
    protected $table = 'app_search';
    protected $primaryKey = 'id';
    public $timestamps = false;//关闭 自动更新时间
}
