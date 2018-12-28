<?php
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-28
 * Time: 10:50
 */
namespace app\common\model;

class WechatConfig extends Base
{

    //加密的key
    private $encrypt_key = 'mG&8%ABbP5#TmCpR';

    /*消息加解密方式:
        1=明文模式(明文模式下，不使用消息体加解密功能，安全系数较低),
        2=兼容模式(兼容模式下，明文、密文将共存，方便开发者调试和维护).
        3=安全模式(安全模式下，消息包为纯密文，需要开发者加密和解密，安全系数高)
    */
    public $decrypt_type_list = array(
        1 => '明文模式',
        2 => '兼容模式',
        3 => '安全模式',
    );

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
    }

    //输出字段处理
    public function getAppIdAttr($value,$data)
    {
        $is_bool = isset($data['app_id']) && empty($data['app_id']);
        return $is_bool ? '' : decrypt($data['app_id'], $this->encrypt_key);
    }

    public function getAppSecretAttr($value,$data)
    {
        $is_bool = isset($data['app_secret']) && empty($data['app_secret']);
        return $is_bool ? '' : decrypt($data['app_secret'], $this->encrypt_key);
    }

    public function getCompanyNameAttr($value,$data)
    {
        $compayInfo = model('Company')->detail($data['company_id']);
        return empty($compayInfo) ? '' : $compayInfo['name'];
    }

    public function getDecryptTypeNameAttr($value,$data)
    {
        return isset($this->decrypt_type_list[$data['decrypt_type']]) ? $this->decrypt_type_list[$data['decrypt_type']] : '';
    }

    //添加字段处理
    public function setAppIdAttr($value)
    {
        return empty($value) ? '' : encrypt($value, $this->encrypt_key);
    }

    public function setAppSecretAttr($value)
    {
        return empty($value) ? '' : encrypt($value, $this->encrypt_key);
    }
}