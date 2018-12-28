<?php
/**
 * 登录控制器
 */
namespace app\admin\controller;

class Login extends Base
{

    //如果没有就跳转到登录页面
    protected function _initialize()
    {
        parent::_initialize();
    }

    //登录页面
    public function index()
    {
        return $this->fetch('index');
    }

    //登录
    public function login()
    {
        $validate = validate('Login');

        if(!$validate->scene('login')->check(input())){
            $this->error($validate->getError());
        }
        else
        {
            $username = input('username');
            $password = input('password');

            $adminModel = model('Admin');
            $adminInfo = $adminModel->getInfo(array('admin_name'=>$username));

            $adminPassword = make_password($password, $adminInfo['admin_salt']);
            if($adminInfo['admin_password'] != $adminPassword)
            {
                $this->error('密码不正确,请重新输入!');
            }

            //保存session
            session('adminInfo', $adminInfo);
            session('admin_id', $adminInfo['admin_id']);
            session('admin_gid', $adminInfo['admin_gid']);

            //修改数据
            $editData['admin_login_time'] = time();
            $editData['admin_login_num'] = $adminInfo['admin_login_num'] + 1;
            $editData['admin_login_ip'] = request()->ip();
            $adminModel->editData(array('admin_id' => $adminInfo['admin_id']), $editData);

            $this->success('登录成功', url('index/index'));
        }
    }

    //退出
    public function logout()
    {
        //保存session
        session('adminInfo', null);
        session('admin_id', null);
        session('admin_auth_list', null);

        $this->redirect(url('login/index'));
    }

}
