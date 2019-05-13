<?php
namespace App\Http\Controllers\Mingde;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\V1\AuthControllor;
use Illuminate\Support\Facades\Redis;

class IndexController extends CommonController
{

    public function kks()
    {
        $command = '/usr/local/nginx/html/yanxuxu/app/Http/Controllers/V1/PZAlgCore /usr/local/nginx/html/yanxuxu/app/Http/Controllers/V1/1.png';
        $escp_command = escapeshellcmd($command);
        exec($escp_command,$r,$status);
        $arr = json_decode($r[0],true);
        echo "<pre>";
        print_r($arr);exit;
    }
    /**
     *获取热门搜索关键词
     * 2018-8-16
     * by chenyu
     */
    public function bd_hot_kw()
    {

        $list =  [
            "验嘘嘘",
            "艾蓓乐",
            "尿液检测",
            "微量元素",
            "指标",
            "安全",
            "准确",
        ];
        return $this->api_json($list,'Success','获取热门搜索词');
    }
    /**
     *获取分类列表、以及热门问题
     * 2018-8-16
     * by chenyu
     */
    public function bd_cate()
    {
        $hlist = DB::table('answer')
            ->select('id','title','not_resolve','is_resolve')
            ->where(['is_delete' => 0])
            ->orderBy('views','DESC')
            ->limit(20)
            ->get();

        $list =DB::table('answer_cate')
            ->select('id','title' )
            ->where(['is_delete' => 0])
            ->get();
        $hot_kw =  [
            "验嘘嘘",
            "艾蓓乐",
            "尿液检测",
            "微量元素",
            "指标",
            "安全",
            "准确",
        ];
        return $this->api_json(['list'=>$list->toArray(),'hot_list'=>$hlist->toArray(),'hot_kw'=>$hot_kw],'Success','获取热门搜索词');

    }
    /**
     *百问百答搜索
     * 2018-8-16
     * by chenyu
     */
    public function bd_list(Request $request)
    {
        $p = $request->input('p');
        $l = $request->input('l');
        $limit = $l*($p-1);
        if($request->has('cid') && $request->input('cid')!==null){
            $cid = $request->input('cid');
            if($cid == '0'){
                $hlist = DB::table('answer')
                    ->select('id','title','not_resolve','is_resolve')
                    ->where(['is_delete' => 0])
                    ->orderBy('views','DESC')
                    ->offset($limit)
                    ->limit($l)
                    ->get();
                return $this->api_json(['list'=>$hlist->toArray()],'Success','获取热门搜索');
            }else{
                $list =DB::table('answer')
                    ->select('id','title','not_resolve','is_resolve')
                    ->where(['cid'=>$cid,'is_delete'=>0])
                    ->orderBy('views','DESC')
                    ->offset($limit)
                    ->limit($l)
                    ->get();
                return $this->api_json(['list'=>$list->toArray()],'Success','获取指定cid列表成功');
            }
        }
        if($request->has('kw')){
            $kw = $request->input('kw');
            $list = DB::table('answer')
                ->select('id','title')
                ->where('is_delete',0)
                ->where('title','like',"%".$kw."%")
                ->orderBy('views','DESC')
                ->offset($limit)
                ->limit($l)
                ->get();
            return $this->api_json(['list'=>$list->toArray()],'Success','获取指定kw列表成功');

        }
        return $this->api_json(['list'=>[]],'Success','获取指定kw列表成功');
    }
    /**
     *问题详情页
     * 2018-8-17
     * by chenyu
     **/
    public function bd_detail(Request $request)
    {
        $id = $request->input('id');
        $token = $request->input('token');
        DB::table('answer')->where('id',$id)->increment('views');
        $useInfo = DB::table('xcx_user')->where('token',$token)->first();

        $detail = DB::table('answer')
            ->select('id','title','content')
            ->where('id',$id)
            ->where('is_delete',0)
            ->first();

        $record = DB::table('answer as a')
            ->select('r.id','r.status')
            ->leftJoin('answer_record as r','r.aid','=','a.id')
            ->where('a.id',$id)
            ->where('a.is_delete',0)
            ->where('xuid',$useInfo->id)
            ->first();
        if($record){
            $detail->status = $record->status;
        }
        return $this->api_json(['detail'=>$detail],'Success','获取详情成功');
    }
    /**
     *标记问题结果
     * 2018-9-30
     * by chenyu
     */
    public function bd_detail_record(Request $request)
    {
        $id = $request->input('id');
        $val = $request->input('val');
        $token = $request->input('token');
        if(empty($token)){
            return $this->api_json('','500','反馈失败');
        }
        $xuser = DB::table('xcx_user')
            ->select('id')
            ->where('token',$token)
            ->first();
        $data['aid'] = $id;
        $data['xuid'] = $xuser->id;
        $data['status'] = $val;
        $data['updatetime'] = date('Y-m-d H:i:s',time());
        $result=DB::table('answer_record')
            ->insert($data);
        if($result){
            if($val == 1){
                DB::table('answer')->where('id',$id)->increment('is_resolve');
            }else{
                DB::table('answer')->where('id',$id)->increment('not_resolve');
            }
            return $this->api_json('','200','反馈成功');
        }else{
            return $this->api_json('','500','反馈失败');
        }

    }
    /**
     *获取用户openid
     * 2018-8-17
     * by chenyu
     */
    public function loginOpenid(Request $request)
    {
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=APPID&secret=SECRET&js_code=JSCODE&grant_type=authorization_code';
    }
    /**
     *测试登录
     */
    public function testLogin(Request $request)
    {
        Redis::set('name', 'guwenjie');
        $values = Redis::get('name');
        dd($values);
        $phone = $request->input('phone');
        $token = md5($phone . time() . "yxx");
//        $phone = $this->CodePlusPass($phone);
        $usr = DB::table('User')
            ->where('telephone',$phone)
            ->first();
        if($usr){
           $res =  DB::table('User')
                ->where('id',$usr->id)
                ->update(['token' => $token]);
            return $this->api_json(['token'=>$token],'Success','测试接口登录成功');
        }
    }
    /**
     *咨询
     * 2018-8-22
     * by chenyu
     **/
    public function IndexNews(Request $request)
    {
        $p = $request->input('p');
        $l = $request->input('l');
        $limit = $l*($p-1);
//        DB::connection()->enableQueryLog();
        $list = DB::table('App_News')
            ->select("id", "sort", "title", "img_url", "show_count","create_time")
            ->where('is_del',0)
            ->orderBy('index_show','desc')
            ->orderBy('sort','asc')
            ->orderBy('create_time','desc')
            ->offset($limit)
            ->limit($l)
            ->get();
//        $log = DB::getQueryLog();
//        var_dump($log);exit;
        if(!empty($list)){
            return $this->api_json($list->toArray(),'Success','列表数据');
        }else{
            return $this->api_json([],'Success','null');
        }
    }
    /**
     *咨询详情
     * 2018-8-22
     * by chenyu
     **/
    public function IndexNewsDetail(Request $request)
    {
        $id = $request->input('id');
        $detail = DB::table('App_News')
            ->select('title','content')
            ->where('id',$id)
            ->first();
        return $this->api_json($detail,'Success','咨询详情');
    }
    /**
     *获取openid
     * 2018-9-19
     * by chenyu
     */
    public function getOpenid(Request $request)
    {
        $code = $request->input('code');
        $c = config('auth');
        $xcx = $c['xcx'];
        $URL = "https://api.weixin.qq.com/sns/jscode2session?appid=".$xcx['appid']."&secret=".$xcx['AppSecret']."&js_code=".$code."&grant_type=authorization_code";
        $result = file_get_contents($URL);
        $result = json_decode($result,true);
        $user_info = DB::table('xcx_user')
            ->select('id','uid','info_k')
            ->where('openid',$result['openid'])
            ->first();
        if(!empty($user_info)){//存在
            $token = md5($result['openid'] . time() . "yxx");
            DB::table('xcx_user')->where('id',$user_info->id)->update(['token'=>$token]);
            if($user_info->uid==-1){
                return $this->api_json(['token'=>$token,'key'=>$result['session_key'],'info_k'=>$user_info->info_k,'uid'=>-1],'200','');
            }else{
                return $this->api_json(['token'=>$token,'key'=>$result['session_key'],'info_k'=>$user_info->info_k,'uid'=>$user_info->uid],'200','');
            }
        }else{//需同步 已有用户

            $token = md5($result['openid'] . time() . "yxx");
            $xcx_data['openid'] = $result['openid'];
            $xcx_data['sessionkey'] = $result['session_key'];
            $xcx_data['create_time'] = date('Y-m-d H:i:s',time());
            $xcx_data['token'] = $token;
            $bool=DB::table('xcx_user')
                ->insert($xcx_data);
            if($bool){
                return $this->api_json(['token'=>$token,'key'=>$result['session_key'],'info_k'=>1,'uid'=>-1],'200','');
            }else{

            }
        }
    }
    /**
     * 获取手机号
     * 2018-9-19
     * by chenyu
     **/
    public function getWxPhone(Request $request)
    {

        $c = config('auth');
        $xcx = $c['xcx'];
        $encryptedData = $request->input('encryptedData');
        $iv = $request->input('iv');
        $key = $request->input('key');
        $auth = New AuthControllor($xcx['appid'],$key);
        $code = $auth->decryptData($encryptedData, $iv, $data);
        $token = $request->input('token');
        if ($code == 0) {//手机号 成功
            $phone_code = json_decode($data,true);
            $phone = $phone_code['phoneNumber'];
            $res = DB::table('User')
                ->select('id')
                ->where('telephone',$phone)
                ->first();
            if(!empty($res)){//用户存在
                $data_u['uid'] = $res->id;
                DB::table('xcx_user')
                    ->where('token',$token)
                    ->update($data_u);
            }else{//用户不存在
                $u_data['telephone']=$phone;
                $u_data['create_time']=date('Y-m-d H:s:i',time());
                $uid = DB::table('User')
                    ->insertGetId($u_data);
                if($uid>0){
                    $xcx_data['uid'] = $uid;
                    DB::table('xcx_user')
                        ->where('token',$token)
                        ->update($xcx_data);
                }else{

                }
            }
            return $this->api_json('','200','手机号绑定成功');
        } else {
            return $this->api_json('','500','解析手机号失败');
        }
    }

}