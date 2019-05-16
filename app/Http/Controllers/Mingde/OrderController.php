<?php
namespace App\Http\Controllers\Mingde;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

use App\ClassProduct;
use App\ClassTrip;
use App\ClassGuarder;

class OrderController extends CommonController
{
    /**
     *添加订单
     */
    public function addOrder(Request $request){

        $proid = $request->input('proid');
        $trip = $request->input('trip');//出行人id
        $guarder = $request->input('guarder');//监护人id
        $ip = $request->input('ip');//监护人id
        //商品信息
        $pros = ClassProduct::where('sch_classproduct.id',$proid)
            ->select('sch_classproduct.*','admin_users.username','admin_users.id as userid')
            ->leftJoin('admin_users','admin_users.id','=','sch_classproduct.sale')
            ->first();
        if(!$pros){
            return $this->api_json([],500,'商品信息错误');
        }
        //出行人信息
        $trips = ClassTrip::where('id',$trip)->first();
        if(!$trips){
            return $this->api_json([],500,'出行人信息错误');
        }
        //监护人信息
        $guarders = ClassGuarder::where('id',$guarder)->first();
        if(!$guarders){
            return $this->api_json([],500,'监护人信息错误');
        }

        //订单信息
        $data['orders'] = 'Proojk'.$pros->id.date("w").time();
        $data['title'] = $pros->title;
        $data['xueshengname'] = $trips->name;
        $data['school'] = $trips->school;
        $data['grade'] = $trips->grade;
        $data['class'] = $trips->class;
        $data['pay_status'] = 0;//0未支付
        $data['pay'] = $pros->price;
        $data['channel'] = $pros->channel;//走账公司
        $data['sale'] = $pros->username;
        $data['sale_id'] = $pros->userid;
        $data['created_at'] = today_time();
        $data['updated_at'] = today_time();
        $data['card1'] = $trips->card1;
        $data['card2'] = $trips->card2;
        $data['guarder'] = $guarders->id;
        $data['trip'] = $trips->id;

        DB::beginTransaction();//开启事务
        try {
            $orderid= DB::table('sch_classorder')
                ->insertGetId($data);
            DB::commit();
        }catch(\Exception $exception) {
            //事务回滚
            DB::rollBack();
            return api_json([],500,'DB错误');
//            return back()->withErrors($exception->getMessage())->withInput();
        }

        //支付预订单
        $channel = DB::table('sch_classchannel')
            ->where('id',$pros->channel)
            ->first();
        $order['appid'] =$channel->appid;//应用ID
        $order['body'] = '实际明德';//商品描述
        $order['mch_id'] = $channel->mchid;//商户号
        $order['nonce_str'] = md5('pzzk2018');//随机字符串
        $order['notify_url'] = $channel->notify_url;//通知地址
        $order['out_trade_no'] = $data['orders'];//商户订单号
        $order['spbill_create_ip'] = $ip;//终端IP
        $order['total_fee'] = $data['pay']*100;//总金额
        $order['trade_type'] = 'JSAPI';//交易类型
        $order['sign'] = $this->sign_do($order,$channel->appsecret);//签名
        $Submission = $this->arrayToXml($order);
        $dataxml = $this->curlMet($channel->unif,'post',$Submission);
        var_dump($dataxml);exit;
        $objectxml = (array)simplexml_load_string($dataxml, 'SimpleXMLElement', LIBXML_NOCDATA);
        print_r($objectxml);exit;
        if($objectxml['return_code'] == 'SUCCESS')  {
            if($objectxml['result_code'] == 'SUCCESS') {//成功
                //组装qpp数据
                $order_app['appid'] = $channel->appid;
                $order_app['partnerid'] = $channel->mchid;
                $order_app['prepayid'] = $objectxml['prepay_id'];
                $order_app['package'] = 'Sign=WXPay';
                $order_app['nonceStr'] = $order['nonce_str'];
                $order_app['timeStamp'] = time();
                $order_app['paySign'] =$this->sign_do($order_app,$channel->appsecret);//签名
                $order_app['order']=$data['orders'];
                return api_json($order_app,200,'成功');

            }else{
                return api_json([],500,'支付失败');
            }
        }else{
            return api_json([],500,'支付失败');
        }


    }
    /**
     *签名
     */
    public function sign_do($arr,$appsecret)
    {
        ksort($arr);//排序
        $str = http_build_query($arr)."&key=".$appsecret;
        $str = $this->arrToUrl($str);
        return  strtoupper(md5($str));
    }
    //URL解码为中文
    public function arrToUrl($str)
    {
        return urldecode($str);
    }
    /**
     *转xml 格式
     */
    function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            $xml.="<".$key.">".$val."</".$key.">";
//            if (is_numeric($val)){
//                $xml.="<".$key.">".$val."</".$key.">";
//            }else{
//                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
//            }
        }
        $xml.="</xml>";
        return $xml;
    }
    /**
     *添加/修改出行人
     **/
    public function addOrSetBaby(Request $request)
    {
        $id = $request->input('id');
        $proid = $request->input('proid');
        $name= $request->input('name');
        $sex = $request->input('sex');
        $height = $request->input('height');
        $school = $request->input('school');
        $grade = $request->input('grade');
        $class = $request->input('class');
        $phone = $request->input('phone');
        $nation = $request->input('nation');
        $card = $request->input('card');
        $card1 = $request->input('card1');
        $card2 = $request->input('card2');
        $healthy = $request->input('healthy');
        $healthytext = $request->input('healthytext');

       if(!empty($id)){//修改

           $data['name'] = $name;
           $data['sex'] = $sex;
           $data['height'] = $height;
           $data['school'] = $school;
           $data['grade'] = $grade;
           $data['class'] = $class;
           $data['phone'] = $phone;
           $data['nation'] = $nation;
           $data['card'] = $card;
           $data['card1'] = $card1;
           $data['card2'] = $card2;
           $data['healthy'] = $healthy;
           $data['healthytext'] = $healthytext;
           $data['updated_at'] = today_time();
           $update = DB::table('sch_classtrip')
               ->where('id',$id)
               ->update($data);
           if($update){//添加成功
               return $this->api_json(['id'=>$id],200,'修改成功');
           }else{
               return $this->api_json([],500,'修改失败');
           }
       }else{//添加
           $data['uid'] = $this->userinfo->id;
           $data['proid'] = $proid;
           $data['name'] = $name;
           $data['sex'] = $sex;
           $data['height'] = $height;
           $data['school'] = $school;
           $data['grade'] = $grade;
           $data['class'] = $class;
           $data['phone'] = $phone;
           $data['nation'] = $nation;
           $data['card'] = $card;
           $data['card1'] = $card1;
           $data['card2'] = $card2;
           $data['healthy'] = $healthy;
           $data['healthytext'] = $healthytext;
           $data['created_at'] = today_time();
           $data['updated_at'] = $data['created_at'];
           $data['is_del'] = 0;

           $insertid =DB::table('sch_classtrip')
               ->insertGetId($data);
           if($insertid>0){//添加成功
               return $this->api_json(['id'=>$insertid],200,'添加成功');
           }else{
               return $this->api_json([],500,'添加失败');
           }
       }
    }
    /**
     *出行人列表
     **/
    public function babyList(Request $request)
    {
        $proid = $request->input('proid');

        $list = DB::table('sch_classtrip')
            ->select('id','name','sex','card','height','school','grade','class','phone','nation','card','card1','card2','healthy','healthytext')
            ->where('proid',$proid)
            ->where('uid',$this->userinfo->id)
            ->where('is_del',0)
            ->orderBy('created_at','desc')
            ->get();
        if($list->isEmpty()){
            $lists = [];
        }else{
            $lists = $list->toArray();
        }
        return $this->api_json($lists,200,'获取成功');
    }
    /**
     *删除
     */
    public function babyDel(Request $request)
    {
        $id = $request->input('id');
        $update = DB::table('sch_classtrip')
            ->where('id',$id)
            ->update(['is_del'=>0,'updated_at'=>today_time()]);
        if($update){
            return $this->api_json(['id'=>$id],200,'删除成功');
        }else{
            return $this->api_json(['id'=>$id],500,'删除失败');
        }
    }
    /**
     *添加/修改监护人
     **/
    public function addOrSeGuarder(Request $request)
    {
        $id = $request->input('id');
        $proid = $request->input('proid');
        $name= $request->input('name');
        $phone = $request->input('phone');
        $relation = $request->input('relation');
        $card = $request->input('card');

        if(!empty($id)){//修改
            $data['name'] = $name;
            $data['phone'] = $phone;
            $data['relation'] = $relation;
            $data['card'] = $card;
            $data['updated_at'] = today_time();
            $update = DB::table('sch_classguarder')
                ->where('id',$id)
                ->update($data);
            if($update){//添加成功
                return $this->api_json(['id'=>$id],200,'修改成功');
            }else{
                return $this->api_json([],500,'修改失败');
            }
        }else{//添加
            $data['uid'] = $this->userinfo->id;
            $data['proid'] = $proid;
            $data['name'] = $name;
            $data['phone'] = $phone;
            $data['relation'] = $relation;
            $data['card'] = $card;
            $data['created_at'] = today_time();
            $data['updated_at'] = $data['created_at'];
            $data['is_del'] = 0;

            $insertid =DB::table('sch_classguarder')
                ->insertGetId($data);
            if($insertid>0){//添加成功
                return $this->api_json(['id'=>$insertid],200,'添加成功');
            }else{
                return $this->api_json([],500,'添加失败');
            }
        }
    }
    /**
     *监护人列表
     **/
    public function guarderList(Request $request)
    {
        $proid = $request->input('proid');

        $list = DB::table('sch_classguarder')
            ->select('id','name','phone','relation','card')
            ->where('proid',$proid)
            ->where('uid',$this->userinfo->id)
            ->where('is_del',0)
            ->orderBy('created_at','desc')
            ->get();
        if($list->isEmpty()){
            $lists = [];
        }else{
            $lists = $list->toArray();
        }
        return $this->api_json($lists,200,'获取成功');
    }
    /**
     *删除
     */
    public function guarderDel(Request $request)
    {
        $id = $request->input('id');
        $update = DB::table('sch_classguarder')
            ->where('id',$id)
            ->update(['is_del'=>0,'updated_at'=>today_time()]);
        if($update){
            return $this->api_json(['id'=>$id],200,'删除成功');
        }else{
            return $this->api_json(['id'=>$id],500,'删除失败');
        }
    }
    /**
     *上传图片
     **/
    public function uploadImg(Request $request){
        if ($request->isMethod('POST')){
            $file = $request->file('img');
            //判断文件是否上传成功
            if ($file->isValid()){
                //原文件名
                $originalName = $file->getClientOriginalName();
                //扩展名
                $ext = $file->getClientOriginalExtension();
                //MimeType
                $type = $file->getClientMimeType();
                //临时绝对路径
                $realPath = $file->getRealPath();
                $filename = uniqid().'.'.$ext;
                $bool = Storage::disk('public')->put($filename,file_get_contents($realPath));
                //判断是否上传成功
                if($bool){
                    return $this->api_json(['filename'=>$filename],200,'上传成功');
                }else{
                    return $this->api_json([],500,'上传失败');
                }
            }else{
                return $this->api_json([],500,'未检查到文件');
            }
        }else{
            return $this->api_json([],500,'未检查到文件');
        }

    }

}