<?php
namespace App\Http\Controllers\V4;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Member;


class HelpcenterController extends Controller
{

    public function index(Request $request)
    {

        $id = $request->input('id');
        $center = DB::table('app_helpcenter')
            ->where('id',$id)
            ->first();
//        echo $center->text;exit;
        if($center){
            return view('helpcenter', ['data'=>$center->text]);
        }else{
            return view('helpcenter', ['data'=>'<h1>404</h1>']);
        }
    }

}