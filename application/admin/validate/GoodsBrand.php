<?php
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-27
 * Time: 17:30
 */

namespace app\admin\validate;

use think\Validate;

class GoodsBrand extends Validate
{
    //验证规则
    protected $rule = array(
        'brand_name' => 'require|token',
    );

    // 验证提示信息
    protected $message = array(
        'brand_name.require' => '品牌名称不能为空',
    );

    //验证场景
    protected $scene = array(
        'add' => array('brand_name'),
        'edit' => array('brand_name'),
    );
}