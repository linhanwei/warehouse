<?php
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-27
 * Time: 17:30
 */

namespace app\admin\validate;

use think\Validate;

class Company extends Validate
{
    //正则验证规则
    protected $regex = [ 'mobile' => VALIDATE_MOBILE_REGX];

    //验证规则
    protected $rule = array(
        'name' => 'require|token',
        'legal_person' => 'require',
        'reg_capital' => 'require',
        'business_circles_number' => 'require',
        'mobile' => 'require|regex:mobile',
        'email' => 'require|email',
        'tel' => 'require',
        'contacts' => 'require',
        'public_account' => 'require',
        'bank_id' => 'require',
        'business_circles_number' => 'require',
        'business_license_img' => 'require',
        'trademark_img' => 'require',
        'other_img' => 'require',
        'province_id' => 'require',
        'city_id' => 'require',
        'district_id' => 'require',
        'address' => 'require',
    );

    // 验证提示信息
    protected $message = array(
        'name.require' => '企业名称不能为空',
        'legal_person.require' => '法人不能为空',
        'reg_capital.require' => '注册资本不能为空',
        'business_circles_number.require' => '工商注册号不能为空',
        'mobile.require' => '手机号码不能为空',
        'mobile.mobile' => '手机号码格式不正确',
        'email.require' => '邮箱不能为空',
        'email.mobile' => '邮箱格式不正确',
        'tel.require' => '固定电话不能为空',
        'contacts.require' => '联系人不能为空',
        'public_account.require' => '对公账户不能为空',
        'bank_id.require' => '对公银行不能为空',
        'business_license_img.require' => '营业执照不能为空',
        'trademark_img.require' => '商标证不能为空',
        'province_id.require' => '省份不能为空',
        'city_id.require' => '市不能为空',
        'district_id.require' => '区县不能为空',
        'address.require' => '详细地址不能为空',
    );

    //验证场景
    protected $scene = array(
        'add' => array('name', 'legal_person', 'reg_capital', 'business_circles_number', 'mobile', 'contacts', 'public_account', 'bank_id', 'business_license_img', 'province_id', 'city_id', 'district_id', 'address'),
        'edit' => array('name', 'legal_person', 'reg_capital', 'business_circles_number', 'mobile', 'contacts', 'public_account', 'bank_id', 'business_license_img', 'province_id', 'city_id', 'district_id', 'address'),
    );
}