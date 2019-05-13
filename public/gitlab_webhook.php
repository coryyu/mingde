<?php
/* gitlab deploy webhook */

// 自定义字串掩码 用于验证
$access_token = 'fd2883225f1569f8595705420d9c29ad';
// 接受的ip数组，也就是允许哪些IP访问这个文件 这里是 GitLab 服务器IP
$access_ip = array('192.168.1.180','192.168.1.98');

// 项目目录
//$www_file='/home/server/pzyanxuxuserver/';
$www_file=__DIR__.'/';

// 更新脚本
$release_deploy=__DIR__.'/deploy.sh release';
$develop_deploy=__DIR__.'/deploy.sh';

//echo $release_deploy;
//echo shell_exec('pwd');
//echo shell_exec($release_deploy);
//echo $www_file.'================ Update Start ===============';
// get user token and ip address 
$client_ip=$_SERVER['REMOTE_ADDR'];
$client_token=$_SERVER['HTTP_X_GITLAB_TOKEN'];

// create open log 
$fs = fopen($www_file.'webhook.log', 'a') or exit("Unable to open file!");


//fwrite($fs, '================ Update Start ==============='.json_encode($_SERVER).PHP_EOL);

// 脚本执行用户
$who=exec('whoami');
fwrite($fs, $who.'================ Update Start ==============='.PHP_EOL);
fwrite($fs, 'Request on ['.date("Y-m-d H:i:s").'] from ['.$client_ip.']'.PHP_EOL);

// 验证Token 有错就写进日志并退出
/*
if ($client_token != $access_token)
{
    echo "error 403";
    fwrite($fs, "Invalid token [{$client_token}]".PHP_EOL);
    exit(0);
}
 */

// 验证ip地址
/*
if ( ! in_array($client_ip, $access_ip))
{
    echo "error 503";
    fwrite($fs, "Invalid ip [{$client_ip}]".PHP_EOL);
    exit(0);
}
*/

// 获取请求端发送来的信息，具体格式参见 GitLab 的文档
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// get branch 
$branch = $data["ref"];
fwrite($fs, '======================================================================='.PHP_EOL);

// 调试用，把传送过来的信息写进log
// fwrite($fs, 'DATA: '.print_r($data, true).PHP_EOL);

// branch filter

fwrite($fs, 'BRANCH: '.print_r($branch, true).PHP_EOL);
fwrite($fs, '======================================================================='.PHP_EOL);

//echo $release_deploy;
echo shell_exec($release_deploy);
echo 'end';exit;
//echo exec($release_deploy);

if ($branch === 'refs/heads/master')
{
    fwrite($fs, "HHHHHHHHHHHHHH $release_deploy".PHP_EOL);
    exec($release_deploy);
}
elseif ($branch === 'refs/heads/develop')
{
    exec($develop_deploy);
}
$fs and fclose($fs);

?>
