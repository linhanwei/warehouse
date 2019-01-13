<?php
    /**
     * Created by PhpStorm.
     * User: Darren
     * Date: 2018-04-14
     * Time: 18:48
     *
     *  微信自定义菜单管理
     *
     * 请注意：
     * 1、自定义菜单最多包括3个一级菜单，每个一级菜单最多包含5个二级菜单。
     * 2、一级菜单最多4个汉字，二级菜单最多7个汉字，多出来的部分将会以“...”代替。
     * 3、创建自定义菜单后，菜单的刷新策略是，在用户进入公众号会话页或公众号profile页时，如果发现上一次拉取菜单的请求在5分钟以前，就会拉取一下菜单，如果菜单有更新，就会刷新客户端的菜单。测试时可以尝试取消关注公众账号后再次关注，则可以看到创建后的效果。
     */

    namespace Wechat;

    class CustomMenu extends Base
    {
        //一级菜单限制数量
        const FIRST_MENU_LIMIT_NUMBER = 3;

        //一级菜单最多显示4个汉字
        const FIRST_MENU_WORD_LIMIT_NUMBER = 4;

        //二级菜单限制数量
        const SECOND_MENU_LIMIT_NUMBER = 5;

        //二级菜单最多显示7个汉字
        const SECOND_MENU_WORD_LIMIT_NUMBER = 7;

        //新增接口每日限制次数为2000次
        const CREATE_REQUEST_INTERFACE_LIMIT_NUMBER = 2000;

        //删除接口接口每日限制次数为2000次
        const DELETE_REQUEST_INTERFACE_LIMIT_NUMBER = 2000;

        //测试个性化菜单匹配结果接口为20000次
        const CUSTOM_REQUEST_INTERFACE_LIMIT_NUMBER = 20000;

        //出于安全考虑，一个公众号的所有个性化菜单，最多只能设置为跳转到3个域名下的链接
        const CUSTOM_REQUEST_DOMAIN_LIMIT_NUMBER = 3;

        /* 菜单相关URL */
        //创建
        const CREATE_URL = 'https://api.weixin.qq.com/cgi-bin/menu/create';
        //查询
        const GET_URL = 'https://api.weixin.qq.com/cgi-bin/menu/get';
        //删除
        const DELETE_URL = 'https://api.weixin.qq.com/cgi-bin/menu/delete';
        //创建个性化菜单
        const CREATE_CUSTOM_URL = 'https://api.weixin.qq.com/cgi-bin/menu/addconditional';
        //删除个性化菜单
        const DELETE_CUSTOM_URL = 'https://api.weixin.qq.com/cgi-bin/menu/delconditional';
        //测试个性化菜单匹配结果
        const TEST_CUSTOM_URL = 'https://api.weixin.qq.com/cgi-bin/menu/trymatch';
        //获取自定义菜单配置接口
        const GET_CUSTOM_URL = 'https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info';

        /**
         * 自定义菜单接口可实现多种类型按钮
         *
         * 请注意，3到8的所有事件，仅支持微信iPhone5.4.1以上版本，和Android5.4以上版本的微信用户，
         * 旧版本微信用户点击后将没有回应，开发者也不能正常接收到事件推送。
         * 9和10，是专门给第三方平台旗下未微信认证（具体而言，是资质认证未通过）的订阅号准备的事件类型，
         * 它们是没有事件推送的，能力相对受限，其他类型的公众号不必使用。
         */
        private $button_type_list = array(
            /**
             * 点击推事件用户点击click类型按钮后，微信服务器会通过消息接口推送消息类型为event的结构给开发者
             * （参考消息接口指南），并且带上按钮中开发者填写的key值，开发者可以通过自定义的key值与用户进行交互；
             */
            1 => array('event' => 'click', 'event_name' => '点击推事件'),
            /**
             * 跳转URL用户点击view类型按钮后，微信客户端将会打开开发者在按钮中填写的网页URL，
             * 可与网页授权获取用户基本信息接口结合，获得用户基本信息。
             */
            2 => array('event' => 'view', 'event_name' => '跳转URL'),
            /**
             *扫码推事件用户点击按钮后，微信客户端将调起扫一扫工具，完成扫码操作后显示扫描结果（如果是URL，将进入URL），
             * 且会将扫码的结果传给开发者，开发者可以下发消息。
             */
            3 => array('event' => 'scancode_push', 'event_name' => '扫码推事件或者进入URL'),
            /**
             * 扫码推事件且弹出“消息接收中”提示框用户点击按钮后，微信客户端将调起扫一扫工具，完成扫码操作后，
             * 将扫码的结果传给开发者，同时收起扫一扫工具，然后弹出“消息接收中”提示框，随后可能会收到开发者下发的消息。
             */
            4 => array('event' => 'scancode_waitmsg', 'event_name' => '扫码推事件'),
            /**
             * 弹出系统拍照发图用户点击按钮后，微信客户端将调起系统相机，完成拍照操作后，
             * 会将拍摄的相片发送给开发者，并推送事件给开发者，同时收起系统相机，随后可能会收到开发者下发的消息。
             */
            5 => array('event' => 'pic_sysphoto', 'event_name' => '弹出系统拍照'),
            /**
             * 弹出拍照或者相册发图用户点击按钮后，微信客户端将弹出选择器供用户选择“拍照”或者“从手机相册选择”。
             * 用户选择后即走其他两种流程。
             */
            6 => array('event' => 'pic_photo_or_album', 'event_name' => '弹出拍照或者相册发图'),
            /**
             *弹出微信相册发图器用户点击按钮后，微信客户端将调起微信相册，完成选择操作后，
             * 将选择的相片发送给开发者的服务器，并推送事件给开发者，同时收起相册，随后可能会收到开发者下发的消息。
             */
            7 => array('event' => 'pic_weixin', 'event_name' => '弹出微信相册发图器'),
            /**
             * 弹出地理位置选择器用户点击按钮后，微信客户端将调起地理位置选择工具，完成选择操作后，
             * 将选择的地理位置发送给开发者的服务器，同时收起位置选择工具，随后可能会收到开发者下发的消息。
             */
            8 => array('event' => 'location_select', 'event_name' => '弹出地理位置选择器'),
            /**
             * 下发消息（除文本消息）用户点击media_id类型按钮后，微信服务器会将开发者填写的永久素材id对应的素材下发给用户，
             * 永久素材类型可以是图片、音频、视频、图文消息。
             * 请注意：永久素材id必须是在“素材管理/新增永久素材”接口上传后获得的合法id。
             */
            9 => array('event' => 'media_id', 'event_name' => '下发消息（除文本消息）'),
            /**
             * 跳转图文消息URL用户点击view_limited类型按钮后，微信客户端将打开开发者在按钮中填写的永久素材id对应的图文消息URL，
             * 永久素材类型只支持图文消息。请注意：永久素材id必须是在“素材管理/新增永久素材”接口上传后获得的合法id。
             */
            10 => array('event' => 'view_limited', 'event_name' => '跳转图文消息URL'),
        );

        //初始化
        protected function _init()
        {
            parent::_init();

            //判断accessToken
            $this->_validAccessToken();
        }

        /**
         * 获取菜单列表
         *
         * @return array
         */
        public function getButtonTypeList()
        {
            return $this->button_type_list;
        }

        /**
         * 获取菜单
         * @return array|boolean
         *
         */
        public function get()
        {
            $params = array(
                'access_token' => $this->_getAccessToken(),
            );
            return $this->_http(self::GET_URL, $params);
        }

        /**
         * 创建菜单
         *
         * @param  array $menus 自定义菜单数组
         *
         * @return boolen
         *
         * 示例请参考:https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421141013
         */
        public function create($menus)
        {
            if(!is_array($menus))
            {
                $this->error = '菜单格式不正确';
                return FALSE;
            }

            foreach($menus as $k => $val)
            {
                if(isset($val['sub_button']))
                {
                    if(!is_array($val['sub_button']))
                    {
                        $this->error = '菜单格式不正确';
                        return FALSE;
                    }

                    foreach($val['sub_button'] as $bk => $bval)
                    {
                        if(!$this->_judgeButtonTypeExist($bval['type']))
                        {
                            $this->error = '菜单的响应动作类型不正确';
                            return FALSE;
                        }
                    }
                }
                else
                {
                    if(!$this->_judgeButtonTypeExist($val['type']))
                    {
                        $this->error = '菜单的响应动作类型不正确';
                        return FALSE;
                    }
                }
            }

            $menus = array(
                'button' => $menus
            );

            $url = self::CREATE_URL . '?access_token=' . $this->_getAccessToken();
            return $this->_http($url, $menus, 'POST');
        }

        /**
         * 删除菜单
         * @return boolean
         *
         */
        public function delete()
        {
            $params = array(
                'access_token' => $this->_getAccessToken(),
            );
            return $this->_http(self::DELETE_URL, $params);
        }

        /**
         * 获取个性化菜单
         * @return array|boolean
         *
         */
        public function getCustom()
        {
            $params = array(
                'access_token' => $this->_getAccessToken(),
            );
            return $this->_http(self::GET_CUSTOM_URL, $params);
        }

        /**
         * 创建个性化菜单
         *
         * @param  array $menus 自定义菜单数组
         *
         * @return boolen
         *
         */
        public function createCustom($menus)
        {
            $url = self::CREATE_CUSTOM_URL . '?access_token=' . $this->_getAccessToken();
            return $this->_http($url, $menus, 'POST');
        }

        /**
         * 删除个性化菜单
         *
         * @return boolean
         *
         */
        public function deleteCustom()
        {
            $params = array(
                'access_token' => $this->_getAccessToken(),
            );
            return $this->_http(self::DELETE_CUSTOM_URL, $params);
        }

        /**
         * 测试个性化菜单匹配结果
         *
         * @param $user_id  可以是粉丝的OpenID，也可以是粉丝的微信号。
         *
         * @return array
         */
        public function testCustom($user_id)
        {
            $params = array(
                'user_id' => $user_id,
                'access_token' => $this->_getAccessToken(),
            );
            return $this->_http(self::TEST_CUSTOM_URL, $params);
        }


        /**
         * 判断按钮类型是否存在, 存在返回true,否则false
         *
         * @param $button_type  按钮类型
         *
         * @return bool
         */
        private function _judgeButtonTypeExist($button_type)
        {
            foreach($this->button_type_list as $key => $val)
            {
                if($val['event'] == $button_type)
                {
                    return TRUE;
                }
            }

            return FALSE;
        }

    }