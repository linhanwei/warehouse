<?php
    /**
     * Created by PhpStorm.
     * User: Darren
     * Date: 2018-05-07
     * Time: 18:48
     *
     *  微信用户管理
     *
     */

    namespace Wechat;

    class User extends Base
    {
        /* 用户相关URL */
        //获取用户基本信息（包括UnionID机制）
        const INFO_URL = 'https://api.weixin.qq.com/cgi-bin/user/info';

        //批量获取用户基本信息
        const BATCHGET_URL = 'https://api.weixin.qq.com/cgi-bin/user/info/batchget';

        //生成带参数的二维码
        const QRCODE_TICKET_URL = 'https://api.weixin.qq.com/cgi-bin/qrcode/create';

        //通过ticket换取二维码
        const SHOWQRCODE_URL = 'https://mp.weixin.qq.com/cgi-bin/showqrcode';

        //长链接转短链接接口
        const SHORTURL_URL = 'https://api.weixin.qq.com/cgi-bin/shorturl';

        //初始化
        protected function _init()
        {
            parent::_init();
            //判断accessToken
            $this->_validAccessToken();
        }


        /**
         * 获取用户基本信息
         *
         * @param  string $openid 用户openid
         * @param string  $lang   语言
         *
         * @return array
         */
        public function info($openid, $lang = 'zh_CN')
        {
            $data['openid'] = $openid;
            $data['access_token'] = $this->_getAccessToken();
            $data['lang'] = $lang;

            return $this->_http(self::INFO_URL, $data);
        }

        /**
         * 批量获取用户基本信息
         *
         * @return array
         */
        public function batchget()
        {
            $data['access_token'] = $this->_getAccessToken();

            return $this->_http(self::BATCHGET_URL, $data);
        }

        /**
         * 创建二维码ticket
         *
         * @param string $data  二维码数据
         * @param bool   $is_string 数据内容是否字符串
         * @param bool   $is_forever 是否永久二维码: FALSE = 否, TRUE = 是
         * @param bool   $expire_seconds 该二维码有效时间，以秒为单位。 最大不超过2592000（即30天），此字段如果不填，则默认有效期为30秒。
         *
         * @return array  array(3) {
                                ["ticket"] => string(96) "gQGf8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyTWZpVDFmaC1kNWkxNktqNTFyMWcAAgQuGfxaAwSAOgkA"
                                ["expire_seconds"] => int(604800)
                                ["url"] => string(45) "http://weixin.qq.com/q/02MfiT1fh-d5i16Kj51r1g"
                            }
         *
         * 注意事项:
         *
         * 1、临时二维码，是有过期时间的，最长可以设置为在二维码生成后的30天（即2592000秒）后过期，但能够生成较多数量。
         * 2、永久二维码，是无过期时间的，但数量较少（目前为最多10万个）。
         * 3、scene_id	场景值ID，临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）
         * 4、scene_str 场景值ID（字符串形式的ID），字符串类型，长度限制为1到64
         *
         */
        public function qrcode_ticket($data = '', $is_string = FALSE, $is_forever = false, $expire_seconds = 2592000)
        {
            $url = self::QRCODE_TICKET_URL . '?access_token=' . $this->_getAccessToken();

            if($is_forever === FALSE)
            {
                $action_name = $is_string === FALSE ? 'QR_SCENE' : 'QR_STR_SCENE';
                $scene = $is_string === FALSE ? 'scene_id' : 'scene_str';
                $data = array(
                    'expire_seconds' => $expire_seconds,
                    'action_name' => $action_name,
                    'action_info' => array('scene' => array($scene => $data)),
                );
            }
            else
            {
                $action_name = $is_string === FALSE ? 'QR_LIMIT_SCENE' : 'QR_LIMIT_STR_SCENE';
                $scene = $is_string === FALSE ? 'scene_id' : 'scene_str';
                $data = array(
                    'action_name' => $action_name,
                    'action_info' => array('scene' => array($scene => $data)),
                );
            }

            return $this->_http($url, $data, 'POST');
        }

        /**
         * 通过ticket换取二维码
         *
         * @param string $ticket
         *
         * @return array
         */
        public function showqrcode($ticket = '')
        {
            $url = self::SHOWQRCODE_URL . '?ticket=' . $ticket;
            return $url;
        }

        /**
         * 长链接转短链接接口
         *
         * @param $long_url 需要转换的链接
         *
         * @return array
         */
        public function shorturl($long_url)
        {
            $data['action'] = 'long2short';
            $data['long_url'] = $long_url;

            $url = self::SHORTURL_URL. '?access_token=' .$this->_getAccessToken();
            return $this->_http($url, $data, 'POST');
        }
    }