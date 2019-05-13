<?php

namespace App\Admin\Controllers;

use App\Kouxrecord;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class KouxrecordController extends Controller
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
        return $content
            ->header('Create')
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
        $grid = new Grid(new Kouxrecord);

        $grid->id('Id');
        $grid->home_id('Home id');
        $grid->phone('Phone');
        $grid->name('Name');
        $grid->birth_time('Birth time');
        $grid->sex('Sex');
        $grid->connect_time('Connect time');
        $grid->tx_time('Tx time');
        $grid->tx_type('Tx type');
        $grid->system('System');
        $grid->xx_version('Xx version');
        $grid->app_version('App version');
        $grid->position('Position');
        $grid->flag('Flag');

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
        $show = new Show(Kouxrecord::findOrFail($id));

        $show->id('Id');
        $show->home_id('Home id');
        $show->phone('Phone');
        $show->name('Name');
        $show->birth_time('Birth time');
        $show->sex('Sex');
        $show->connect_time('Connect time');
        $show->tx_time('Tx time');
        $show->tx_type('Tx type');
        $show->system('System');
        $show->xx_version('Xx version');
        $show->app_version('App version');
        $show->position('Position');
        $show->flag('Flag');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Kouxrecord);

        $form->number('home_id', 'Home id');
        $form->mobile('phone', 'Phone');
        $form->text('name', 'Name');
        $form->date('birth_time', 'Birth time')->default(date('Y-m-d'));
        $form->switch('sex', 'Sex');
        $form->datetime('connect_time', 'Connect time')->default(date('Y-m-d H:i:s'));
        $form->datetime('tx_time', 'Tx time')->default(date('Y-m-d H:i:s'));
        $form->text('tx_type', 'Tx type');
        $form->switch('system', 'System');
        $form->text('xx_version', 'Xx version');
        $form->text('app_version', 'App version');
        $form->text('position', 'Position');
        $form->switch('flag', 'Flag')->default(1);

        return $form;
    }
}
