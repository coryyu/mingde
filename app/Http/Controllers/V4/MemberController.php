<?php
namespace App\Http\Controllers\V4;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Controllers\V4\CommonController as Common;
use Illuminate\Support\Facades\Log;
use App\Member;

class MemberController extends Common
{

    /**
     *
     *会员卡类型获取
     * 2019-1-18
     * by chenyu
     *
     */
    public function getCardList()
    {

        $list = DB::table('app_member_card')
            ->select('id','name','pay','status','sort','logo')
            ->where('status', 0)
            ->orderBy('sort', 'asc')
            ->get();
        if (!$list->isEmpty()) {
            $lists = $list->toArray();
            foreach($lists as $k=>$v){
                $lists[$k]->logo = config('app.app_configs.loadhost').$v->logo;
            }
            return api_json(['info'=>$lists], 200, '获取成功');
        } else {//无数据
            return api_json([], 10002, '无数据');
        }
    }

    /**
     *读取会员信息
     * 2019-1-18
     * by chenyu
     */
    public function getMemberInfo()
    {
        $date = date('Y-m-d', time());
        $member = DB::table('app_member')
            ->select('app_member.mem_type', 'app_member.end_time', 'app_member.end_time', 'app_member.total', 'app_member.grant_num', 'app_member.do_test', 'app_member.contaminated','app_member_card.icon')
            ->leftJoin('app_member_card','app_member_card.id','=','app_member.mem_type')
            ->where('app_member.telephone', $this->userinfo->phone)
            ->where('app_member.end_time', '>=', $date)
            ->orderBy('app_member.pay', 'desc')
            ->get();
        if (!$member->isEmpty()) {

            $m_list = $member->toArray();

            $data['member_type'] = $m_list[0]->mem_type;//会员等级
            $data['official_token'] = $this->userinfo-> official_token;//会员推广id
            $data['logo'] =  config('app.app_configs.loadhost').$m_list[0]->icon;//

            $data['total'] = 0;
            $data['grant_num'] = 0;
            $data['do_test'] = 0;
            $data['contaminated'] = 0;
            foreach ($m_list as $k => $v) {
                $data['total'] = $v->total;
                $data['grant_num'] += $v->grant_num;
                $data['do_test'] += $v->do_test;
                $data['contaminated'] += $v->contaminated;
                if($v->mem_type == $data['member_type']){
                    $data['end_time'] = $v->end_time;
                }
            }

            return api_json($data, 200, '获取成功');

        } else {//无数据
            return api_json([], 10002, '无数据');
        }

    }

    /**
     * 实体卡信息
     * 2019-02-19
     * by chenyu
     */
    public function activateMember(Request $request)
    {

        $code = $request->input('code');

        $change = DB::table('app_member_change')
            ->select('app_member_change.id','app_member_change.channel','app_member_change.status','app_member_change.code','app_member_card.name','app_member_card.pay','app_member_card.icon','app_member_card.card','app_member_card.text')
            ->leftJoin('app_member_card','app_member_card.id','=','app_member_change.cardid')
            ->where('app_member_change.code',$code)
            ->where('app_member_change.is_del',0)
            ->first();
        if($change){
            if($change->status !== 0){
                return api_json([], 90601, '卡片已经使用过了');
            }else{
                return api_json($change, 200, '获取成功');
            }
        }else{
            return api_json([], 90600, '卡片信息有误');
        }

    }
    /**
     *激活实体卡
     * 2019-02-19
     * by chenyu
     */
    public function activateMemberDo(Request $request)
    {
        $code = $request->input('code');
        $addid = $request->input('add');

        $phone = $this->userinfo->phone;
        $app_m = DB::table('app_member')
            ->where('telephone',$phone)
            ->where('is_del',0)
            ->where('end_time','>',today_time())
            ->first();
        if($app_m){
            return api_json([],90603,'您已是会员，不能重复购买！');
        }

        $add = DB::table('app_user_add')
            ->where('id',$addid)
            ->first();
        if(!$add){
            return api_json([],90145,'用户地址不存在');
        }

        $change = DB::table('app_member_change')
            ->select('app_member_change.id','app_member_change.cardid','app_member_change.channel','app_member_change.status','app_member_change.code','app_member_card.name','app_member_card.pay','app_member_card.icon','app_member_card.card','app_member_card.text')
            ->leftJoin('app_member_card','app_member_card.id','=','app_member_change.cardid')
            ->where('app_member_change.code',$code)
            ->where('app_member_change.is_del',0)
            ->first();
        if($change){
            if($change->status !== 0){
                return api_json([], 90601, '卡片已经使用过了');
            }else{
                //执行 激活


                DB::beginTransaction();//开启事务
                try {

                    $member['name']=$add->name;
                    $member['add']=$add->text;
                    $member['mem_type']=$change->cardid;
                    $member['create_time']=today_time();
                    $member['telephone']=$this->userinfo->phone;
                    $member['update_time']= $member['create_time'];
                    $member['end_time']= date('Y-m-d',time()+31536000);;
                    $member['oid']= $code;
                    $member['usr']= 990;
                    $member['province']= $add->province;
                    $member['city']= $add->city;
                    $member['update_time']= $member['create_time'];
                    $member['total'] = 5;
                    $member['grant_num'] = 0;
                    $member['do_test'] = 0;
                    $member['contaminated'] = 0;
                    $member['is_del'] = 0;
                    $member['pay'] = $change->pay;
                    $member['channel'] = 1;

                    DB::table('app_member')
                        ->insert($member);

                    if($this->userinfo->member_type<$member['mem_type']){
                        DB::table('app_user')
                            ->where('id',$this->userinfo->id)
                            ->update(['member_type'=>$member['mem_type']]);
                    }

                    $member_change['status'] = 1;
                    $member_change['uid'] = $this->userinfo->id;

                    DB::table('app_member_change')
                        ->where('id',$change->id)
                        ->update($member_change);
                    //提交
                    DB::commit();
                }catch(\Exception $exception) {
                    //事务回滚
                    DB::rollBack();
                    return api_json([],90602,'实体卡激活失败');
//            return back()->withErrors($exception->getMessage())->withInput();
                }

                return api_json([], 200, '激活成功');

            }
        }else{
            return api_json([], 90600, '卡片信息有误');
        }

    }

    /**
     * 获取购买记录
     * 2019-1-18
     * by chenyu
     */
    public function getOrderList()
    {
        $orderlist = DB::table('app_member')
            ->select('app_member.pay','app_member.mem_type', 'app_member_card.name', 'app_member.create_time', 'app_member.telephone','app_member.channel')
            ->leftJoin('app_member_card','app_member_card.id','=','app_member.mem_type')
            ->where('app_member.telephone', $this->userinfo->phone)
            ->orderBy('app_member.create_time', 'desc')
            ->get();
        if (!$orderlist->isEmpty()) {
            $data = $orderlist->toArray();
            foreach($data as $k=>$v){
                $data[$k]->channelname = $v->channel == 1?'卡密激活':'在线购买';
            }
            return api_json(['info'=>$data], 200, '获取成功');
        } else {
            return api_json([], 10002, '无数据');
        }


    }

    /**
     *提交生成预订单
     * 2019-1-24
     * by chenyu
     */
    public function addOrder(Request $request)
    {
        $card = $request->input('card');
        $add = $request->input('add');
        $paytype = $request->input('paytype');//1：微信
        $ip = $request->input('ip');

        $phone = $this->userinfo->phone;
        $app_m = DB::table('app_member')
            ->where('telephone',$phone)
            ->where('is_del',0)
            ->where('end_time','>',today_time())
            ->first();
        if($app_m){
            return api_json([],90603,'您已是会员，不能重复购买！');
        }

        $cardP= DB::table('app_member_card')
            ->select('pay')
            ->where('id',$card)
            ->where('status',0)
            ->first();
        if(!$cardP){
            return api_json([],90144,'该卡不存在');
        }

        $addP= DB::table('app_user_add')
            ->select('province','city','area','text','name','add_tel')
            ->where('id',$add)
            ->where('uid',$this->userinfo->id)
            ->where('is_del',0)
            ->first();
        if(!$addP){
            return api_json([],90145,'用户地址不存在');
        }

        $member_order['name'] =$addP->name;
        $member_order['add'] =$addP->text;
        $member_order['pay'] =$cardP->pay;
        $member_order['mem_type'] =$card;
        $member_order['pay_status'] =0;//未支付订单
        $member_order['create_time'] =today_time();
        $member_order['update_time'] =$member_order['create_time'];
        $member_order['telephone'] =$this->userinfo->phone;
        $member_order['orderid'] =date('YmdHis',time()).$this->userinfo->id.rand(10,99).rand(10,99);
        $member_order['province'] =$addP->province;
        $member_order['city'] =$addP->city;
        $member_order['area'] =$addP->area;
        $member_order['add_tel'] =$addP->add_tel;
        $member_order['usr'] =990;

        $order_insert = DB::table('app_member_order')
            ->insertGetId($member_order);
        if($order_insert>0){
            switch($paytype){
                case 1://微信

                    //创建预订单
                    $order['appid'] =config('app.wx_configs.appid');//应用ID
                    $order['body'] = 'PZZK';//商品描述
                    $order['mch_id'] = config('app.wx_configs.mchid');//商户号
                    $order['nonce_str'] = md5('pzzk2018');//随机字符串
                    $order['notify_url'] = config('app.wx_configs.notify_url');//通知地址
                    $order['out_trade_no'] = $member_order['orderid'];//商户订单号
                    $order['spbill_create_ip'] = $ip;//终端IP
                    $order['total_fee'] = $member_order['pay']*100;//总金额
                    $order['trade_type'] = 'APP';//交易类型
                    $order['sign'] = $this->sign_do($order);//签名
                    $Submission = $this->arrayToXml($order);
                    $dataxml = $this->curlMet(config('app.wx_configs.unifiedorder'),'post',$Submission);
                    $objectxml = (array)simplexml_load_string($dataxml, 'SimpleXMLElement', LIBXML_NOCDATA);
                    if($objectxml['return_code'] == 'SUCCESS')  {
                        if($objectxml['result_code'] == 'SUCCESS'){//成功
                            //组装qpp数据
                            $order_app['appid'] = config('app.wx_configs.appid');
                            $order_app['partnerid'] = config('app.wx_configs.mchid');
                            $order_app['prepayid'] = $objectxml['prepay_id'];
                            $order_app['package'] = 'Sign=WXPay';
                            $order_app['noncestr'] = $order['nonce_str'];
                            $order_app['timestamp'] = time();

                            $order_app['sign'] =$this->sign_do($order_app);//签名

                            $order_app['order']=$member_order['orderid'];
                            return api_json($order_app,200,'成功');

                        }else{
                            return api_json([],90147,'支付失败');
                        }
                    }else{
                        return api_json([],90147,'支付失败');
                    }
                    break;
                //todo
                default:
                    return api_json([],90146,'未开通支付方式');
            }
        }else{
            return api_json([],90147,'支付失败');
        }

    }


    /**
     *确定订单、支付成功
     * 2019-1-24
     * by chenyu
     */
    public function isOrderOk(Request $request)
    {
        $orderid=$request->input('oid');
        $order_info = DB::table('app_member_order')
            ->where('orderid',$orderid)
            ->first();
        if($order_info){
            if($order_info->pay_status == 2){//已支付成功
                return api_json([],200,'支付成功');
            }else{
                //查询订单支付状态
                $order['appid'] = config('app.wx_configs.appid');
                $order['mch_id'] = config('app.wx_configs.mchid');
                $order['out_trade_no'] = $order_info->orderid;
                $order['nonce_str'] = md5('pzzk2018');//随机字符串
                $order['sign'] =$this->sign_do($order);//签名

                $Submission = $this->arrayToXml($order);
                $dataxml = $this->curlMet(config('app.wx_configs.orderquery'),'post',$Submission);
                $objectxml = (array)simplexml_load_string($dataxml, 'SimpleXMLElement', LIBXML_NOCDATA);
                if($objectxml['return_code'] == 'SUCCESS' && $objectxml['result_code'] == 'SUCCESS') {
                    if ($objectxml['trade_state'] == 'SUCCESS') {//成功
                        $sign = $objectxml['sign'];
                        unset($objectxml['sign']);
                        if($this->sign_do($objectxml) == $sign) {//验证签名
//                            Log::info('Showing user profile for user: '.$id);

                            DB::beginTransaction();
                            try {

                                $member_order['transaction_id'] = $objectxml['transaction_id'];
                                $member_order['total_fee'] = $objectxml['total_fee'];
                                $member_order['cash_fee'] = $objectxml['cash_fee'];
                                $member_order['res_json'] = json_encode($objectxml);
                                $member_order['pay_status'] = 2;//已支付
                                $member_order['update_time'] = today_time();
                                //更新支付状态
                                DB::table('app_member_order')
                                    ->where('orderid',$orderid)
                                    ->update($member_order);

                                $app_member['name'] = $order_info->name;
                                $app_member['add'] = $order_info->add;
                                $app_member['mem_type'] = $order_info->mem_type;
                                $app_member['create_time'] = today_time();
                                $app_member['telephone'] = $order_info->telephone;
                                $app_member['update_time'] = $app_member['create_time'];
                                $app_member['end_time'] = date('Y-m-d',time()+31536000);
                                $app_member['oid'] = $order_info->orderid;
                                $app_member['usr'] = 990;
                                $app_member['province'] = $order_info->province;
                                $app_member['city'] = $order_info->city;
                                $app_member['total'] = 5;
                                $app_member['grant_num'] = 0;
                                $app_member['do_test'] = 0;
                                $app_member['contaminated'] = 0;
                                $app_member['is_del'] = 0;
                                $app_member['pay'] = $order_info->pay;
                                $app_member['channel'] = 2;//支付订单

                                $app_member_res =Member::updateOrCreate(['telephone' => $order_info->telephone,'oid'=>$order_info->orderid], $app_member);

                                if($this->userinfo->member_type<$app_member['mem_type']){
                                    DB::table('app_user')
                                        ->where('id',$this->userinfo->id)
                                        ->update(['member_type'=>$app_member['mem_type']]);
                                }

                                //提交
                                DB::commit();
                                return api_json([],200,'支付成功');
                            }catch(\Exception $exception) {
                                //事务回滚
                                DB::rollBack();
                                return api_json([],'90126','失败');
                    //            return back()->withErrors($exception->getMessage())->withInput();
                            }
                        }else{
                            return api_json([],90158,'验证签名失败');
                        }
                    }else{//支付失败
                        return api_json([],90147,'支付失败');
                    }
                }else{//借口信息 错误
                    return api_json([],90147,'支付失败');
                }
            }
        }else{
            return api_json([],90148,'该订单不存在');
        }
    }
    /**
     *获取 会员推荐记录
     * 2019-3-5
     * by chenyu
     **/
    public function getSaleOrder(Request $request)
    {

        $member = DB::table('app_member')
            ->select('app_member_order.orderid','app_member_card.name','app_member_card.logo','app_member_card.logo','app_integral_list.sum','app_member.telephone','app_member.pay','app_member.create_time')
            ->leftJoin('app_member_card','app_member_card.id','=','app_member.mem_type')
            ->leftJoin('app_member_order','app_member_order.id','=','app_member.oid')
            ->leftJoin('app_integral_list','app_integral_list.typeid','=','app_member.oid')
            ->where('app_member.usr',$this->userinfo->official)
            ->where('app_integral_list.type',2)
            ->orderBy('app_member.id','desc')
            ->get();
        if($member->isEmpty()){
            return api_json([],10002,'无数据');
        }else{
            $member = $member->toArray();
            foreach($member as $k=>$v){
                $member[$k]->logo = config('app.app_configs.loadhost').$v->logo;
            }
            return api_json(['info'=>$member],200,'获取成功');
        }





    }

    /**
     *签名
     */
    public function sign_do($arr)
    {
        ksort($arr);//排序
        $str = http_build_query($arr)."&key=".config('app.wx_configs.appsecret');
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

}