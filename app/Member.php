<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    //
    protected $table = 'app_member';
    protected $primaryKey = 'id';
    public $timestamps = false; //关闭 自动更新时间

    protected $fillable = [
        'name', 'add','mem_type','create_time','telephone','update_time','end_time','oid','usr','province','city','total','grant_num','do_test','contaminated','is_del','pay','channel'
    ];

}
