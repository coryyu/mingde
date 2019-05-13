

    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">检测结果</h3>
                </div>
                <div class="form-horizontal">
                    <div class="box-body">
                        <table class="table">
                            <tbody>
                            <tr>
                                <td style="text-align:center; font-size:18px; font-weight: bold; border:none" colspan="3">检验结果</td>
                            </tr>
                            <tr>
                                <td style="width:33%">头像： <img src="{{$data->avatar_url}}" width="40" alt=""></td>
                                <td style="width:33%"></td>
                                <td style="width:33%">检验时间：{{$data->create_at_app}}</td>
                            </tr>
                            <tr>
                                <td>序号：{{$data->id}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td>姓名：{{$data->name}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td>出生日期：{{$data->age}}</td>
                            </tr>
                            <tr>
                                <td>手机号：{{$data->phone}}</td>
                                <td colspan="2">性别：{{$data->sex}}</td>
                            </tr>

                            <tr>
                                <td>症状：{{$data->state}}</td>
                                <td>确诊疾病：--</td>
                                <td>检测原始图：

                                    <img src="{{$data->picture_path}}" width="50" alt="">
                                    <a href="{{$data->picture_path}}" target="_blank">查看原图</a>
                                    <a href="" target="_blank">检测识别标点</a>

                                </td>
                            </tr>

                            <tr>
                                <td>医院：--</td>
                                <td>科室：--</td>
                                <td>医生：--</td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <table class="table table-striped">
                    <thead>
                    <tr>

                        <th>项目</th>
                        <th>正/异常</th>
                        <th>实际值</th>
                        <th>显示值</th>
                        <th>参考值</th>
                        <th>颜色差</th>
                        <th>RGB信息</th>
                        <th>单位</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($result as $v)

                        <tr>
                            <td>
                                {{$v->row.'-'.$v->item}}
                            </td>

                            <td style="background-color:{{$v->determine == '正常'?'#5bdea8':'#fca0b9'}}">
                                {{$v->determine}}
                            </td>
                            <td>{{$v->quantify}}</td>
                            <td>{{$v->value}}</td>
                            <td>{{$v->standard_range}}</td>
                            <td>{{$v->diff}}</td>
                            @if (empty($v->rgb1))
                                <td>--</td>
                            @else
                                <td style="background-color:rgb({{ $v->rgb1}},{{$v->rgb2}},{{$v->rgb3}})">{{ $v->rgb1.','.$v->rgb2.','.$v->rgb3}}</td>
                            @endif
                            <td>{{$v->unit}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="box-footer">
        <table class="table table-striped">
            <tbody>
            <tr>
                <td>地理位置：{{$data->area}}</td>
                <td>算法库版本：{{$data->algo_version}}</td>
                <td>坐标点：
                    @if (empty($data->positionData))
                        {{'--'}}
                    @else
                        <div style="width:300px;border-style:solid;border-width:1px;word-wrap:break-word ;">
                            <text style="">{{$data->positionData}}</text>
                        </div>
                    @endif
                </td>
                <td>纸尿库型号：{{$data->model}}</td>
                <td>纸尿库编号：{{$data->code}}</td>
                <td>APP类型：{{$data->app==1?'安卓':$data->app==2?'IOS':'小程序'}}</td>
            </tr>
            </tbody>
        </table>
    </div>


