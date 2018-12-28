<?php
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-27
 * Time: 17:30
 */

namespace app\admin\validate;

use think\Validate;

class Region extends Validate
{
    //验证规则
    protected $rule = array(
        'region_name' => 'require|token',
        'region_code' => 'require',
        'region_name_en' => 'require',
        'region_shortname_en' => 'require',
    );

    // 验证提示信息
    protected $message = array(
        'region_name.require' => '地区名称不能为空',
        'region_code.require' => '地区编码不能为空',
        'region_name_en.require' => '地区英文缩写不能为空',
        'region_shortname_en.require' => '地区拼音简写不能为空',
    );

    //验证场景
    protected $scene = array(
        'add' => array('region_name', 'region_code', 'region_name_en', 'region_shortname_en'),
        'edit' => array('region_name', 'region_code', 'region_name_en', 'region_shortname_en'),
    );
}