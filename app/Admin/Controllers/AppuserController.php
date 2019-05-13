<?php

namespace App\Admin\Controllers;

use App\AppUser;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class AppuserController extends Controller
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
            ->header('用户管理')
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
        $grid = new Grid(new AppUser);
        $grid->model()->where('is_del', '=', 0);

        $grid->id('Id');
        $grid->name('姓名');
        $grid->phone('Phone');
        $grid->unionid('微信id');
        $grid->position('坐标');
        $grid->position_text('坐标地址');
        $grid->avatar_url('头像')->display(function($avatar_url){
            $img = '<img src="'.$avatar_url.'" width="50px;"></img>';
            return $img;
        });
        $grid->home_sum('成员数');
        $grid->updated_at('修改时间');
        $grid->created_at('创建时间');
        $grid->last_at('最后登录时间');
        $grid->member_type('会员')->display(function($member_type){
            return $member_type==0?'非会员':'会员';
        });

        $grid->disableRowSelector();
        $grid->disableCreateButton();//禁止创建按钮

        $grid->actions(function ($actions) {
            $actions->disableEdit();//禁止编辑
            $actions->disableView();//禁止展示
            $actions->disableDelete();
            $id = $actions->getKey();
            $html = '<a href="apphome?key='.$id.'"><i class="fa fa-eye"></i></a>';
            $actions->prepend($html);
            // append一个操作
            $actions->append('<a href="appuser/del?key='.$id.'"><i class="fa fa-trash"></i></a>');
        });
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
        $show = new Show(AppUser::findOrFail($id));


        $show->id('Id');
        $show->name('Name');
        $show->phone('Phone');
        $show->password('Password');
        $show->unionid('Unionid');
        $show->position('Position');
        $show->position_text('Position text');
        $show->avatar_url('Avatar url');
        $show->home_sum('Home sum');
        $show->updated_at('Updated at');
        $show->created_at('Created at');
        $show->last_at('Last at');
        $show->is_del('Is del');
        $show->member_type('Member type');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AppUser);

        $form->text('name', 'Name');
        $form->mobile('phone', 'Phone');
        $form->password('password', 'Password');
        $form->text('unionid', 'Unionid');
        $form->text('position', 'Position');
        $form->textarea('position_text', 'Position text');
        $form->textarea('avatar_url', 'Avatar url');
        $form->switch('home_sum', 'Home sum');
        $form->datetime('last_at', 'Last at')->default(date('Y-m-d H:i:s'));
        $form->switch('is_del', 'Is del');
        $form->switch('member_type', 'Member type');

        return $form;
    }
    /**
     *标记删除
     */
    public function del($key)
    {
        echo $key;
    }
}
