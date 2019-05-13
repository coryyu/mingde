<?php
namespace App\Http\Controllers\V4;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Controllers\V4\CommonController as Common;
use App\AppSignIn;
use App\AppMessageIs;
use Illuminate\Support\Facades\Storage;

class UserController extends Common
{
    /**
     *获取用户信息
     * 2019-1-17
     * by chenyu
     */
   public function getUserInfo()
   {

       $data['id'] = $this->userinfo->id;
       $data['avatar_url'] = config('app.app_configs.loadhost').$this->userinfo->avatar_url;
       $data['name'] = $this->userinfo->name;
       $data['member_type'] = $this->userinfo->member_type;
       $data['active'] = $this->userinfo->active;
       $data['official_token'] = $this->userinfo->official_token;

        //检测记录
       $homes = DB::table('app_home')
           ->where('user_id',$this->userinfo->id)
           ->get();
       if(!$homes->isEmpty()){
           foreach($homes->toArray() as $k=>$v){
               $ids[] = $v->id;
           }
           $testList = $this->getTestsrecordCount($ids);
           if($testList){
               $data['testCount']  = count($testList);
           }else{//不存在 检测记录
               $data['testCount'] = 0;
           }
       }else{
           $data['testCount'] = 0;
       }
       $data['memberlogo']='';
        if($data['member_type']>0) {//会员角色

            $card = DB::table('app_member_card')
                ->select('icon')
                ->where('id',$data['member_type'])
                ->first();

            $data['memberlogo'] =  config('app.app_configs.loadhost').$card->icon;
        }
       //关注医生
       $to_doc = DB::table('app_to_doc')
           ->where('uid',$this->userinfo->id)
           ->where('status',1)
           ->get()
           ->toArray();

       $data['to_doc'] = count($to_doc);
       //是否签到
       $app_signin=DB::table('app_signin')
           ->where('uid',$this->userinfo->id)
           ->where('date',date('Y-m-d',time()))
           ->first();
       if($app_signin){
           $data['signin'] = 1;
       }else{
           $data['signin'] = 0;
       }

       return api_json($data,200,'获取成功');
   }
    /**
     *修改用户名
     * 2019-1-17
     * by chenyu
     */
    public function updateUserName(Request $request)
    {
        $name = $request->input("name");

        $up = DB::table('app_user')
            ->where('id',$this->userinfo->id)
            ->update(['name'=>$name,'update_at'=>date('Y-m-d H:i:s',time())]);
        if($up){
            return api_json([],200,'修改成功');
        }else{
            return api_json([],90128,'姓名修改失败');
        }
    }
    /**
     *退出登录
     * 2019-1-17
     * by chenyu
     */
    public function outPut()
    {

        $up = DB::table('app_user')
            ->where('id',$this->userinfo->id)
            ->update(['token'=>'','update_at'=>today_time()]);

        if($up>0){
            return api_json([],200,'退出成功');
        }else{
            return api_json([],90129,'退出失败，请重试');
        }

    }

    /**
     *添加收货地址
     * 2019-1-18
     * by chenyu
     */
    public function addUserAdd(Request $request)
    {

        $name = $request->input("name");
        $phone = $request->input("phone");
        $province = $request->input("province");
        $city = $request->input("city");
        $area = $request->input("area");
        $text = $request->input("text");
        $is_default = $request->input("is_default");

        if(empty($name)){
            return api_json([],90131,'参数name错误');
        }
        if(empty($phone)){
            return api_json([],90131,'参数phone错误');
        }
        if(empty($province)){
            return api_json([],90131,'参数province错误');
        }
        if(empty($city)){
            return api_json([],90131,'参数city错误');
        }
        if(empty($area)){
            return api_json([],90131,'参数area错误');
        }
        if(empty($text)){
            return api_json([],90131,'参数text错误');
        }
        if(empty($is_default)){
            return api_json([],90131,'参数is_default错误');
        }

        DB::beginTransaction();//开启事务
        try {
            $data['uid']= $this->userinfo->id;
            $data['province']= $province;
            $data['city']= $city;
            $data['area']= $area;
            $data['text']= $text;
            $data['name']= $name;
            $data['add_tel']= $phone;
            $data['create_at']= today_time();
            $data['update_at']= $data['create_at'];

            $add_id = DB::table('app_user_add')
                ->insertGetId($data);
            if($is_default == 1){
                //todo
                DB::table('app_user_add')
                    ->where('uid',$this->userinfo->id)
                    ->update(['']);

                DB::table('app_user_add')
                    ->where('id',$add_id)
                    ->update(['']);

            }else{

            }
        }catch(\Exception $exception) {
            //事务回滚
            DB::rollBack();
            return api_json([],'90126','添加失败');
            //return back()->withErrors($exception->getMessage())->withInput();
        }
    }
    /**
     *签到
     */
    public function signIn()
    {
        $signin['uid'] = $this->userinfo->id;
        $signin['date'] = date('Y-m-d',time());
        $signin['create_at'] = today_time();
        DB::beginTransaction();//开启事务
        try {
            $res_signin = DB::table('app_signin')
                ->where('uid',$this->userinfo->id)
                ->where('date',date('Y-m-d',time()))
                ->first();
            if($res_signin){//已签到
                return api_json([],90141,'已签到');
            }else{
                //添加签到记录
                $signin_id = DB::table('app_signin')
                    ->insertGetId($signin);

                //添加积分记录
                $integral_list['type'] = 1;
                $integral_list['plus'] = 1;
                $integral_list['sum'] = 2;
                $integral_list['uid'] = $this->userinfo->id;
                $integral_list['create_at'] = today_time();
                $integral_list['typeid'] = $signin_id;

                $integral_list_id = DB::table('app_integral_list')
                    ->insertGetId($integral_list);

                //更新用户总积分
                DB::table('app_user')
                    ->where('id',$this->userinfo->id)
                    ->increment('active',2);
                DB::commit();
                return api_json(['active'=>$this->userinfo->active+2],200,'签到成功');
            }
        }catch(\Exception $exception) {
            //事务回滚
            DB::rollBack();
            return api_json([],90140,'签到失败，请重试');
//            return back()->withErrors($exception->getMessage())->withInput();
        }
    }
    /**
     *意见反馈
     * 2019-1-23
     * by chenyu
     */
    public function feedBack(Request $request)
    {
        $imgs =$request->input('imgs');
        $text =$request->input('text');
        $phone =$request->input('phone');


        $data['text'] = $text;
        $data['phone'] = $phone;
        $data['imgs'] = $imgs;
        $data['create_at'] = today_time();
        $data['update_at'] = $data['create_at'];
        $data['is_del'] = 0;
        $data['uid'] = $this->userinfo->id;

        $bool = DB::table('app_feedback')
            ->insert($data);
        if($bool){
            return api_json([],200,'反馈成功');
        }else{
            return api_json([],200,'反馈失败');
        }
    }

    /**
     *更新头像
     * 2019-1-23
     * by chenyu
     */
    public function userAvatarUpload(Request $request)
    {
        $file =$request->file('file');
        //文件是否上传成功
        if($file->isValid()) {
            $ext = $file->getClientOriginalExtension();
            $realPath = $file->getRealPath();

            $filename = date('Ymd') . '/' . date('Y-m-d-H-i-s') . '-' . uniqid() . '.' . $ext;
            $bool = Storage::disk('public')->put($filename,file_get_contents($realPath));
            if($bool) {//上传成功
                $avar_bool = DB::table('app_user')
                    ->where('id',$this->userinfo->id)
                    ->update(['avatar_url'=>$filename,'update_at'=>today_time()]);
                if($avar_bool){
                    return api_json(['filename'=>config('app.app_configs.loadhost').$filename],200,'上传成功');
                }else{
                    return api_json([],90150,'头像上传失败');
                }
            }else{
                return api_json([],90150,'头像上传失败');
            }
        }else{
            return api_json([],90150,'头像上传失败');
        }
    }


    /**
     *图片上传
     * 2019-1-23
     * by chenyu
     */
    public function userUpload(Request $request)
    {
        $file =$request->file('file');
        //文件是否上传成功
        if($file->isValid()) {
            $ext = $file->getClientOriginalExtension();
            $realPath = $file->getRealPath();

            $filename = date('Ymd') . '/' . date('Y-m-d-H-i-s') . '-' . uniqid() . '.' . $ext;
            $bool = Storage::disk('public')->put($filename,file_get_contents($realPath));
            if($bool) {//上传成功
                return api_json(['filename'=>config('app.app_configs.loadhost').$filename],200,'上传成功');
            }else{
                return api_json([],90143,'文件上传失败');
            }
        }else{
            return api_json([],90143,'文件上传失败');
        }
    }

    /**
     *获取消息列表
     * 2019-2-10
     * by chenyu
     */
    public function getMessageList(Request $request)
    {

        $page = (int) $request->input("page");//页码
        $limit = (int) $request->input("limit");//页码
        $limit = empty($limit)?15:$limit;


        //关联临时表
        DB::connection()->enableQueryLog();#开启执行日志
        $message_is_sql = 'select id,messageid,status from app_message_is where userid='.$this->userinfo->id;

        $message = DB::table('app_message')
            ->select('app_message.id','app_message.channel','app_message.text','app_message.type','app_message.remarks','app_message.create_at','m.status')
            ->leftJoin(DB::raw('('.$message_is_sql.') m '),'app_message.id','=','m.messageid')
            ->where('app_message.receive',0)
            ->orderBy('create_at','desc')
            ->offset(($page-1)*$limit)
            ->limit($limit)
            ->get();

        if ($message->isEmpty()) {
            return api_json([],10002,'无数据');
        }else{
            return api_json(['info'=>$message->toArray()],200,'获取成功');
        }

    }
    /**
     *已读消息
     * 2019-2-10
     * by chenyu
     */
    public function isMessage(Request $request)
    {

        $messageid = $request->input('messageid');

        $data['messageid'] = $messageid;
        $data['status'] = 1;
        $data['userid'] = $this->userinfo->id;
        $data['create_at'] = today_time();

        DB::beginTransaction();//开启事务
        try {
            AppMessageIs::updateOrCreate(['userid' => $this->userinfo->id,'messageid'=>$messageid], $data);
            DB::commit();
            return api_json([],200,'成功');
        }catch(\Exception $exception) {
            //事务回滚
            DB::rollBack();
            return api_json([],90133,'失败');
//            return back()->withErrors($exception->getMessage())->withInput();
        }


    }
    /**
     *帮助中心
     * 2019-02-20
     * by chenyu
     **/
    public function getHelpcenter(Request $request)
    {
        $type = $request->input('type');
        switch ($type){

            case 1://使用指南
                $url = env('APP_URL_API').'v4/helpcenter/index?id=1';
                return api_json(['url'=>$url],200,'获取成功');
                break;
            case 2://扫描说明
                $url = env('APP_URL_API').'v4/helpcenter/index?id=2';
                return api_json(['url'=>$url],200,'获取成功');
                break;
            case 3://手动调整说明
                $url = env('APP_URL_API').'v4/helpcenter/index?id=3';
                return api_json(['url'=>$url],200,'获取成功');
                break;
            case 4://意见反馈
                $url = env('APP_URL_API').'v4/helpcenter/index?id=4';
                return api_json(['url'=>$url],200,'获取成功');
                break;
            default:
                return api_json([],10001,'参数信息有误');

        }
    }

    /**
     *手机绑定
     * 2019-3-6
     * chenyu
     */
    public function updatePhone(Request $request)
    {
        $oldphone = $request->input('oldphone');
        $password = $request->input('password');
        $newphone = $request->input('newphone');
        $code = $request->input('code');

        if($this->userinfo->member_type > 0){
            return api_json([],90607,$oldphone.'是会员用户，无法修改，如需修改请联系客服');
        }
        //验证码 校验
        $url = config('app.app_configs.sms_url');
        $sms_appkey = config('app.app_configs.sms_appkey');
        $zone = 86;
        if(empty($code) || !is_numeric($code)){
            return api_json([],'90108','验证码格式不正确');
        }
        if(empty($oldphone) || !rules_phone($oldphone) || empty($newphone) || !rules_phone($newphone) ){
            return api_json([],'90107','手机号格式错误');
        }
        $post_date=[
            'appkey'=>$sms_appkey,
            'phone'=>$newphone,
            'zone'=>$zone,
            'code'=>$code,
        ];
        $result = $this->uCurl($url,'post',$post_date);//网络请求

        if($result['status'] !==200){//判断验证码
//        if(false){//判断验证码
            return api_json([],90109,'验证码错误');
        }else {//验证码成功

            if($this->userinfo->phone ==$oldphone && $this->userinfo->password ==  md5(md5('yxx'.$password))) {
                $new_user =  DB::table('app_user')
                    ->where('phone',$newphone)
                    ->where('is_del',0)
                    ->first();

                if($new_user){
                    return api_json([],90606,$newphone.'该用户已经注册，绑定失败');
                }else{


                    $app_member =  DB::table('app_member')
                        ->where('telephone',$newphone)
                        ->where('is_del',0)
                        ->first();
                    if($app_member){
                        return api_json([],90607,$newphone.'是会员用户，无法修改，如需修改请联系客服');
                    }

                    DB::beginTransaction();//开启事务
                    try {
                        DB::table('app_user')
                            ->where('id', $this->userinfo->id)
                            ->update(['phone' => $newphone, 'token' => '', 'update_at' => today_time()]);

                        DB::commit();
                        return api_json([], 200, '成功');
                    } catch (\Exception $exception) {
                        //事务回滚
                        DB::rollBack();
                        return api_json([], 90605, '手机号修改失败');
//            return back()->withErrors($exception->getMessage())->withInput();
                    }
                }
            }else{
                return api_json([],90604,'账号密码错误，修改失败');
            }

        }

    }
    /**
     *网络请求
     *
     **/
    public function uCurl($url,$method,$post_data = 0)
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