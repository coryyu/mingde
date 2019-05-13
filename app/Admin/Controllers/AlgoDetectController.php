<?php

namespace App\Admin\Controllers;

use App\AlgoDetect;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class AlgoDetectController extends Controller
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
        $grid = new Grid(new AlgoDetect);

        $grid->id('Id');
        $grid->version('Version');
        $grid->product_id('Product id');
        $grid->category('Category');
        $grid->type('Type');
        $grid->description('Description');
        $grid->item_id1('Item id1');
        $grid->item_id2('Item id2');
        $grid->item_id3('Item id3');
        $grid->item_id4('Item id4');
        $grid->item_id5('Item id5');
        $grid->item_id6('Item id6');
        $grid->item_id7('Item id7');
        $grid->item_id8('Item id8');
        $grid->item_id9('Item id9');
        $grid->item_id10('Item id10');
        $grid->item_id11('Item id11');
        $grid->item_id12('Item id12');
        $grid->item_id13('Item id13');
        $grid->item_id14('Item id14');
        $grid->is_del('Is del');

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
        $show = new Show(AlgoDetect::findOrFail($id));

        $show->id('Id');
        $show->version('Version');
        $show->product_id('Product id');
        $show->category('Category');
        $show->type('Type');
        $show->description('Description');
        $show->item_id1('Item id1');
        $show->item_id2('Item id2');
        $show->item_id3('Item id3');
        $show->item_id4('Item id4');
        $show->item_id5('Item id5');
        $show->item_id6('Item id6');
        $show->item_id7('Item id7');
        $show->item_id8('Item id8');
        $show->item_id9('Item id9');
        $show->item_id10('Item id10');
        $show->item_id11('Item id11');
        $show->item_id12('Item id12');
        $show->item_id13('Item id13');
        $show->item_id14('Item id14');
        $show->is_del('Is del');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AlgoDetect);

        $form->text('version', 'Version');
        $form->number('product_id', 'Product id');
        $form->text('category', 'Category');
        $form->text('type', 'Type');
        $form->textarea('description', 'Description');
        $form->number('item_id1', 'Item id1');
        $form->number('item_id2', 'Item id2');
        $form->number('item_id3', 'Item id3');
        $form->number('item_id4', 'Item id4');
        $form->number('item_id5', 'Item id5');
        $form->number('item_id6', 'Item id6');
        $form->number('item_id7', 'Item id7');
        $form->number('item_id8', 'Item id8');
        $form->number('item_id9', 'Item id9');
        $form->number('item_id10', 'Item id10');
        $form->number('item_id11', 'Item id11');
        $form->number('item_id12', 'Item id12');
        $form->number('item_id13', 'Item id13');
        $form->number('item_id14', 'Item id14');
        $form->switch('is_del', 'Is del');

        return $form;
    }
}
