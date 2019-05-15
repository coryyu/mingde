<?php
namespace App\Http\Controllers\Mingde;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\ClassProduct;
use Illuminate\Support\Facades\Storage;

class OrderController extends CommonController
{

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