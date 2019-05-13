<?php
namespace App\Http\Controllers\V4;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;
use App\Http\Controllers\V4\CommonController as Common;
use App\AppToDoc;

class DocterController extends Common
{

    /**
     *获取医生列表
     * 2019-1-19
     * by chenyu
     */
        public function getDocterList(Request $request)
    {
        $page = (int) $request->input("page");//页码
        $search = $request->input("search");//搜索
        $limit = (int) $request->input("limit");//页码
        $limit = empty($limit)?15:$limit;


        $follow = $request->input('follow');
        if($follow){//关注
            $doc_info = DB::table('doc_user')
                ->select('doc_user.id','doc_user.name as doc_name','doc_user.follow','doc_user.avatar','doc_titles.title','admin_city.city_name','doc_hospital.title as hos_name','doc_department.title as dep_name','app_to_doc.status')
                ->leftJoin('doc_hospital','doc_user.hospital_id','=','doc_hospital.id')
                ->leftJoin('admin_city','doc_hospital.citycode','=','admin_city.city_code')
                ->leftJoin('doc_department','doc_user.department_id','=','doc_department.id')
                ->leftJoin('doc_titles','doc_user.titles_id','=','doc_titles.id')
                ->join('app_to_doc','doc_user.id','=','app_to_doc.docid');
            if(!empty($search)){
                $doc_info->where('doc_user.name','like','like','%'.$search.'%');
            }

            $docs = $doc_info->where('doc_user.is_recommend',0)
                ->where('doc_user.e',0)
//                ->where('doc_user.is_check',1)
                ->where('app_to_doc.uid',$this->userinfo->id)
                ->where('app_to_doc.status',1)
                ->orderBy('doc_user.follow','desc')
                ->offset(($page-1)*$limit)
                ->limit($limit)
                ->get();
            $count=0;

        }else{//全部
            //关联临时表
            DB::connection()->enableQueryLog();#开启执行日志
            $to_u_sql = 'select uid,docid,status from app_to_doc where uid='.$this->userinfo->id;

            $doc_info = DB::table('doc_user')
                ->select('doc_user.id','doc_user.name as doc_name','doc_user.follow','doc_titles.title','doc_user.avatar','admin_city.city_name','doc_hospital.title as hos_name','doc_department.title as dep_name','c.status')
                ->leftJoin('doc_hospital','doc_user.hospital_id','=','doc_hospital.id')
                ->leftJoin('admin_city','doc_hospital.citycode','=','admin_city.city_code')
                ->leftJoin('doc_department','doc_user.department_id','=','doc_department.id')
                ->leftJoin('doc_titles','doc_user.titles_id','=','doc_titles.id')
                ->leftJoin(DB::raw('('.$to_u_sql.') c '),'doc_user.id','=','c.docid');
            if(!empty($search)){
                $doc_info->where('doc_user.name','like','like','%'.$search.'%');
            }
            $docs = $doc_info->where('doc_user.is_recommend',0)
                ->where('doc_user.is_del',0)
//                ->where('doc_user.is_check',1)
                ->orderBy('doc_user.follow','desc')
                ->orderBy('doc_user.id','desc')
                ->offset(($page-1)*$limit)
                ->limit($limit)
                ->get();

            $count = DB::table('doc_user')
                ->where('doc_user.is_recommend',0)
                ->where('doc_user.is_del',0)
//                ->where('doc_user.is_check',1)
                ->orderBy('doc_user.follow','desc')
                ->orderBy('doc_user.id','desc')
                ->count();
        }
        if (!$docs->isEmpty()) {
            foreach($docs->toArray() as $k=>$v){
                if(!$v->status){
                    $v->status = 0;
                }
                $res[] = $v;
            }
            return api_json(['info'=>$res,'count'=>$count],200,'获取成功');

        }else{
            return api_json([],10002,'无数据');
        }
    }

    /**
     *关注医生
     * 2019-1-19
     * by chenyu
     **/
    public function followDocter(Request $request)
    {
        $doc_id = $request->input('doc_id');
        $type = $request->input('type');//1 关注   2 取消关注

        $doc_info = DB::table('doc_user')
            ->select('id')
            ->where('is_del',0)
            ->first();
        if(empty($doc_id) || !$doc_info){
            return api_json([],90132,'医生id不能为空');
        }


        $data['uid']= $this->userinfo->id;
        $data['docid']= $doc_id;
        if($type == 1){
            $data['status']= 1;
        }else{
            $data['status']= 0;
        }


        DB::beginTransaction();//开启事务
        try {
            AppToDoc::updateOrCreate(['uid' => $this->userinfo->id,'docid'=>$doc_id], $data);
            $docuser =DB::table('doc_user')
                ->where('id',$doc_id);
            if($type == 1){
                $docuser->increment('follow');
            }else{
                $docuser->decrement('follow');
            }
            DB::commit();
            return api_json([],200,'成功');
        }catch(\Exception $exception) {
            //事务回滚
            DB::rollBack();
            return api_json([],90133,'失败');
//            return back()->withErrors($exception->getMessage())->withInput();
        }



    }

}