<?php
namespace App\Http\Controllers\V4;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{

    /**
     *登录
     * by chenyu
     * 2018-12-29
     */
    public function login(Request $request)
    {


        $phone = $request->input("phone");
        $password = $request->input("password");
        //验证手机号
        if(empty($phone) || !rules_phone($phone) ){
            return api_json([],'90107','手机号格式错误');
        }
        //验证密码格式
        if(empty($password) || !preg_match('/^[\d\w_]{6,20}$/', $password)){
            return api_json([],'90110','密码格式错误');
        }

        //验证 账号 密码
        $useinfo = DB::table('app_user')
            ->select('id','name','home_sum','member_type','avatar_url','phone','active','official_token')
            ->where('phone', $phone)
            ->where('password',md5(md5('yxx'.$password)))
            ->where('is_del',0)
            ->first();
        if(empty($useinfo)){
            return api_json([],'90111','账号或密码错误');
        }else{
            //登录成功 重写token
            $token = md5($phone . time() . "yxx");
            $is = DB::table('app_user')
                ->where('id',$useinfo->id)
                ->update(['token'=>$token,'last_at'=>date('Y-m-d H:i:s',time())]);
            if($is>0){
                $useinfo->token = $token;
                //账号下 家庭成员
                if($useinfo->home_sum>0){

                    $homes = DB::table('app_home')
                        ->select('id','name','sex','age','is_use')
                        ->where(['user_id' =>$useinfo->id,'is_del'=>0])
                        ->orderBy('create_at','DESC')
                        ->get();
                    foreach($homes->toArray() as $k=>$v){
                        $ids[] = $v->id;
                    }
                    $testList = $this->getTestsrecordCount($ids);
                    if($testList){
                        $useinfo->testCount = count($testList);
                    }else{//不存在 检测记录
                        $useinfo->testCount = 0;
                    }

                    $useinfo->homes = $homes;

                }else{ //无家庭成员
                    $useinfo->homes = '';
                }
                $useinfo->avatar_url = config('app.app_configs.loadhost').$useinfo->avatar_url;
                return api_json($useinfo,200,'登录成功');
            }else{//token 写入失败
                return api_json([],'90124','token写入失败');
            }

        }
    }
    /**
     * 验证码登录
     * by chenyu
     * 2019-1-11
     **/
    public function loginPhone(Request $request)
    {
        $phone = $request->input("phone");
        $code = $request->input("code");

        $zone = 86;
        if(empty($code) || !is_numeric($code)){
            return api_json([],'90108','验证码格式不正确');
        }
        if(empty($phone) || !rules_phone($phone) ){
            return api_json([],'90107','手机号格式错误');
        }

        //验证码 校验
        $url = config('app.app_configs.sms_url');
        $sms_appkey = config('app.app_configs.sms_appkey');

        $post_date=[
            'appkey'=>$sms_appkey,
            'phone'=>$phone,
            'zone'=>$zone,
            'code'=>$code,
        ];

//        var_dump($post_date);exit;
        $result = $this->uCurl($url,'post',$post_date);//网络请求
        if($result['status'] !==200){//判断验证码
//        if(false){//判断验证码
            return api_json([],'90109','验证码错误');

        }else{//验证码成功
            $useinfo = DB::table('app_user')
                ->select('id','name','home_sum','member_type','avatar_url','phone','active','official_token')
                ->where(['phone' => $phone,'is_del'=>0])
                ->first();
            if(empty($useinfo)){
                return api_json([],'90112','账号不存在');
            }else{

                $token = md5($phone . time() . "yxx");
                $is = DB::table('app_user')
                    ->where('id',$useinfo->id)
                    ->update(['token'=>$token,'last_at'=>date('Y-m-d H:i:s',time())]);
                if($is>0) {
                    $useinfo->token = $token;

                    if($useinfo->home_sum>0 ){
                        $homes = DB::table('app_home')
                            ->select('id','name','sex','age','is_use')
                            ->where(['user_id' =>$useinfo->id,'is_del'=>0])
                            ->orderBy('create_at','DESC')
                            ->get();

                        foreach($homes->toArray() as $k=>$v){
                            $ids[] = $v->id;
                        }
                        $testList = $this->getTestsrecordCount($ids);
                        if($testList){
                            $useinfo->testCount = count($testList);
                        }else{//不存在 检测记录
                            $useinfo->testCount = 0;
                        }

                        $useinfo->homes = $homes;
                    }else{//无家庭成员
                        $useinfo->homes = '';
                    }
                    $useinfo->avatar_url  = config('app.app_configs.loadhost').$useinfo->avatar_url;
                    return api_json($useinfo,200,'登录成功');

                }else{
                    return api_json([],'90124','token写入失败');
                }
            }
        }
    }
    /**
     *效验验证码
     * by chenyu
     * 2019-1-10
     */
    public function verifyCode(Request $request)
    {
        $phone = $request->input("phone");
        $code = $request->input("code");
        $zone = 86;

        if(empty($phone) || !rules_phone($phone) ){
            return api_json([],'90107','手机号格式错误');
        }
        if(empty($code) || !is_numeric($code)){
            return api_json([],'90108','验证码格式不正确');
        }
        $url = config('app.app_configs.sms_url');
        $sms_appkey = config('app.app_configs.sms_appkey');

        $post_date=[
            'appkey'=>$sms_appkey,
            'phone'=>$phone,
            'zone'=>$zone,
            'code'=>$code,
        ];

        $result = $this->uCurl($url,'post',$post_date);//网络请求

        if($result['status'] !==200){//判断验证码
            return api_json([],'90109','验证码错误');
        }else{
            return api_json([],'200','验证码正确');
        }
    }

    /**
     *注册
     * by chenyu
     * 2019-1-10
     */
    public function register(Request $request)
    {
        $phone = $request->input("phone");
        $password = $request->input("password");
        $code = $request->input("code");
        $app = $request->input("app");
        $latitude  = $request->input("latitude");//纬度
        $longitude = $request->input("longitude");//经度


        $zone = 86;
        if(empty($code) || !is_numeric($code)){
            return api_json([],'90108','验证码格式不正确');
        }
        if(empty($phone) || !rules_phone($phone) ){
            return api_json([],'90107','手机号格式错误');
        }
        if(empty($password) || !preg_match('/^[\d\w_]{6,20}$/', $password)){
            return api_json([],'90110','密码格式错误');
        }

        //验证 验证码
        $url = config('app.app_configs.sms_url');
        $sms_appkey = config('app.app_configs.sms_appkey');

        $post_date=[
            'appkey'=>$sms_appkey,
            'phone'=>$phone,
            'zone'=>$zone,
            'code'=>$code,
        ];

        $result = $this->uCurl($url,'post',$post_date);//网络请求

        if($result['status'] !==200){//判断验证码
            return api_json([],'90109','验证码错误');
        }else{

            //验证码 正确
            $us = DB::table('app_user')
                ->select('id')
                ->where(['phone' => $phone,'is_del'=>0])
                ->first();
            if(!empty($us->id)){//用户已注册
                return api_json([],90123,'用户已注册');
            }else{



                // TEST


                //新用户
                $userinfo['phone']=$phone;
                $userinfo['password']=md5(md5('yxx'.$password));
                $userinfo['is_del']=0;
                $userinfo['avatar_url']='image/avater_bg.png';//默认头像
                $userinfo['create_at']=today_time();
                $userinfo['update_at']=$userinfo['create_at'];
                $userinfo['last_at']=$userinfo['create_at'];
                $userinfo['home_sum']=0;
                $token = md5($userinfo['phone'] . time() . "yxx");
                $userinfo['token']=$token;
                $userinfo['name']='游客'.substr($phone,-4);
                $userinfo['active']=0;
                $userinfo['official_token']=md5('pzzk'.md5($phone));
                $userinfo['app']= empty($app)?'':$app;

                /*
                //读取坐标信息
                if(!empty($latitude) && !empty($longitude)){
                    $pos = $this->apiMapGeocoder($latitude,$longitude);
                    $userinfo['position'] = $latitude.','.$longitude;
                    $userinfo['province'] = $pos['province'];
                    $userinfo['city'] = $pos['city'];
                    $userinfo['district'] = $pos['district'];
                    $userinfo['addr'] = $pos['addr'];

                }
                */

                //查看是否办过会员
                $us = DB::table('app_member')
                    ->select('mem_type')
                    ->where('telephone',$phone)
                    ->where('end_time','>',date('Y-m-d H:i:s',time()))
                    ->first();

                if(!empty($us->mem_type)){//有会员
                    $userinfo['member_type'] = $us->mem_type;
                }else{
                    $userinfo['member_type'] = 0;
                }
               //执行添加
                $uid = DB::table('app_user')
                    ->insertGetId($userinfo);
                if($uid>0){
                    $result['id'] =$uid;
                    $result['name'] =$userinfo['name'];
                    $result['phone'] =$userinfo['phone'];
                    $result['home_sum'] =0;
                    $result['member_type'] =$userinfo['member_type'];
                    $result['avatar_url'] =config('app.app_configs.loadhost').$userinfo['avatar_url'];
                    $result['token'] =$token;
                    $result['active'] =0;
                    return api_json($result,200,'注册成功');
                }else{
                    return api_json([],90125,'注册失败');
                }
            }
        }
    }
    /**
     *修改密码
     **/
    public function editPassword(Request $request)
    {

        $phone = $request->input("phone");
        $code = $request->input("code");
        $password = $request->input("password");

        $zone = 86;
        if(empty($code) || !is_numeric($code)){
            return api_json([],'90108','验证码格式不正确');
        }
        if(empty($phone) || !rules_phone($phone) ){
            return api_json([],'90107','手机号格式错误');
        }


        //验证 验证码
        $url = config('app.app_configs.sms_url');
        $sms_appkey = config('app.app_configs.sms_appkey');
        $post_date=[
            'appkey'=>$sms_appkey,
            'phone'=>$phone,
            'zone'=>$zone,
            'code'=>$code,
        ];
        $result = $this->uCurl($url,'post',$post_date);//网络请求

        if($result['status'] !==200){//判断验证码
            return api_json([],'90109','验证码错误');
        }else{

            $user=  DB::table('app_user')
                ->select('id')
                ->where('phone',$phone)
                ->where('is_del',0)
                ->first();
            if(empty($user->id)){//用户不存在
                return api_json([],90126,'用户不存在，请注册');
            }else{
                if(empty($password) || !preg_match('/^[\d\w_]{6,20}$/', $password)){
                    return api_json([],90110,'密码格式错误');
                }
                $password = md5(md5('yxx'.$password));
                $updateres = DB::table('app_user')
                    ->where('id',$user->id)
                    ->update(['token'=>'','password'=>$password,'update_at'=>date('Y-m-d H:i:s',time())]);
                if($updateres>0){//修改成功
                    return api_json([],200,'成功，请利用新密码登录');
                }else{
                    return api_json([],90127,'密码重置失败');
                }
            }

        }

    }
    /**
     *微信登录
     * 2019-2-11
     * by chenyu
     **/
    public function wxLogin(Request $request)
    {
        $unionid = $request->input('unionid');
        $app = $request->input('app');
            $dataset = $request->input('dataset');
        if(empty($unionid)){
            return api_json([],90154,'unionid参数错误');
        }
        if(empty($app)){
            return api_json([],90155,'app参数错误');
        }
        if(empty($dataset) || !$this->is_json($dataset)){
            return api_json([],90156,'dataset参数错误');
        }
        //微信用户是否存在
        $app_user = DB::table('app_user')
            ->where('unionid',$unionid)
            ->where('is_del',0)
            ->first();
        if($app_user){//已存在 直接登录
            $token = md5($app_user->phone . time() . "yxx");
            $is = DB::table('app_user')
                ->where('id',$app_user->id)
                ->update(['token'=>$token,'last_at'=>date('Y-m-d H:i:s',time())]);
            if($is>0) {
                $app_users = $this->returnUserHome($app_user->id);
                return api_json($app_users,200,'登录成功');
            }else{
                return api_json([],90157,'token写入失败');
            }
        }else{// 用户不存在，提醒注册
            //表  记录unionid 访问记录
            $wxlog['unionid'] = $unionid;
            $wxlog['json'] = $dataset;
            $wxlog['create_time'] = date('Y-m-d H:i:s',time());
            $wxlog['app'] = $app;
            $wxlog =DB::table('app_user_wxlog')
                ->insert($wxlog);
            $data['unionid'] = $unionid;

            if($wxlog){
                return api_json([],300,'用户不存在，注册登录');
            }else{
                return api_json([],90158,'登录失败');
            }

        }

    }

    /**
     *微信登录 绑定手机号
     * chenyu
     * 2019-2-11
     */
    public function bindingPhone(Request $request)
    {
        $phone = $request->input('phone');
        $unionid = $request->input('unionid');
        $code = $request->input("code");
        $app = $request->input("app");

        $zone = 86;
        if (empty($code) || !is_numeric($code)) {
            return api_json([], '90108', '验证码格式不正确');
        }


        if (empty($unionid)) {
            return api_json([], 10001, '参数错误');
        } else {
            $wxlog = DB::table('app_user_wxlog')
                ->where('unionid', $unionid)
                ->first();
            if (!$wxlog) {
                return api_json([], 10001, '参数错误');
            }
        }
        if (!is_numeric($phone) || strlen($phone) != 11 || !rules_phone($phone)) {
            return api_json([], 10001, '参数错误');
        }

        //验证码 校验
        $url = config('app.app_configs.sms_url');
        $sms_appkey = config('app.app_configs.sms_appkey');

        $post_date = [
            'appkey' => $sms_appkey,
            'phone' => $phone,
            'zone' => $zone,
            'code' => $code,
        ];

        $result = $this->uCurl($url,'post',$post_date);//网络请求

        if($result['status'] !==200){//判断验证码
            return api_json([],'90109','验证码错误');
        }

        $u_info = DB::table('app_user')
            ->where('phone',$phone)
            ->where('is_del',0)
            ->first();
        if($u_info) {//用户存在
            $token = md5($u_info->phone . time() . "yxx");
            $user_update['unionid'] = $unionid;
            $user_update['update_at'] = today_time();
            $user_update['token'] = $token;
            $user_update['app'] = $app;
            DB::beginTransaction();
            try {
                DB::table('app_user')
                    ->where('id',$u_info->id)
                    ->update($user_update);
                    //微信小程序 同步
                $xcx = DB::table('xcx_user')
                    ->where('unionid',$unionid)
                    ->first();
                if($xcx){
                    //开启事务

                    DB::table('xcx_user')
                        ->where('id',$xcx->id)
                        ->update(['uid'=>$u_info->id,'update_time'=>today_time()]);

                    DB::table('app_home')
                        ->where('wx_u',$xcx->id)
                        ->update(['update_at'=>today_time(),'user_id'=>$u_info->id]);

                    DB::table('app_user')
                        ->where('id',$u_info->id)
                        ->increment('home_sum');

                }
                //无小程序数据 更新
                DB::commit();

            }catch(\Exception $exception) {
                //事务回滚
                DB::rollBack();
                return api_json([],'90126','失败');
//            return back()->withErrors($exception->getMessage())->withInput();
            }

            $app_user = $this->returnUserHome($u_info->id);
            return api_json($app_user,200,'登录成功');
        }else{
            //手机号用户不存在 需要注册
            return api_json([],300,'手机号没有被注册，需要填写密码注册');
        }
    }

    /**
     * 新增手机号 绑定微信
     * 2019-2-11
     * by chenyu
     **/
    public function bindingNewPhone(Request $request)
    {
        $phone =$request->input("phone");
        $password = $request->input("password");
        $app =$request->input("app");
        $unionid = $request->input("unionid");
        if (!is_numeric($phone) || strlen($phone) != 11 || !rules_phone($phone)) {
            return api_json([], 10001, '参数错误');
        }

        if(empty($password) || !preg_match('/^[\d\w_]{6,20}$/', $password)){
            return api_json([],90110,'密码格式错误');
        }
        if(empty($unionid)){
            return api_json([],90154,'unionid不存在');
        }else{
            $wxlog = DB::table('app_user_wxlog')
                ->where('unionid',$unionid)
                ->first();
            if(empty($wxlog)){
                return api_json([],90154,'unionid不存在');
            }
        }

        $userinfos = DB::table('app_user')
            ->where('phone',$phone)
            ->where('is_del',0)
            ->first();
        if ($userinfos) {
            return api_json([],90123,'用户已注册');
        } else {
            //新用户 注册

            $token = md5($phone. time() . "yxx");

            $userinfo['token']=$token;
            $userinfo['phone']=$phone;
            $userinfo['password']=md5(md5('yxx'.$password));
            $userinfo['is_del']=0;
            $userinfo['avatar_url']='image/avater_bg.png';
            $userinfo['create_at']=today_time();
            $userinfo['update_at']=$userinfo['create_at'];
            $userinfo['last_at']=$userinfo['create_at'];
            $userinfo['home_sum']=0;
            $token = md5($userinfo['phone'] . time() . "yxx");
            $userinfo['token']=$token;
            $userinfo['name']='游客'.substr($phone,-4);
            $userinfo['active']=0;
            $userinfo['official_token']=md5('pzzk'.md5($phone));
            $userinfo['app']= empty($app)?'':$app;
            $userinfo['unionid']= $unionid;

            /*
            //读取坐标信息
            if(!empty($latitude) && !empty($longitude)){
                $pos = $this->apiMapGeocoder($latitude,$longitude);
                $userinfo['position'] = $latitude.','.$longitude;
                $userinfo['province'] = $pos['province'];
                $userinfo['city'] = $pos['city'];
                $userinfo['district'] = $pos['district'];
                $userinfo['addr'] = $pos['addr'];

            }
            */

            //查看是否办过会员
            $us = DB::table('app_member')
                ->select('mem_type')
                ->where('telephone',$phone)
                ->where('end_time','>',date('Y-m-d H:i:s',time()))
                ->first();

            if(!empty($us->mem_type)){//有会员
                $userinfo['member_type'] = $us->mem_type;
            }else{
                $userinfo['member_type'] = 0;
            }

            DB::beginTransaction();
            try {

                //执行添加
                $uid = DB::table('app_user')
                    ->insertGetId($userinfo);


                $xcx = DB::table('xcx_user')
                    ->where('unionid',$unionid)
                    ->first();
                if($xcx){
                    //开启事务

                    DB::table('xcx_user')
                        ->where('id',$xcx->id)
                        ->update(['uid'=>$uid,'update_time'=>today_time()]);

                    DB::table('app_home')
                        ->where('wx_u',$xcx->id)
                        ->update(['update_at'=>today_time(),'user_id'=>$uid]);

                    DB::table('app_user')
                        ->where('id',$uid)
                        ->increment('home_sum');

                }
                DB::commit();

            }catch(\Exception $exception) {
                //事务回滚
                DB::rollBack();
                return api_json([],'90126','失败');
//            return back()->withErrors($exception->getMessage())->withInput();
            }
            if($uid>0){
                //注册成功
                $app_user = $this->returnUserHome($uid);
                return api_json($app_user,200,'登录成功');
            }else{
                //注册失败
                return api_json([],'90125','注册失败');
            }

        }
    }

    /**
     *登录成功返回用户信息
     * 2019-2-12
     * by chenyu
     */
    public function returnUserHome($uid)
    {
        $app_user = DB::table('app_user')
            ->select('id','token','name','home_sum','member_type','avatar_url','phone','active','official_token')
            ->where('id',$uid)
            ->where('is_del',0)
            ->first();
        if($app_user->home_sum>0 ){
            $homes = DB::table('app_home')
                ->select('id','name','sex','age','is_use')
                ->where(['user_id' =>$app_user->id,'is_del'=>0])
                ->orderBy('create_at','DESC')
                ->get();
            if(!$homes->isEmpty()) {
                foreach ($homes->toArray() as $k => $v) {
                    $ids[] = $v->id;
                }
                $testList = $this->getTestsrecordCount($ids);
                if ($testList) {
                    $app_user->testCount = count($testList);
                } else {//不存在 检测记录
                    $app_user->testCount = 0;
                }
                $app_user->homes = $homes;
            }else{
                $app_user->homes = '';
            }
        }else{//无家庭成员
            $app_user->homes = '';
        }
        $app_user->avatar_url = config('app.app_configs.loadhost').$app_user->avatar_url;
        return $app_user;
    }
    public function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
    /**
     *网络请求
     *
     **/
    function uCurl($url,$method,$post_data = 0)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);

            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $post_data ));
        } elseif ($method == 'get') {
            curl_setopt($ch, CURLOPT_HEADER, 0);
        }
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
            'Accept: application/json',
        ) );
        $output = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($output,true);

        return $res;
    }
}