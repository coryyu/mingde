<?php
namespace App\Http\Controllers\V4;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\V4\CommonController as Common;
use JPush\Client as JPush;

class LailaiController extends Common
{

    /**
     * 赖赖扣 记录
     * 2019-1-22
     * by chenyu
     */
    public function addLailaiList(Request $request)
    {
        $home_id = $request->input('home_id');
        $system = $request->input('system');
        $app_version = $request->input('app_version');
        $xx_version = $request->input('xx_version');
        $position = $request->input('position');
        $tx_type = $request->input('tx_type');
        $cate = $request->input('cate');

        if (empty($home_id)){
            return api_json([],10001,'参数错误');
        }
        $homes = $this->isHome($home_id);
        if(!$homes){
            return api_json([],90126,'家庭成员不存在');
        }
        $data['home_id'] = $home_id;
        $data['phone'] = $this->userinfo->phone;
        $data['name'] = $homes->name;
        $data['birth_time'] = $homes->age;
        $data['sex'] = $homes->sex;
        $data['connect_time'] = today_time();
        $data['tx_time'] = today_time();
        $data['tx_type'] = $tx_type;
        $data['system'] = $system;
        $data['xx_version'] = $xx_version;
        $data['app_version'] = $app_version;
        $data['position'] = $position;
        $data['flag'] = $cate;

        $res = DB::table('app_kou_xxrecord')
            ->insertGetId($data);
        if ($res>0){
            return api_json([],200,'成功');
        }else{
            return api_json([],90148,'记录添加失败');
        }

    }

    /**
     *嘘嘘记录
     * 2019-2-27
     * by chenyu
     */
    public function getLailaiList(Request $request)
    {
        $homes = DB::table('app_home')
            ->select('id')
            ->where('user_id',$this->userinfo->id)
            ->where('is_del',0)
            ->get()
            ->toArray();
        foreach($homes as $k=>$v){
            $ids[]=$v->id;
        }


        $kou = DB::table('app_kou_xxrecord')
            ->select('tx_time','flag','name')
            ->whereIn('home_id',$ids)
            ->orderBy('id','desc')
            ->get();
//            ->toArray();

        if($kou->isEmpty()){
            return api_json([],10002,'无数据');
        }else{
            return api_json(['info'=>$kou->toArray()],200,'无数据');
        }
    }

  public function test()
    {
        try{
            $message =  'chenyu test';
            $client = new JPush("39452cffb57a4261ba2e70f3", "446946b83a64e083b646e3f7");



            $push = $client->push();
            $platform = array('ios', 'android');
            $alert = $message;

            $tag = array('15040198002');
//            $ios_notification = array(
//                'sound' => 'hello',
//                'badge' => 2,
//                'content-available' => true,
//                'category' => 'jiguang',
//                'extras' => array(
//                    'key' => 'value',
//                    'jiguang'
//                ),
//            );

            $ios_notification['badge'] = 2;
            $android_notification = array(
                'title' => 'hello',
                'extras' => array(
                    'key' => 'value',
                    'jiguang'
                ),
            );
            $content = $message;
            $message = array(
                'title' => 'hello',
                'content_type' => $message,
                'extras' => array(
                    'id' => 1
                ),
            );
            $options = array(
                'apns_production' => true,          //true为生成环境   false为开发
            );
            $response = $push
                ->setPlatform($platform)
                ->addTag($tag)
                //->addRegistrationId($regId)
                ->iosNotification($alert, $ios_notification)
//                ->androidNotification($alert, $android_notification)
//                ->message($content, $message)
//                ->options($options)->send();
                ->options($options)->send();
            var_dump($response);exit;
            return true;


            $res = $client->push()
                ->platform('all')
                ->addAllAudience()
                ->setNotificationAlert('Hello, JPush')
                ->send();
            print_r($res);
            exit;

        }

        catch (Exception $e)

        {
            print_r($e);
            return true;
        }
    }


}