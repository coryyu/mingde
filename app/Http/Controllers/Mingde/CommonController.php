<?php
namespace App\Http\Controllers\Mingde;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class CommonController extends Controller
{

    public function api_json( $data = array() , $code = "", $msg = '')
    {
        $code = empty($code)?'Success':$code;

        $backData['return_code'] = $code;
        $backData['return_msg'] = $msg;
        $backData['return_info'] = $data;

        return response()->json($backData);
    }
    /**
     *by chenyu
     * 20180817
     *
     **/
    public  function CodePlusPass($pass)
    {
        if(!empty($pass) && isset($pass)){
            $b64 = base64_encode($pass);
            $len = strlen($b64);
            $cei =  ceil(sqrt($len));
            for($i = 0; $i < $cei; $i++) {
                $line_arr[] = substr($b64, $i*$cei, $cei);
            }
            $sign = '';
            for($i = 0; $i < $cei; $i++) {
                for($j = 0; $j < $cei; $j++) {
                    $sign .= !isset($line_arr[$j][$i])?'*':$line_arr[$j][$i];
                }
            }
            return $sign;
        }
        return '';
    }
    /**
     *by chenyu
     * 20180817
     *
     **/
    public  function CodeGetPass($pass)
    {
        if(!empty($pass) && isset($pass)){
            $len = strlen($pass);
            $cei =  ceil(sqrt($len));
            for($i = 0; $i < $cei; $i++) {
                $line_arr[] = substr($pass, $i*$cei, $cei);
            }
            $sign = '';
            for($i = 0; $i < $cei; $i++) {
                for($j = 0; $j < $cei; $j++) {
                    if(!empty($line_arr[$j][$i]) || $line_arr[$j][$i] ==0){
                        $sign .= $line_arr[$j][$i]=='*'?'':$line_arr[$j][$i];
                    }
                }
            }
            return base64_decode($sign);
        }
        return '';
    }
    /**
     *排列组合
     */
    protected function getIdForName($idname, $valuename, $list) {
        $tmparr = array();
        for ($i = 0; $i < count($list); $i++) {
            $tmparr[$list[$i][$idname]] = $list[$i][$valuename];
        }
        return $tmparr;
    }
    /**
     *curl
     */
    public function curlMet($url,$method,$post_data = 0)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        } elseif ($method == 'get') {
            curl_setopt($ch, CURLOPT_HEADER, 0);
        }
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

}