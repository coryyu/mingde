<?php

namespace App\Http\Middleware;

use Closure;

class ApiTokenMiddleware
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
            return api_json([],90105,'签名参数错误');
        }
        $sign =$post['sign'];
        unset($post['sign']);
        if($sign !== sign($post)){
            return api_json([],90106,'签名失败');
        }
        $token = $request->input("token");

        return $next($request);
    }
}
