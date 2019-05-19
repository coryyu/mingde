<?php
namespace App\Http\Controllers\Mingde;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\ClassProduct;

class IndexController extends CommonController
{

    /**
     *首页推荐
     */
    public function indexRecommend()
    {
        //最上方两个推荐
        $pro = DB::table('sch_classrecommend')
            ->select('sch_classproduct.id','sch_classproduct.title','sch_classproduct.image1')
            ->leftJoin('sch_classproduct','sch_classproduct.number','=','sch_classrecommend.number')
            ->where('sch_classproduct.is_del',0)
            ->orderBy('sch_classrecommend.sort','desc')
            ->limit(2)
            ->get();
        foreach($pro as $k=>$v){
            $pro[$k]->image1 = config('app.app_configs.loadhost').$v->image1;
        }

        //研学推荐
        $yanxue = DB::table('sch_classyanxurecommend')
            ->select('sch_classproduct.id','sch_classproduct.title','sch_classproduct.image1')
            ->leftJoin('sch_classproduct','sch_classproduct.number','=','sch_classproduct.number')
            ->where('sch_classproduct.is_del',0)
            ->orderBy('sch_classyanxurecommend.sort','desc')
            ->limit(6)
            ->get();
        foreach($yanxue as $k=>$v){
            $yanxue[$k]->image1 = config('app.app_configs.loadhost').$v->image1;
        }
        $res['tuijian'] = $pro;
        $res['yanxue'] = $yanxue;

        return $this->api_json($res,200,'成功');
    }
    /**
     *研学推荐
     */
    public function indexYanxueRec()
    {
        $pro = DB::table('sch_classproduct as pro')
            ->select('pro.id','pro.title','pro.image1')
            ->where('is_recommend',0)
            ->orderBy('sort','desc')
            ->limit(6)
            ->get()->toArray();
        foreach ($pro as $k=>$v) {
            $pro[$k]->image1 = config('app.app_configs.loadhost').$v->image1;
        }
        return $this->api_json($pro,200,'成功');
    }

    /**
     *商品详情
     */
    public function indexDetail(Request $request)
    {
        $id = $request->input('id');

        $pro = ClassProduct::select('title','title_fit','price','price','is_onoff','image1','image2','image3','text_item','text_introduce','text_arrange','fit','day','start_time','city','clothing','gradeup','gradedo','is_pay','is_sign','school')
            ->where('id',$id)->first();
        $grade = DB::table('sch_classgrade')
            ->whereBetween('id', [$pro->gradeup, $pro->gradedo])
            ->get()->toArray();
        foreach($grade as $k=>$v){
            $grades[]=$v->name;
        }
        $pro->grade = $grades;
        $image['image1'] = config('app.app_configs.loadhost').$pro->image1;
        $image['image2'] = config('app.app_configs.loadhost').$pro->image2;
        $image['image3'] = config('app.app_configs.loadhost').$pro->image3;
        $pro->imagemini = config('app.app_configs.loadhost').$pro->image1;
        $pro['image'] = $image;
        $school = explode(',',$pro->school);
        $pro->school = $school;
        return $this->api_json($pro->toarray(),200,'成功');
    }


    /**
     *首页加载
     */
    public function getHomePage(Request $request)
    {

        $page = (int) $request->input("page");//页码
        $limit = 15;


        $lists = DB::table('sch_classproduct')
            ->select('sch_classproduct.id','sch_classproduct.title','sch_classproduct.title_fit','sch_classproduct.image1')
            ->where('is_del',0)
            ->where('is_show',0)
            ->where('is_recommend',1)
            ->orderBy('sort','desc')
            ->orderBy('created_at','desc')
            ->offset(($page-1)*$limit)
            ->limit($limit)
            ->get();

        if($lists->isEmpty()){
            $rearray = [];
        }else{
            $rearray= $lists->toArray();
            foreach ($lists as $k=>$v) {
                $rearray[$k] = $v;
                $rearray[$k]->image1 = config('app.app_configs.loadhost').$v->image1;
            }
        }
        return $this->api_json($rearray,200,'成功');
    }

    /**
     *搜索列表
     */
    public function search(Request $request){

        $page = (int) $request->input("page");//页码
        $limit = 15;
        $title = $request->input('title');
        $city = $request->input('city');


        $pro = DB::table('sch_classproduct')
            ->select('sch_classproduct.id','sch_classproduct.title','sch_classproduct.title_fit','sch_classproduct.image1');
        if(!empty($city)){
            $pro->where('city','like','%'.$city.'%');
        }
        if(!empty($title)){
            $pro->where('title','like','%'.$title.'%');
        }

        $lists = $pro->where('is_del',0)
            ->where('is_show',0)
            ->orderBy('sort','desc')
            ->orderBy('created_at','desc')
            ->offset(($page-1)*$limit)
            ->limit($limit)
            ->get();

        if($lists->isEmpty()){
            $rearray = [];
        }else{
            $rearray= $lists->toArray();
            foreach ($lists as $k=>$v) {
                $rearray[$k] = $v;
                $rearray[$k]->image1 = config('app.app_configs.loadhost').$v->image1;
            }
        }
        return $this->api_json($rearray,200,'成功');
    }

    /**
     *城市推荐
     */
    public function getCityList()
    {

        $citys = DB::table('sch_classcity')
            ->select('id','name','type')
            ->where('is_del',0)
            ->orderBy('sort','desc')
            ->orderBy('created_at','desc')
            ->get()->toArray();

        $city=[];

        foreach($citys as $k=>$v){
            if($v->type ==1 ){//国内城市
                $city['guonei'][] = $v;
            }elseif($v->type ==2) {//国际城市
                $city['guoji'][] = $v;
            }

        }
        return $this->api_json($city,200,'成功');
    }
    /**
     *获取协议
     */
    public function getAgreement(Request $request)
    {
        $agr = DB::table('sch_classagreement')
            ->first();

        $text = str_replace("{{username}}",$this->userinfo->id,$agr->text);
        return $this->api_json($text,200,'成功');
    }
}