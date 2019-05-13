<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
<div class="container">
    <div class="panel-heading">上传文件</div>
    <form class="form-horizontal" method="POST" action="upload" enctype="multipart/form-data">
        <?php echo e(csrf_field(), false); ?>

        <label for="file">机型</label>
        <select name="model" class="form-control">
            <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($v->model, false); ?>"><?php echo e($v->model, false); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <label for="file">选择文件</label>
        <input id="file" type="file" class="form-control" name="source" required>
        <button type="submit" class="btn btn-primary">确定</button>
    </form>
</div>

            
            </div>
        </div>
    </div>