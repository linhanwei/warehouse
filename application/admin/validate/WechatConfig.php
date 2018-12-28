<?php
    /**
     * Created by PhpStorm.
     * User: LHW
     * Date: 2018-02-27
     * Time: 17:30
     */

    namespace app\admin\validate;

    use think\Validate;

    class WechatConfig extends Validate
    {
        //验证规则
        protected $rule = array(
            'company_id' => 'require|token',
            'app_name' => 'require',
            'app_id' => 'require',
            'app_secret' => 'require',
            'token' => 'require',
            'encoding_aes_key' => 'require',
            'wechat_config_id' => 'require',
        );

        // 验证提示信息
        protected $message = array(
            'company_id.require' => '公司不能为空',
            'app_name.require' => '公众号名称不能为空',
            'app_id.require' => '开发者ID(AppID)不能为空',
            'app_secret.require' => '开发者密码(AppSecret)不能为空',
            'token.require' => '令牌(Token)不能为空',
            'encoding_aes_key.require' => '消息加解密密钥(EncodingAESKey)不能为空',
            'wechat_config_id.require' => '请选择公众号',
            'menus.require' => '自定义菜单不能为空',
        );

        //验证场景
        protected $scene = array(
            'add' => array('company_id', 'app_name', 'app_id', 'app_secret', 'token', 'encoding_aes_key'),
            'edit' => array('company_id', 'app_name', 'app_id', 'app_secret', 'token', 'encoding_aes_key'),
            'customMenu' => array('wechat_config_id', ),
        );
    }