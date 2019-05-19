<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class ClassText extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table='sch_classtext';
    protected $fillable = [
//        'name', 'phone','hospital_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
//        'password',
    ];
    public $timestamps = false; //关闭 自动更新时间

//    const UPDATED_AT='updated_at';
//    const CREATED_AT = 'create_at';

}
