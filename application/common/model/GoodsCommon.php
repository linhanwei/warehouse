<?php
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-28
 * Time: 10:50
 */
namespace app\common\model;

class GoodsCommon extends Base
{
    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
    }

    //输出字段处理
    public function getGoodsImageAttr($value,$data)
    {
        if(empty($data['goods_image']))
        {
            return false;
        }

        return explode(',', $data['goods_image']);
    }

    public function getBrandNameAttr($value,$data)
    {
        if(empty($data['brand_name']))
        {
            $brandInfo = model('GoodsBrand')->detail($data['id'], 'brand_name');
            $data['brand_name'] = empty($brandInfo) ? '' : $brandInfo['brand_name'];
        }

        return $data['brand_name'];
    }

    public function getCompanyNameAttr($value,$data)
    {
        if(empty($data['company_id']))
        {
            return false;
        }

        $companyInfo = model('Company')->detail($data['company_id']);

        return empty($companyInfo) ? '' : $companyInfo['name'];
    }

    //添加字段处理
    public function setGoodsImageAttr($value)
    {
        if(empty($value))
        {
            return false;
        }

        return implode(',', $value);
    }

}