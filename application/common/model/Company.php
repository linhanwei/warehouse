<?php
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-28
 * Time: 10:50
 */
namespace app\common\model;

class Company extends Base
{
    //字段设置类型自动转换
    protected $type = [

    ];

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
    }

    //输出字段处理
    public function getCompanyAddressAttr($value,$data)
    {
        $RegionModel = model('Region');
        $province_info = $RegionModel->detail($data['province_id'], 'region_name');
        $city_info = $RegionModel->detail($data['city_id'], 'region_name');
        $district_info = $RegionModel->detail($data['district_id'], 'region_name');

        return $province_info['region_name'].$city_info['region_name'].$district_info['region_name'].$data['address'];
    }

    public function getBankNameAttr($value,$data)
    {
        $BankModel = model('Bank');
        $bank_info = $BankModel->detail($data['bank_id'], 'name');
        return $bank_info['name'];
    }

    public function getContactsNumberAttr($value,$data)
    {
        if($data['mobile'] && $data['tel'])
        {
            $contacts_number = $data['mobile'] .' | '. $data['tel'];
        }
        elseif($data['mobile'])
        {
            $contacts_number = $data['mobile'];
        }
        else
        {
            $contacts_number = $data['tel'];
        }

        return $contacts_number;
    }

    public function getBusinessLicenseImgAttr($value,$data)
    {
        if(empty($data['business_license_img']))
        {
            return false;
        }

        return explode(',', $data['business_license_img']);
    }

    public function getTrademarkImgAttr($value,$data)
    {
        if(empty($data['trademark_img']))
        {
            return false;
        }

        return explode(',', $data['trademark_img']);
    }

    public function getOtherImgAttr($value,$data)
    {
        if(empty($data['other_img']))
        {
            return false;
        }

        return explode(',', $data['other_img']);
    }

    //添加字段处理
    public function setBusinessLicenseImgAttr($value)
    {
        if(empty($value))
        {
            return false;
        }

        return implode(',', $value);
    }

    public function setTrademarkImgAttr($value)
    {
        if(empty($value))
        {
            return false;
        }
        return implode(',', $value);
    }

    public function setOtherImgAttr($value)
    {
        if(empty($value))
        {
            return false;
        }
        return implode(',', $value);
    }
}