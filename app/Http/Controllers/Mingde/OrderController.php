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

        $ip = '47.104.158.214';//监护人id
        //商品信息
        $pros = ClassProduct::where('sch_classproduct.id',$proid)
            ->select('sch_classproduct.*','admin_users.username','admin_users.id as userid')
            ->leftJoin('admin_users','admin_users.id','=','sch_classproduct.sale')
            ->first();
        if(!$pros){
            return $this->api_json([],500,'商品信息错误');
        }
        if($pros->is_sign == 0){//需要报名信息
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

            $ss = DB::table('sch_classorder')
                ->where('proid',$proid)
                ->where('trip',$trip)
                ->first();
//            if(!$ss){
//                $msg = '证件号为：'.$trips->card.'的出行人已经下单该产品，请不要再次下单';
//                return $this->api_json([],500,$msg);
//            }
        }

        //订单信息
        $data['userid'] = $this->userinfo->id;
        $data['orders'] = 'Proojk'.$pros->id.date("w").time();
        $data['title'] = $pros->title;
        $data['xueshengname'] = empty($trips->name)?'':$trips->name;
        $data['school'] = empty($trips->school)?'':$trips->school;
        $data['grade'] = empty($trips->grade)?'':$trips->grade;
        $data['class'] = empty($trips->class)?'':$trips->class;
        $data['pay_status'] = 0;//0未支付
        $data['pay'] = $pros->price;
        $data['channel'] = $pros->channel;//走账公司
        $data['sale'] = $pros->username;
        $data['sale_id'] = $pros->userid;
        $data['created_at'] = today_time();
        $data['updated_at'] = today_time();
        $data['card1'] = empty($trips->card1)?'':$trips->card1;
        $data['card2'] = empty($trips->card2)?'':$trips->card2;
        $data['guarder'] = empty($guarders->id)?'':$guarders->id;
        $data['trip'] = empty($trips->id)?'':$trips->id;
        $data['proid'] = empty($pros->id)?'':$pros->id;
        $data['invoice'] = empty($trips->invoice)?'0':$trips->invoice;
        $data['email'] = empty($trips->email)?'':$trips->email;
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

        if($pros->is_pay ==0) {

            //支付预订单
            $channel = DB::table('sch_classchannel')
                ->where('id', $pros->channel)
                ->first();
            $order['appid'] = $channel->appid;//应用ID
            $order['mch_id'] = $channel->mchid;//商户号
            $order['nonce_str'] = md5('pzzk2018');//随机字符串
            $order['body'] = '世纪明德';//商品描述
            $order['out_trade_no'] = $data['orders'];//商户订单号
            $order['total_fee'] = 3 * 100;//总金额
            $order['spbill_create_ip'] = $ip;//终端IP
            $order['notify_url'] = $channel->notify_url;//通知地址
            $order['trade_type'] = 'JSAPI';//交易类型
            $order['openid'] = $this->userinfo->openid;//通知地址
            //            $order['total_fee'] = $data['pay'] * 100;//总金额
            $order['sign'] = $this->sign_do($order, $channel->appsecret);//签名
            $Submission = $this->arrayToXml($order);
            $dataxml = $this->curlMet($channel->unif, 'post', $Submission);
            $objectxml = (array)simplexml_load_string($dataxml, 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($objectxml['return_code'] == 'SUCCESS') {
                if ($objectxml['result_code'] == 'SUCCESS') {//成功
                    //组装qpp数据
                    $order_app['appid'] = $channel->appid;
                    $order_app['partnerid'] = $channel->mchid;
                    $order_app['prepayid'] = $objectxml['prepay_id'];
                    $order_app['package'] = 'Sign=WXPay';
                    $order_app['nonceStr'] = $order['nonce_str'];
                    $order_app['timeStamp'] = time();
                    $order_app['paySign'] = $this->sign_do($order_app, $channel->appsecret);//签名
                    $order_app['order'] = $data['orders'];
                    return api_json($order_app, 200, '成功');

                } else {
                    return api_json([], 500, '支付失败');
                }
            } else {
                return api_json([], 500, '支付失败');
            }
        }else{//不需要支付
            return api_json(['orderid'=>$orderid], 200, '成功');
        }
    }
    /**
     *确定订单、支付成功
     */
    public function isOrderOk(Request $request)
    {
        $orderid=$request->input('oid');
        $order_info = DB::table('sch_classorder')
            ->where('orders',$orderid)
            ->first();
        $channel = DB::table('sch_classchannel')
            ->where('id', $order_info->channel)
            ->first();
        if($order_info){
            if($order_info->pay_status == 1){//已支付成功
                return api_json([],200,'支付成功');
            }else{
                //查询订单支付状态
                $order['appid'] = $channel->appid;
                $order['mch_id'] = $channel->mchid;
                $order['out_trade_no'] = $order_info->orders;
                $order['nonce_str'] = md5('pzzk2018');//随机字符串
                $order['sign'] =$this->sign_do($order,$channel->appsecret);//签名

                $Submission = $this->arrayToXml($order);
                $dataxml = $this->curlMet(config('app.wx_configs.orderquery'),'post',$Submission);
                $objectxml = (array)simplexml_load_string($dataxml, 'SimpleXMLElement', LIBXML_NOCDATA);
                if($objectxml['return_code'] == 'SUCCESS' && $objectxml['result_code'] == 'SUCCESS') {
                    if ($objectxml['trade_state'] == 'SUCCESS') {//成功
                        $sign = $objectxml['sign'];
                        unset($objectxml['sign']);
                        if($this->sign_do($objectxml,$channel->appsecret) == $sign) {//验证签名
//                            Log::info('Showing user profile for user: '.$id);
                            DB::beginTransaction();
                            try {

                                $class_order['pay_status'] =1;
                                $class_order['pay_time'] = today_time();
                                $class_order['transaction_id'] = $objectxml['transaction_id'];
                                $class_order['total_fee'] = $objectxml['total_fee'];
                                $class_order['cash_fee'] = $objectxml['cash_fee'];
                                $class_order['res_json'] = json_encode($objectxml);
                                //成功修改订单状态
                                DB::table('sch_classorder')
                                    ->where('orders',$orderid)
                                    ->update($class_order);
                                //提交
                                DB::commit();
                                return api_json([],200,'支付成功');
                            }catch(\Exception $exception) {
                                //事务回滚
                                DB::rollBack();
                                return api_json([],500,'失败');
                                //            return back()->withErrors($exception->getMessage())->withInput();
                            }
                        }else{
                            return api_json([],500,'验证签名失败');
                        }
                    }else{//支付失败
                        return api_json([],500,'支付失败');
                    }
                }else{//借口信息 错误
                    return api_json([],500,'支付失败');
                }
            }
        }else{
            return api_json([],500,'该订单不存在');
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
        $birthday = $request->input('birthday');
        $size = $request->input('size');
        $nation = $request->input('nation');
        $card = $request->input('card');
        $card1 = $request->input('card1');
        $card2 = $request->input('card2');
        $healthy = $request->input('healthy');
        $healthytext = $request->input('healthytext');
        $invoice = $request->input('invoice');
        $email = $request->input('email');
        $cardtype = $request->input('cardtype');

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
           $data['invoice'] =$invoice;
           $data['email'] = $email;
           $data['cardtype'] = $cardtype;
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
           $data['birthday'] = $birthday;
           $data['size'] = $size;
           $data['nation'] = $nation;
           $data['card'] = $card;
           $data['card1'] = $card1;
           $data['card2'] = $card2;
           $data['healthy'] = $healthy;
           $data['healthytext'] = $healthytext;
           $data['created_at'] = today_time();
           $data['updated_at'] = $data['created_at'];
           $data['is_del'] = 0;
           $data['invoice'] =$invoice;
           $data['email'] = $email;
           $data['cardtype'] = $cardtype;

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
            ->select('id','name','sex','card','height','school','grade','class','phone','nation','cardtype','card','card1','card2','healthy','healthytext','invoice','email','size','birthday')
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
        $cardtype= $request->input('cardtype');

        if(!empty($id)){//修改
            $data['name'] = $name;
            $data['phone'] = $phone;
            $data['relation'] = $relation;
            $data['card'] = $card;
            $data['cardtype'] = $cardtype;
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
            $data['cardtype'] = $cardtype;
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
            ->select('id','name','phone','relation','card','cardtype')
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
            if($file){
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

        }else{
            return $this->api_json([],500,'未检查到文件');
        }

    }
    /**
     *订单列表
     */
    public function orderList(Request $request)
    {

       $status =  $request->input('status');

        $orders = DB::table('sch_classorder')
            ->select('sch_classorder.id','sch_classorder.orders','sch_classorder.title','sch_classproduct.title_fit','sch_classorder.pay','sch_classorder.created_at','sch_classorder.pay_status','sch_classproduct.image1')
            ->leftJoin('sch_classproduct','sch_classproduct.id','=','sch_classorder.proid')
            ->where('sch_classorder.userid',$this->userinfo->id)
            ->where('sch_classorder.pay_status',$status)
            ->orderBy('sch_classorder.created_at','desc')
            ->get();

        if($orders->isEmpty()){
            $or=[];
        }else{
            $or = $orders->toArray();
            foreach($or as $k=>$v){
                $or[$k]->img = config('app.app_configs.loadhost').$v->image1;
            }
        }

        return $this->api_json($or,200,'订单列表');
    }
    /**
     *订单详情
     */
    public function orderDetail(Request $request)
    {
        $id = $request->input('id');

        $detail = DB::table('sch_classorder')
            ->select('sch_classorder.pay_status','sch_classorder.orders','sch_classorder.pay','sch_classorder.created_at','sch_classorder.title','sch_classproduct.start_time','sch_classtrip.name as tname','sch_classtrip.card','sch_classguarder.name as gname','sch_classguarder.phone as gphone','sch_classtrip.phone as tphone','sch_classguarder.relation')
            ->leftJoin('sch_classproduct','sch_classproduct.id','=','sch_classorder.proid')
            ->leftJoin('sch_classtrip','sch_classtrip.id','=','sch_classorder.trip')
            ->leftJoin('sch_classguarder','sch_classguarder.id','=','sch_classorder.guarder')
            ->where('sch_classorder.id',$id)
            ->first();
        $detail ->insurance = '旅游意外险';
        return $this->api_json($detail,200,'订单详情');

    }
    /**
     *申请退款
     */
    public function orderRefund(Request $request)
    {
        $id = $request->input('id');
        $tuitext = $request->input('text');

        $order = DB::table('sch_classorder')
            ->where('id',$id)
            ->first();
        if($order-> pay_status == 1){//已支付订单

            $order = DB::table('sch_classorder')
                ->where('id',$id)
                ->update(['pay_status'=>2,'tuitext'=>$tuitext,'tui_time'=>today_time()]);
            if($order){//申请退款成功
                return $this->api_json([],200,'申请退款成功');
            }else{
                return $this->api_json([],500,'申请退款失败');
            }
        }else{//未支付 或  已退款
            return $this->api_json([],500,'未支付订单');
        }

    }
}