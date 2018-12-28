<?php
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-27
 * Time: 17:30
 */
namespace app\admin\validate;

use think\Validate;

class Login extends Validate
{
    //验证规则
    protected $rule = array(
        'username'  =>  'require|min:5|token',
        'password' =>  'require|min:5',
    );

    // 验证提示信息
    protected $message = array(
        'username.require'  => '登录账号不能为空',
        'username.min'      => '登录账号长度至少为5位字符',
        'password.require' => '登录密码不能为空',
        'password.min' => '登录密码长度至少为5位字符',
    );

    //验证场景
    protected $scene = array(
        'login'  =>  array('username','password'),
    );
}