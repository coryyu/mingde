<?php

namespace App\Admin\Controllers;

use App\AlgoItemstandard;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
//use Maatwebsite\Excel\Excel;
use Excel;

class AlgoItemstandardController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        $model = DB::table('app_model')
            ->select('model')
            ->groupBy('model')
            ->get();
        $data = $model->toArray();

        return $content
            ->header('上传标准值')
            ->body(view('admin.algoitem',['data'=>$data])->render());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AlgoItemstandard);

        $grid->id('Id');
        $grid->row('Row');
        $grid->version('Version');
        $grid->column('model');
        $grid->item_id('Item id');
        $grid->standard_r('Standard r');
        $grid->standard_g('Standard g');
        $grid->standard_b('Standard b');
        $grid->determine('Determine');
        $grid->quantify('Quantify');
        $grid->value('Value');
        $grid->srow('Srow');
        $grid->scol('Scol');
        $grid->sthreshold('Sthreshold');
        $grid->is_del('Is del');
        $grid->create_at('更新时间');
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->like('Model', 'Model');

        });

        $grid->model()->where('product_id', '=', 1);
        $grid->model()->where('is_del', '=', 0);

//        $grid->disableCreateButton();
        $grid->disableRowSelector();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();
        });

//        $grid->tools(function ($tools) {
//            $tools->append(new Upload());
//        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(AlgoItemstandard::findOrFail($id));

        $show->id('Id');
        $show->row('Row');
        $show->version('Version');
        $show->model('Model');
        $show->product_id('Product id');
        $show->item_id('Item id');
        $show->standard_r('Standard r');
        $show->standard_g('Standard g');
        $show->standard_b('Standard b');
        $show->determine('Determine');
        $show->quantify('Quantify');
        $show->value('Value');
        $show->srow('Srow');
        $show->scol('Scol');
        $show->sthreshold('Sthreshold');
        $show->is_del('Is del');
        $show->create_at('Create at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AlgoItemstandard);

        $form->text('standard_r', 'Standard r');
        $form->text('standard_g', 'Standard g');
        $form->text('standard_b', 'Standard b');
        $form->text('determine', 'Determine');
        $form->text('quantify', 'Quantify');
        $form->text('value', 'Value');
        $form->text('srow', 'Srow');
        $form->text('scol', 'Scol');
        $form->text('sthreshold', 'Sthreshold');

        $form->tools(function (Form\Tools $tools) {

            // 去掉`删除`按钮
            $tools->disableDelete();
            $tools->disableView();

        });

        $form->footer(function ($footer) {

            // 去掉`重置`按钮
            $footer->disableReset();

            // 去掉`查看`checkbox
            $footer->disableViewCheck();

            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();

            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();

        });
        $form->saving(function (Form $form) {
            $version = $form->model()->version;
            $model = $form->model()->model;
            $row = $form->model()->row;
            $Items = AlgoItemstandard::where('version', $version)
                ->where('model', $model)
                ->where('product_id', 1)
                ->where('is_del', 0)
                ->get();
            $arr_items = $Items->toArray();

            DB::beginTransaction();//开启事务
            try {
                //修改插入数据
                $version_new = $version+1;
                foreach($arr_items as $k=>$v){
                    unset($arr_items[$k]['id']);
                    $arr_items[$k]['create_at'] = today_time();
                    $arr_items[$k]['update_at']=$arr_items[$k]['create_at'];
                    $arr_items[$k]['version'] = $version_new;
                    if($v['row'] == $row){
                        $arr_items[$k]['standard_r'] = $form->standard_r;
                        $arr_items[$k]['standard_g'] = $form->standard_g;
                        $arr_items[$k]['standard_b'] = $form->standard_b;
                        $arr_items[$k]['determine'] = $form->determine;
                        $arr_items[$k]['quantify'] = $form->quantify;
                        $arr_items[$k]['value'] = $form->value;
                        $arr_items[$k]['srow'] = $form->srow;
                        $arr_items[$k]['scol'] = $form->scol;
                        $arr_items[$k]['sthreshold'] = $form->sthreshold;
                    }
                }

                AlgoItemstandard::where('version', $version)
                    ->where('model', $model)
                    ->where('product_id', 1)
                    ->update(['is_del'=>1]);

                DB::table('app_algo_itemstandard')->insert($arr_items);

                //app_table_algo
                DB::table('app_table_algo')
                    ->where('table','app_algo_itemstandard')
                    ->where('model',$model)
                    ->update(['version'=>$version_new,'update_at'=>today_time()]);

                DB::commit();
            }catch(\Exception $exception) {
                //事务回滚
                DB::rollBack();
            return back()->withErrors($exception->getMessage())->withInput();
            }
//            var_dump($form->determine);
            return redirect('/admin/algoitemstandard');
            exit;

        });
        return $form;
    }
    public function upload (Request $request)
    {

        if ($request->isMethod('POST')) { //判断是否是POST上传，应该不会有人用get吧，恩，不会的

            //在源生的php代码中是使用$_FILE来查看上传文件的属性
            //但是在laravel里面有更好的封装好的方法，就是下面这个
            //显示的属性更多
            $fileCharater = $request->file('source');


            if ($fileCharater->isValid()) { //括号里面的是必须加的哦
                //如果括号里面的不加上的话，下面的方法也无法调用的

                //获取文件的扩展名
                $ext = $fileCharater->getClientOriginalExtension();

                //获取文件的绝对路径
                $path = $fileCharater->getRealPath();

                //定义文件名
                $filename = date('Y-m-d-h-i-s').'.'.$ext;

                //存储文件。disk里面的public。总的来说，就是调用disk模块里的public配置
                Storage::disk('excel')->put($filename, file_get_contents($path));
            }
            DB::beginTransaction();//开启事务
            try {

                $model = $request->input('model');

                $table_algo = DB::table('app_table_algo')
                    ->where('table', 'app_algo_itemstandard')
                    ->where('model', $model)
                    ->first();
                $version = 0;
                if ($table_algo) {//存在 机型版本

                    $version = $table_algo->version + 1;
                    //更新版本号
                    DB::table('app_table_algo')
                        ->where('id', $table_algo->id)
                        ->update(['version' => $version, 'update_at' => today_time()]);

                    //删除原有版本
                    DB::table('app_algo_itemstandard')
                        ->where('model', $model)
                        ->where('is_del', 0)
                        ->update(['is_del' => 1, 'update_at' => today_time()]);

                } else {//不存在 更新

                    //更新版本号
                    $version = $version + 1;
                    $app_table_algo['table'] = 'app_algo_itemstandard';
                    $app_table_algo['version'] = $version;
                    $app_table_algo['update_at'] = today_time();
                    $app_table_algo['text'] = '开放' . $model;
                    $app_table_algo['model'] = $model;
                    $app_table_algo['is_del'] = 0;
                    DB::table('app_table_algo')
                        ->insert($app_table_algo);
                }


                $valeus = DB::table('app_algo_itemstandard_values')
                    ->get()
                    ->toArray();


                $filePath = 'storage/app/public/excel/' . $filename;
                $loads = Excel::load($filePath);
                $data = $loads->toArray();
                $algo = [];
                foreach ($data[0] as $key => $val) {

                    $algo[$key]['row'] = $val['id'];
                    $algo[$key]['version'] = $version;//todo
                    $algo[$key]['model'] = $model;
                    $algo[$key]['product_id'] = 1;
                    $algo[$key]['item_id'] = $valeus[$val['id'] - 1]->item_id;
                    $algo[$key]['standard_r'] = $val['r'];
                    $algo[$key]['standard_g'] = $val['g'];
                    $algo[$key]['standard_b'] = $val['b'];
                    $algo[$key]['determine'] = $valeus[$val['id'] - 1]->determine;
                    $algo[$key]['quantify'] = $valeus[$val['id'] - 1]->quantify;
                    $algo[$key]['value'] = $valeus[$val['id'] - 1]->value;
                    $algo[$key]['srow'] = $valeus[$val['id'] - 1]->srow;
                    $algo[$key]['scol'] = $valeus[$val['id'] - 1]->scol;
                    $algo[$key]['sthreshold'] = $valeus[$val['id'] - 1]->sthreshold;
                    $algo[$key]['is_del'] = 0;
                    $algo[$key]['create_at'] = today_time();
                    $algo[$key]['update_at'] = $algo[$key]['create_at'];
                }
                //插入数据
                DB::table('app_algo_itemstandard')
                    ->insert($algo);

                DB::commit();
                return redirect('/admin/algoitemstandard');exit;
            }catch(\Exception $exception) {
                //事务回滚
                DB::rollBack();
                return back()->withErrors($exception->getMessage())->withInput();
            }
        }
    }

}
