<?php
/**
 * 模块控制器
 */
namespace app\admin\controller;

class AdminAuthority extends Common
{
    //列表
    public function index()
    {
        $field_list = array(
            'name' => '权限名称',
            'module' => '模块',
            'controller' => '控制器',
        );

        $where = array();
        $field = input('field');
        $keyword = input('keyword');
        if(isset($field_list[$field]) && !empty($keyword))
        {
            $where[$field] = $keyword;
        }

        $list = model('AdminAuthority')->pageList($where, self::PAGE_TOTAL);

        $this->assign('field_list', $field_list);
        $this->assign('list', $list);
        return $this->fetch('index');
    }

    //添加
    public function add()
    {
        if(request()->isPost())
        {
            $validate = validate('AdminAuthority');

            if(!$validate->scene('add')->check(input())){
                $this->error($validate->getError());
            }
            else
            {
                $name = input('name');
                //$module = input('module');
                $module = 'admin';
                $controller = input('controller');
                $function = input('function');

                $AdminAuthorityModel = model('AdminAuthority');

                //验证权限名称与账号是否存在
                $count = $AdminAuthorityModel->getCount(array('name' => $name));

                if($count > 0)
                {
                    $this->error('权限名称已经存在!');
                }

                $count = $AdminAuthorityModel->getCount(array('module' => $module, 'controller' => $controller, 'function' => $function));

                if($count > 0)
                {
                    $this->error('操作权限已经存在!');
                }

                $addData['module'] = $module;
                $addData['name'] = $name;
                $addData['controller'] = $controller;
                $addData['function'] = $function;

                $result = $AdminAuthorityModel->addData($addData);

                if($result)
                {
                    $this->success('添加成功', url('admin_authority/index'));
                }
                else
                {
                    $this->error('添加失败');
                }
            }
        }
        else
        {
            return $this->fetch('add');
        }
    }

    //修改
    public function edit()
    {
        $id = input('id');

        if(empty($id))
        {
            $this->error('请选择需要修改的权限!');
        }

        $AdminAuthorityModel = model('AdminAuthority');

        //判断权限是否存在
        $authInfo = $AdminAuthorityModel->detail($id);

        if(empty($authInfo))
        {
            $this->error('选择的权限不存在!');
        }

        if(request()->isPost())
        {
            $validate = validate('AdminAuthority');

            if(!$validate->scene('edit')->check(input())){
                $this->error($validate->getError());
            }
            else
            {
                $name = input('name');
                //$module = input('module');
                $module = 'admin';
                $controller = input('controller');
                $function = input('function');

                //验证权限名称与账号是否存在
                $nameWhere['name'] = $name;
                $nameWhere['id'] = array('neq', $id);
                $count = $AdminAuthorityModel->getCount($nameWhere);

                if($count > 0)
                {
                    $this->error('权限名称已经存在!');
                }

                $authWhere['id'] = array('neq', $id);
                $authWhere['module'] = $module;
                $authWhere['controller'] = $controller;
                $authWhere['function'] = $function;
                $count = $AdminAuthorityModel->getCount($authWhere);

                if($count > 0)
                {
                    $this->error('操作权限已经存在!');
                }

                $editData['module'] = $module;
                $editData['name'] = $name;
                $editData['controller'] = $controller;
                $editData['function'] = $function;

                $result = $AdminAuthorityModel->editData(array('id'=> $id), $editData);

                if($result)
                {
                    $this->success('修改成功', url('admin_authority/index'));
                }
                else
                {
                    $this->error('修改失败');
                }
            }
        }
        else
        {
            $this->assign('info', $authInfo);
            return $this->fetch('edit');
        }
    }

    //删除
    public function del()
    {
        $id = input('id');

        if(empty($id))
        {
            $this->error('请选择需要删除的权限!');
        }

        $AdminAuthorityModel = model('AdminAuthority');

        //判断权限是否存在
        $authInfo = $AdminAuthorityModel->detail($id, 'id');

        if(empty($authInfo))
        {
            $this->error('选择的权限不存在!');
        }

        $result = $AdminAuthorityModel->delData(array('id'=>$id));

        if($result)
        {
            $this->success('删除成功!',url('admin_authority/index'));
        }
        else
        {
            $this->error('删除失败!');
        }
    }
}
