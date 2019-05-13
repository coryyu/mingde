<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
<div class="container">
    <form class="form-horizontal" method="POST" action="upload" enctype="multipart/form-data">
        {{ csrf_field() }}
        <label for="file">机型</label>
        <select name="model" class="form-control">
            @foreach ($data as $v)
                <option value="{{$v->model}}">{{$v->model}}</option>
            @endforeach
        </select>
        <label for="file">选择文件</label>
        <input id="file" type="file" class="form-control" name="source" required>
        <button type="submit" class="btn btn-primary">确定</button>
    </form>
</div>


            </div>
        </div>
    </div>