<?php
namespace App\Http\Controllers\Mingde;

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
                $order_data = DB::table('sch_classorder')
                    ->where('orders',$object_res['nonce_str'])
                    ->first();
                if($order_data->pay_status == 0){
                    DB::beginTransaction();
                    try {

                        $class_order['pay_status'] =1;
                        $class_order['pay_time'] = today_time();
                        $class_order['transaction_id'] = $objectxml['transaction_id'];
                        $class_order['total_fee'] = $objectxml['total_fee'];
                        $class_order['cash_fee'] = $objectxml['cash_fee'];
                        $class_order['res_json'] = json_encode($objectxml);
                        //更新支付状态
                        DB::table('sch_classorder')
                            ->where('orders',$object_res['nonce_str'])
                            ->update($class_order);

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
        $str = http_build_query($arr)."&key=25f92f178ec71ccfde62450282f141f4";
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