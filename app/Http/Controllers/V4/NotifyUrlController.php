<?php
namespace App\Http\Controllers\V4;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Member;


class NotifyUrlController extends Controller
{
    public function notifyUrl(Request $request)
    {

        $re = $_REQUEST;
        if($re == null){
            $re = file_get_contents("php://input");
        }
        $objectxml = (array)simplexml_load_string($re, 'SimpleXMLElement', LIBXML_NOCDATA);
        Log::info('notifyOrder  '. json_encode($objectxml));

        $object_res = $objectxml;
        $sign = $object_res['sign'];
        if($this->sign_do($object_res) == $sign) {//验证签名
            if($object_res['result_code'] == "SUCCESS" && $object_res['return_code'] == "SUCCESS"){//支付成功
                $order_data = DB::table('app_member_order')
                    ->where('orderid',$object_res['nonce_str'])
                    ->first();
                if($order_data->orderid !== 2){
                    DB::beginTransaction();
                    try {

                        $member_order['transaction_id'] = $object_res['transaction_id'];
                        $member_order['total_fee'] = $object_res['total_fee'];
                        $member_order['cash_fee'] = $object_res['cash_fee'];
                        $member_order['res_json'] = json_encode($object_res);
                        $member_order['pay_status'] = 2;//已支付
                        $member_order['update_time'] = today_time();
                        //更新支付状态
                        DB::table('app_member_order')
                            ->where('orderid',$object_res['nonce_str'])
                            ->update($member_order);

                        $app_member['name'] = $order_data->name;
                        $app_member['add'] = $order_data->add;
                        $app_member['mem_type'] = $order_data->mem_type;
                        $app_member['create_time'] = today_time();
                        $app_member['telephone'] = $order_data->telephone;
                        $app_member['update_time'] = $app_member['create_time'];
                        $app_member['end_time'] = date('Y-m-d',time()+31536000);
                        $app_member['oid'] = $order_data->orderid;
                        $app_member['usr'] = 990;
                        $app_member['province'] = $order_data->province;
                        $app_member['city'] = $order_data->city;
                        $app_member['total'] = 5;
                        $app_member['grant_num'] = 0;
                        $app_member['do_test'] = 0;
                        $app_member['contaminated'] = 0;
                        $app_member['is_del'] = 0;
                        $app_member['pay'] = $order_data->pay;
                        $app_member['channel'] = 2;//支付订单

                        $app_member_res = Member::updateOrCreate(['telephone' => $order_data->telephone,'oid'=>$order_data->orderid], $app_member);

                        $userinfo = DB::table('app_user')
                            ->where('phone',$order_data->telephone)
                            ->first();

                        if($userinfo->member_type<$app_member['mem_type']){
                            DB::table('app_user')
                                ->where('id',$userinfo->id)
                                ->update(['member_type'=>$app_member['mem_type']]);
                        }
//提交
                        DB::commit();
                    }catch(\Exception $exception) {
                        //事务回滚
                        DB::rollBack();
                        return api_json([],'90126','失败');
//            return back()->withErrors($exception->getMessage())->withInput();
                    }
                }

                echo '<xml>
                        <return_code><![CDATA[SUCCESS]]></return_code>
                        <return_msg><![CDATA[OK]]></return_msg>
                      </xml>';
                exit;

            }
        }else{

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
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

}