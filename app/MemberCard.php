<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MemberCard extends Model
{
    //
    protected $table = 'app_member_card';
    protected $primaryKey = 'id';
    public $timestamps = false; //关闭 自动更新时间

    protected $fillable = [

    ];

    const UPDATED_AT = 'update_at';
    const CREATED_AT = 'create_at';

    public function author()
    {
        return $this->belongsTo(MemberChange::class, 'cardid');
    }

    public function getCartName()
    {

        $res = $this->all();

            print_r($res);exit;
    }

}
