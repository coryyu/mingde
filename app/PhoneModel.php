<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhoneModel extends Model
{
    //
    protected $table = 'app_model';
    protected $primaryKey = 'id';
//    public $timestamps = false; //关闭 自动更新时间
    const UPDATED_AT='update_at';
    const CREATED_AT = 'create_at';

}
