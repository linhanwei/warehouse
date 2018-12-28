<?php
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-27
 * Time: 17:30
 */

namespace app\admin\validate;

use think\Validate;

class Admin extends Validate
{
    //验证规则
    protected $rule = array(
        'admin_nickname' => 'require|token',
        'admin_name' => 'require|min:6',
        'admin_password' => 'require|min:6',
        'confirm_password' => 'require|min:6|confirm:admin_password',
    );

    // 验证提示信息
    protected $message = array(
        'admin_nickname.require' => '管理员名称不能为空',
        'admin_nickname.min' => '管理员名称长度至少为6位字符',
        'admin_name.require' => '管理员账号不能为空',
        'admin_name.min' => '管理员账号长度至少为6位字符',
        'admin_password.require' => '管理员密码不能为空',
        'admin_password.min' => '管理员密码长度至少为6位字符',
        'confirm_password.require' => '确认密码不能为空',
        'confirm_password.min' => '确认密码长度至少为6位字符',
        'confirm_password.confirm' => '管理员密码与确认密码不一致',
    );

    //验证场景
    protected $scene = array(
        'add' => array('admin_nickname', 'admin_name', 'admin_password', 'confirm_password'),
        'edit' => array('admin_nickname'),
        'reset_password' => array('admin_password', 'confirm_password'),
    );
}