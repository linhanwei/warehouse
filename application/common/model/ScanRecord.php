<?php
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-28
 * Time: 10:50
 */
namespace app\common\model;

class ScanRecord extends Base
{
    //查询方式
    private $query_type_list = array(
        1 => '网站扫码',
        2 => '手机扫码',
    );

    //查询结果
    private $query_result_list = array(
        1 => '真码',
        2 => '假码',
    );

    //性别
    private $gender_list = array(
        0 => '未知',
        1 => '男',
        2 => '女',
    );

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
    }

    //输出字段处理
    public function getAreaAttr($value,$data)
    {
        $RegionModel = model('Region');
        $province_info = $RegionModel->detail($data['province_id'], 'region_name');
        $city_info = $RegionModel->detail($data['city_id'], 'region_name');
        $district_info = $RegionModel->detail($data['district_id'], 'region_name');

        return $province_info['region_name'].$city_info['region_name'].$district_info['region_name'];
    }

    public function getQueryTypeNameAttr($value,$data)
    {
        return isset($this->query_type_list[$data['query_type']]) ? $this->query_type_list[$data['query_type']] : '';
    }

    public function getQueryResultNameAttr($value,$data)
    {
        return isset($this->query_result_list[$data['query_result']]) ? $this->query_result_list[$data['query_result']] : '';
    }

    public function getGenderNameAttr($value,$data)
    {
        return isset($this->gender_list[$data['gender']]) ? $this->gender_list[$data['gender']] : '';
    }

    public function getLatitudeAndLongitudeAttr($value,$data)
    {
        return $data['longitude'].' - '.$data['latitude'];
    }

    //添加字段处理
    public function setScanIpAttr($value)
    {
        return request()->ip();
    }
}