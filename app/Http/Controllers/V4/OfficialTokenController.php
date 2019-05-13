<?php
namespace App\Http\Controllers\V4;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Member;


class OfficialTokenController extends Controller
{

    public $key = "b052a62a6b44711a0718490a40431905";
    public $appid = "wx73e38c7c41d20b2a";
    public $appsecret = "f006992a30cb1ba51af34853ee15a07d";


    public function Index(Request $request)
    {
        $usr = $request->input('token');
        $code = $request->input('code');
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {//微信浏览器
                if(!empty($code)) {//微信公众账号
                    $wx_openid_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->appid.'&secret='.$this->appsecret
                        .'&code='.$code.'&grant_type=authorization_code';
                    $result = file_get_contents($wx_openid_url);
                    $result = json_decode($result,true);

                    if(!empty($result['openid'])){//获取 openid 成功

                        $data['openid'] = $result['openid'];
                        $data['usr'] = $usr;
                        $time = time();
                        $timeData[] ='今天';
                        for($i=1;$i<=60;$i++){
                            $time = $time+86400;
                            $timeData[] = date('Y-m-d',$time);
                        }

                        foreach($timeData as $k=>$v){
                            $timeDatas[]=['id'=>$k,'value'=>$v];
                        }

                        $data['times'] = json_encode($timeDatas);
                        $model =
                            [
                                ['id' =>1 , 'value'=>'S码（推荐4-8公斤宝宝使用）'],
                                ['id' =>2 , 'value'=>'M码（推荐6-11公斤宝宝使用）'],
                                ['id' =>3 , 'value'=>'L码（推荐9-14公斤宝宝使用）']

                            ];
                        $data['model'] = json_encode($model);
                        return view('member.notify',['data'=>$data]);

                    }else{
                        return view('member.index',['data'=>['id'=>$usr]]);
                    }
                }else{
                    return view('member.index',['data'=>['id'=>$usr]]);
                }
            }else{//非微信浏览器
                return '请用微信扫描';
            }
    }

}