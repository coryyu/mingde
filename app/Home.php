<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Home extends Model
{
    //

    protected $table = 'app_home';
    protected $primaryKey = 'id';
    public $timestamps = false;//关闭 自动更新时间

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
