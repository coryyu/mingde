<?php
namespace App\Http\Controllers\Mingde;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Mingde\CommonController as Common;
use App\AppSignIn;
use App\AppMessageIs;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     *获取用户信息
     */
   public function getUserInfo()
   {

   }
    /**
     *获取openid
     * 2018-9-19
     * by chenyu
     */
    public function getOpenid(Request $request)
    {
        $code = $request->input('code');
        $appid = $request->input('appid');
        $classchannel = DB::table('sch_classchannel')
            ->where('number',$appid)
            ->where('is_del',0)
            ->first();
        if(empty($code)){
            return $this->api_json([],500,'code不存在');
        }
        if(!$classchannel){
            return $this->api_json([],500,'appid不存在');
        }
        $URL = "https://api.weixin.qq.com/sns/jscode2session?appid=".$classchannel->appid."&secret=".$classchannel->appsecret."&js_code=".$code."&grant_type=authorization_code";
        $result = file_get_contents($URL);
        $result = json_decode($result,true);
        if(!empty($result['openid'])) {
            $user_info = DB::table('sch_user')
                ->where('openid', $result['openid'])
                ->first();
            if (!empty($user_info)) {//存在
                $token = md5($result['openid'] . time() . "yxx");
                DB::table('sch_user')->where('id', $user_info->id)->update(['token' => $token]);
                return $this->api_json(['token' => $token], 200, '成功');
            } else {//需同步 已有用户
                $token = md5($result['openid'] . time() . "yxx");
                $xcx_data['openid'] = $result['openid'];
                $xcx_data['created_at'] = date('Y-m-d H:i:s', time());
                $xcx_data['updated_at'] = $xcx_data['created_at'];
                $xcx_data['session_key'] = $result['session_key'];
                $xcx_data['is_del'] = 0;
                $bool = DB::table('sch_user')
                    ->insert($xcx_data);
                if ($bool) {
                    return $this->api_json(['token' => $token], 200, '成功');
                } else {
                    return $this->api_json(['token' => $token], 500, '失败');
                }
            }
        }else{
            return $this->api_json([], 500, $result);
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

}