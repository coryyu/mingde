<?php

namespace App\Http\Middleware;

use Closure;

class PostKeyMiddleware
{
    public $key = "BCF319B377825C157A9EDBD9240BB90E";
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $post = $request->input();

        if(empty($post['sign'])){
            echo api_json([],90105,'签名参数错误');
            exit;
        }

        $sign =$post['sign'];
        unset($post['sign']);
        if($sign !== sign($post)){
            echo api_json([],90106,'签名失败');
            exit;
        }

        return $next($request);
    }
}
