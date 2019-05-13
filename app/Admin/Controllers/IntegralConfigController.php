<?php

namespace App\Admin\Controllers;

use App\IntegralConfig;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class IntegralConfigController extends Controller
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
            ->header('积分增长配置')
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
            ->header('积分增长配置')
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
            ->header('积分增长配置')
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
            ->header('积分增长配置')
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
        $grid = new Grid(new IntegralConfig);

        $grid->id('Id');
        $grid->title('Title');
        $grid->attr('Attr');
        $grid->sum('Sum');
        $grid->text('Text');
        $grid->create_at('Create at');
        $grid->update_at('Update at');

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
        $show = new Show(IntegralConfig::findOrFail($id));

        $show->id('Id');
        $show->title('Title');
        $show->attr('Attr');
        $show->sum('Sum');
        $show->text('Text');
        $show->create_at('Create at');
        $show->update_at('Update at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new IntegralConfig);

        $form->text('title', 'Title');
        $form->switch('attr', 'Attr');
        $form->decimal('sum', 'Sum');
        $form->textarea('text', 'Text');
        $form->datetime('create_at', 'Create at')->default(date('Y-m-d H:i:s'));
        $form->datetime('update_at', 'Update at')->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
