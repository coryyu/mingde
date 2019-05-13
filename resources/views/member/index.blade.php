<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0"/>
    <title>验嘘嘘会员卡办理中心</title>
    <style type="text/css">
    </style>

</head>

<body>
</body>
</html>

<script type="text/javascript">
    var id = "{{ $data['id'] }}";
//    console.log(id);
    //        console.log(id);
    //    console.log("https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx73e38c7c41d20b2a&redirect_uri=http%3A%2F%2Fapi.yanxuxu.cn%2FV3%2FMember%2Ffrom%2F"+id+"&response_type=code&scope=snsapi_base&state=123#wechat_redirect");
    window.location.href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx73e38c7c41d20b2a&redirect_uri=http%3A%2F%2Fyanxuxuapi.yanxuxu.cn%3A9980%2Fapi%2Fv4%2Fofficialtoken%2Findex%3Ftoken%"+id+"&response_type=code&scope=snsapi_base&state=123#wechat_redirect";
</script>
