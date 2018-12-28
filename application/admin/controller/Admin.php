<?php
/***
 * 管理员控制器
 */
namespace app\admin\controller;

class Admin extends Common
{
    //列表
    public function index()
    {
        $field_list = array(
            'admin_nickname' => '管理员名称',
            'admin_name' => '管理员账号',
        );

        $where = array();
        $field = input('field');
        $keyword = input('keyword');
        if(isset($field_list[$field]) && !empty($keyword))
        {
            $where[$field] = $keyword;
        }

        $list = model('Admin')->pageList($where, self::PAGE_TOTAL);

        $this->assign('field_list', $field_list);
        $this->assign('list', $list);
        return $this->fetch('index');
    }

    //添加
    public function add()
    {
        if(request()->isPost())
        {
            $validate = validate('Admin');

            if(!$validate->scene('add')->check(input())){
                $this->error($validate->getError());
            }
            else
            {
                $username = input('admin_nickname');
                $account = input('admin_name');
                $password = input('admin_password');

                $adminModel = model('Admin');

                //验证管理员名称与账号是否存在
                $adminCount = $adminModel->getCount(array('admin_nickname' => $username));

                if($adminCount > 0)
                {
                    $this->error('管理员名称已经存在!');
                }

                $adminCount = $adminModel->getCount(array('admin_name' => $account));

                if($adminCount > 0)
                {
                    $this->error('管理员账号已经存在!');
                }

                //获取密码盐
                $admin_salt = \Util\StringSelf::randString();

                $addData['admin_nickname'] = $username;
                $addData['admin_name'] = $account;
                $addData['admin_password'] = make_password($password, $admin_salt);
                $addData['admin_is_super'] = '0';
                $addData['admin_salt'] = $admin_salt;

                $result = $adminModel->addData($addData);

                if($result)
                {
                    $this->success('添加成功', url('admin/index'));
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
        $admin_id = input('admin_id');

        if(empty($admin_id))
        {
            $this->error('请选择需要修改的管理员!');
        }

        $adminModel = model('Admin');

        //判断管理员是否存在
        $adminInfo = $adminModel->detail($admin_id);

        if(empty($adminInfo))
        {
            $this->error('选择的管理员不存在!');
        }

        if($adminInfo['admin_is_super'] == 1)
        {
            $this->error('超级管理员不能修改!');
        }

        if(request()->isPost())
        {
            $validate = validate('Admin');

            if(!$validate->scene('edit')->check(input())){
                $this->error($validate->getError());
            }
            else
            {
                $username = input('admin_nickname');
                //$account = input('admin_name');
                $password = input('admin_password');

                //验证管理员名称与账号是否存在
                $countWhere['admin_id'] = array('neq', $admin_id);
                $countWhere['admin_nickname'] = $username;
                $adminCount = $adminModel->getCount($countWhere);

                if($adminCount > 0)
                {
                    $this->error('管理员名称已经存在!');
                }

                /*
                $adminCount = $adminModel->getCount(array('admin_name' => $account));

                if($adminCount > 0)
                {
                    $this->error('管理员账号已经存在!');
                }
                */

                $editData['admin_nickname'] = $username;
                //$editData['admin_name'] = $account;

                //如果密码不为空时,验证与重设密码
                if(!empty($password))
                {
                    if(!$validate->scene('reset_password')->check(input())){
                        $this->error($validate->getError());
                    }
                    else
                    {
                        //获取密码盐
                        $String = new \Util\StringSelf();
                        $admin_salt = $String->randString();

                        $editData['admin_password'] = make_password($password, $admin_salt);
                        $editData['admin_salt'] = $admin_salt;
                        //$editData['admin_is_super'] = '0';
                    }
                }

                $result = $adminModel->editData(array('admin_id'=> $admin_id), $editData);

                if($result)
                {
                    $this->success('修改成功', url('admin/index'));
                }
                else
                {
                    $this->error('修改失败');
                }
            }
        }
        else
        {
            $this->assign('admin_info', $adminInfo);
            return $this->fetch('edit');
        }
    }

    //删除
    public function del()
    {
        $admin_id = input('admin_id');

        if(empty($admin_id))
        {
            $this->error('请选择需要删除的管理员!');
        }

        $adminModel = model('Admin');

        //判断管理员是否存在
        $adminInfo = $adminModel->detail($admin_id, 'admin_is_super');

        if(empty($adminInfo))
        {
            $this->error('选择的管理员不存在!');
        }

        if($adminInfo['admin_is_super'] == 1)
        {
            $this->error('超级管理员不能删除!');
        }

        $result = $adminModel->delData(array('admin_id'=>$admin_id));

        if($result)
        {
            $this->success('删除成功!',url('admin/index'));
        }
        else
        {
            $this->error('删除失败!');
        }
    }
}
