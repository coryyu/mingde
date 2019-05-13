<?php
namespace App\Http\Controllers\V4;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Routing\Controller;
use App\Http\Controllers\Controller as localController;


class CommonController extends localController
{

    public $userinfo;



    function __construct(Request $request) {

        //token 获取用户信息

        $token = $request->input('token');

        $userinfo = DB::table('app_user')
            ->where('token',$token)
            ->where('is_del',0)
            ->first();
        if($userinfo){
            $userinfo->official= $userinfo->official_token;
            $userinfo->official_token= config('app.app_configs.officialtoken').'?token='.$userinfo->official_token;
            $this->userinfo = $userinfo;
        }else{
            echo api_json([],10000,'登录过期');exit;
        }
    }
    /**
     *坐标获取详情
     * by chenyu
     * 2019-1-10
     * lat 经度
     * lng 维度
     **/
    public function apiMapGeocoder($lat,$lng)
    {
        $ak = config('app.app_configs.mapAk');
        $url = config('app.app_configs.mapUrl');
        $location = $lat.','.$lng;
        $path = $url.'ak="'.$ak.'"&callback=renderReverse&location="'.$location.'"&output=json';

        $map = file_get_contents($path);
        print_r($map);exit;

    }
    /**
     *判断家庭成员是否存在
     * 2019-1-23
     * by chenyu
     */
    public function isHome($homeid)
    {
        //判断家庭成员是否存在
        $homes = DB::table('app_home')
            ->where('id',$homeid)
            ->where('user_id',$this->userinfo->id)
            ->where('is_del',0)
            ->first();

        if(!$homes){
            return false;

        }else{
            return $homes;
        }
    }

    /**
     * post 提交
     * 2019-1-24
     * by chenyu
     */
    public function curlMet($url,$method,$post_data = 0)
    {
        $ch = curl_init();

        if(stripos($url,"https://")!==FALSE){
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }else{
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        }

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