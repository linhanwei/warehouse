<?php
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-27
 * Time: 17:30
 */
namespace app\admin\validate;

use think\Validate;

class LabelCode extends Validate
{
    //验证规则
    protected $rule = array(
        'company_id'  =>  'require|token',
        'number' =>  'require',
    );

    // 验证提示信息
    protected $message = array(
        'company_id.require'  => '公司不能为空',
        'number.require'  => '数量不能为空',
    );

    //验证场景
    protected $scene = array(
        'add'  =>  array('company_id','number'),
        'export'  =>  array('company_id'),
    );
}