<?php
namespace App\Http\Controllers\Mingde;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\ClassProduct;

class MessageController extends CommonController
{

    /**
     *请求发送短信
     */
    public function SendSms(Request $request)
    {
        $phone = $request->input('phone');
    }

}