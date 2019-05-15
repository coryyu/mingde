<?php
namespace App\Http\Controllers\Mingde;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\ClassChannel;

class CommonController extends Controller
{


    public $userinfo;
    public $channel;

    function __construct(Request $request) {

        //token 获取用户信息

        $token = $request->input('token');
        $appid = $request->input('appid');

        $channel = ClassChannel::where('number',$appid)->first();
        if($channel){
            $this->channel = $channel;
        }else{
            echo api_json([],10000,'appid不存在');
            exit;
        }
        $userinfo = DB::table('sch_user')
            ->where('token',$token)
            ->where('is_del',0)
            ->first();
        if($userinfo){
            $this->userinfo = $userinfo;
        }else{
            echo api_json([],10000,'登录过期');
            exit;
        }
    }

    public function api_json( $data = array() , $code = "", $msg = '')
    {
        $code = empty($code)?'Success':$code;

        $backData['return_code'] = $code;
        $backData['return_msg'] = $msg;
        $backData['return_info'] = $data;

        return response()->json($backData);
    }
    /**
     *curl
     */
    public function curlMet($url,$method,$post_data = 0)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        } elseif ($method == 'get') {
            curl_setopt($ch, CURLOPT_HEADER, 0);
        }
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

}