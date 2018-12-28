<?php
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-27
 * Time: 17:30
 */

namespace app\admin\validate;

use think\Validate;

class Goods extends Validate
{
    //验证规则
    protected $rule = array(
        'goods_name' => 'require|token',
        'brand_id' => 'require',
        'company_id' => 'require',
        'goods_image' => 'require',
        'goods_body' => 'require',
    );

    // 验证提示信息
    protected $message = array(
        'goods_name.require' => '商品名称不能为空',
        'brand_id.require' => '请选择品牌',
        'company_id.require' => '请选择企业',
        'goods_image.require' => '请选择商品正面图片',
        'goods_body.require' => '商品详情不能为空',
    );

    //验证场景
    protected $scene = array(
        'add' => array('goods_name', 'brand_id', 'company_id', 'goods_image', 'goods_body'),
        'edit' => array('goods_name', 'brand_id', 'company_id', 'goods_image', 'goods_body'),
    );
}