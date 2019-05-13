<?php
namespace App\Http\Controllers\V4;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Controllers\V4\CommonController as Common;

class IntegralController extends Common
{

    /**
     *积分列表
     * 2019-1-22
     * by chenyu
     */
    public function getIntegralList(Request $request)
    {

        $search = $request->input('search');
        $page = (int) $request->input("page");//页码
        $limit = (int) $request->input("limit");
        $limit = empty($limit)?15:$limit;

        $list = DB::table('app_integral_list')
            ->select('app_integral_list.id','app_integral_config.title','app_integral_list.create_at','app_integral_list.sum','app_integral_list.plus')
            ->leftJoin('app_integral_config','app_integral_list.type','=','app_integral_config.id')
            ->where('app_integral_list.uid',$this->userinfo->id);

        if($search == 'all'){

        }elseif($search == '1'){//增加积分
            $list->where('app_integral_list.plus',1);
        }elseif($search == '2'){//消耗积分
            $list->where('app_integral_list.plus',2);
        }

        $res_list = $list->orderBy('app_integral_list.create_at','desc')
        ->offset(($page-1)*$limit)
        ->limit($limit)
        ->get();
        if (!$res_list->isEmpty()) {

            return api_json(['active'=>$this->userinfo->active,'info'=>$res_list->toArray()],200,'获取成功');

        }else{
            return api_json([],10002,'无数据');
        }



    }


}