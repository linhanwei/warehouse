<?php
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-27
 * Time: 17:30
 */

namespace app\admin\validate;

use think\Validate;

class AdminAuthority extends Validate
{
    //验证规则
    protected $rule = array(
        'name' => 'require|token',
        //'module' => 'require',
        'controller' => 'require',
        'function' => 'require',
    );

    // 验证提示信息
    protected $message = array(
        'name.require' => '权限名称不能为空',
       // 'module.require' => '模块不能为空',
        'controller.require' => '控制器不能为空',
        'function.require' => '操作方法不能为空',
    );

    //验证场景
    protected $scene = array(
        'add' => array('name', 'controller', 'function'),
        'edit' => array('name', 'controller', 'function'),
    );
}