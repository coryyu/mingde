<?php

namespace App\Admin\Controllers;

use App\AppVersion;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class AppVersionController extends Controller
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
            ->header('App版本管理')
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
        return $content
            ->header('App版本管理')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AppVersion);

        $grid->model()->where('isdel', '=', 1);

        $grid->id('ID')->sortable();
        $grid->downurl('Downurl');
        $grid->version('Version');
        $grid->updated_at('updated_at');
        $grid->mess('Mess');
        $grid->isup('强制升级')->display(function($isup){
            return $isup==1?'强制升级':'非强制';
        });
        $grid->come('App')->display(function ($come) {
            return $come ==1? 'ios医护版' :($come==2?'ios家庭版':($come==3?'and医护版':'and家庭版') );
        });
        $grid->number('Number');

        $grid->paginate(10);
        $grid->disableFilter();
        $grid->disableRowSelector();
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
        $show = new Show(AppVersion::findOrFail($id));

        $show->id('Id');
        $show->downurl('Downurl');
        $show->version('Version');
        $show->updatetime('Updatetime');
        $show->mess('Mess');
        $show->isup('Isup');
        $show->come('Come');
        $show->isdel('Isdel');
        $show->number('Number');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AppVersion);
        $come=[
            1=>'ios医护版',
            2=>'ios家庭版',
            3=>'and医护版',
            4=>'and家庭版',
        ];
        $form->select('come', 'App')->options($come)->rules('required',['不能空']);
        $form->text('version', 'Version')->rules('required',['不能空']);
        $states = [
            1  => [ 'text' => '打开', 'color' => 'success'],
            2 => ['text' => '关闭', 'color' => 'danger'],
        ];

//        $form->switch($column[, $label])->states($states);
        $form->switch('isup', '强制升级？')->states($states)->rules('required',['不能空']);
        $form->text('mess')->default('有新的版本？是否升级？')->rules('required',['不能空']);
        $form->text('number', 'Number')->rules('required',['不能空']);
        $form->hidden('isdel')->default(1)->rules('required',['不能空']);

        $form->file('安装包')->uniqueName();

        $form->saved(function (Form $form) {

            $resid =  $form->model()->id;
            $flights = \App\AppVersion::where('isdel', 1)
                ->where('come',$form->come)
                ->where('id','!=',$resid)
                ->update(['isdel' => 2]);
             });


        return $form;
    }
    /**
     *
     */
    public function upData(Request $request)
    {
        var_dump($request->post());exit;
    }
}
