

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
                                <td style="width:33%">头像： <img src="<?php echo e($data->avatar_url, false); ?>" width="40" alt=""></td>
                                <td style="width:33%"></td>
                                <td style="width:33%">检验时间：<?php echo e($data->create_at_app, false); ?></td>
                            </tr>
                            <tr>
                                <td>序号：<?php echo e($data->id, false); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td>姓名：<?php echo e($data->name, false); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td>出生日期：<?php echo e($data->age, false); ?></td>
                            </tr>
                            <tr>
                                <td>手机号：<?php echo e($data->phone, false); ?></td>
                                <td colspan="2">性别：<?php echo e($data->sex, false); ?></td>
                            </tr>

                            <tr>
                                <td>症状：<?php echo e($data->state, false); ?></td>
                                <td>确诊疾病：--</td>
                                <td>检测原始图：

                                    <img src="<?php echo e($data->picture_path, false); ?>" width="50" alt="">
                                    <a href="<?php echo e($data->picture_path, false); ?>" target="_blank">查看原图</a>
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
                    <?php $__currentLoopData = $result; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                        <tr>
                            <td>
                                <?php echo e($v->row.'-'.$v->item, false); ?>

                            </td>

                            <td style="background-color:<?php echo e($v->determine == '正常'?'#5bdea8':'#fca0b9', false); ?>">
                                <?php echo e($v->determine, false); ?>

                            </td>
                            <td><?php echo e($v->quantify, false); ?></td>
                            <td><?php echo e($v->value, false); ?></td>
                            <td><?php echo e($v->standard_range, false); ?></td>
                            <td><?php echo e($v->diff, false); ?></td>
                            <?php if(empty($v->rgb1)): ?>
                                <td>--</td>
                            <?php else: ?>
                                <td style="background-color:rgb(<?php echo e($v->rgb1, false); ?>,<?php echo e($v->rgb2, false); ?>,<?php echo e($v->rgb3, false); ?>)"><?php echo e($v->rgb1.','.$v->rgb2.','.$v->rgb3, false); ?></td>
                            <?php endif; ?>
                            <td><?php echo e($v->unit, false); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="box-footer">
        <table class="table table-striped">
            <tbody>
            <tr>
                <td>地理位置：<?php echo e($data->area, false); ?></td>
                <td>算法库版本：<?php echo e($data->algo_version, false); ?></td>
                <td>坐标点：
                    <?php if(empty($data->positionData)): ?>
                        <?php echo e('--', false); ?>

                    <?php else: ?>
                        <div style="width:300px;border-style:solid;border-width:1px;word-wrap:break-word ;">
                            <text style=""><?php echo e($data->positionData, false); ?></text>
                        </div>
                    <?php endif; ?>
                </td>
                <td>纸尿库型号：<?php echo e($data->model, false); ?></td>
                <td>纸尿库编号：<?php echo e($data->code, false); ?></td>
                <td>APP类型：<?php echo e($data->app==1?'安卓':$data->app==2?'IOS':'小程序', false); ?></td>
            </tr>
            </tbody>
        </table>
    </div>


