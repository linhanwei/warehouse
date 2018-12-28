<?php
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-27
 * Time: 17:30
 */

namespace app\admin\validate;

use think\Validate;

class GoodsAttribute extends Validate
{
    //验证规则
    protected $rule = array(
        'attr_name' => 'require|token',
        'attr_sort' => 'require|integer',
        'attr_value_name' => 'require',
    );

    // 验证提示信息
    protected $message = array(
        'attr_name.require' => '属性名称不能为空',
        'attr_value_name.require' => '属性值不能为空',
        'attr_sort.require' => '排序不能为空',
        'attr_sort.integer' => '排序必须为数字',
    );

    //验证场景
    protected $scene = array(
        'add' => array('attr_name', 'attr_sort', 'attr_value_name'),
        'edit' => array('attr_name', 'attr_sort', 'attr_value_name'),
    );
}