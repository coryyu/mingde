<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MemberIndex extends Model
{
    //
    protected $table = 'app_member_index';
    protected $primaryKey = 'id';
    public $timestamps = false; //关闭 自动更新时间
}
