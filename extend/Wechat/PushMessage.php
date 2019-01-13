<?php
    /**
     * Created by PhpStorm.
     * User: Darren
     * Date: 2018-04-14
     * Time: 18:50
     *
     *  向用户主动推送消息
     */

    namespace Wechat;

    class PushMessage extends Base
    {
        //发送的信息
        private $send_data = array();

        //发送消息类型
        private $msg_type_list = array(
            'text', //文本
            'image', //图片
            'voice', //语音
            'video', //视频
            'music', //音乐
            'news', //图文消息（点击跳转到外链）
            'mpnews', //图文消息（点击跳转到图文消息页面）
            'wxcard', //卡券
            'miniprogrampage', //小程序
        );

        //初始化
        protected function _init()
        {
            parent::_init();

            //判断accessToken
            $this->_validAccessToken();
        }

        /**
         * 发送文本消息
         *
         * @param $openid        接收用户的openid
         * @param $content       发送内容
         * @param $customservice 客服账号信息, 例如:"test1@kftest"
         *
         * @return bool
         */
        public function sendText($openid, $content, $customservice = '')
        {
            $this->send_data['text'] = array(
                'content' => $content,
            );
            if(!empty($customservice)) {
                $this->send_data['customservice'] = array(
                    'kf_account' => $customservice,
                );
            }

            return $this->_sendMsg($openid, 'text');
        }

        /**
         * 发送图片消息
         *
         * @param $openid   接收用户的openid
         * @param $media_id 发送的图片（点击跳转到图文消息页）的媒体ID
         *
         * @return bool
         */
        public function sendImage($openid, $media_id)
        {
            $this->send_data['image'] = array(
                'media_id' => $media_id,
            );

            return $this->_sendMsg($openid, 'image');
        }

        /**
         * 发送视频消息
         *
         * @param $openid      接收用户的openid
         * @param $media_id    发送的视频（点击跳转到图文消息页）的媒体ID
         * @param $title       视频消息小程序卡片的标题
         * @param $description 视频消息的描述
         *
         * @return bool
         */
        public function sendVideo($openid, $media_id, $title = '', $description = '')
        {
            $content = array(
                "media_id" => $media_id,
                "thumb_media_id" => "MEDIA_ID",
                "title" => $title,
                "description" => $description,
            );
            $this->send_data['video'] = $content;

            return $this->_sendMsg($openid, 'video');
        }

        /**
         * 发送语音消息
         *
         * @param $openid   接收用户的openid
         * @param $media_id 发送的语音（点击跳转到图文消息页）的媒体ID
         *
         * @return bool
         */
        public function sendVoice($openid, $media_id)
        {
            $this->send_data['voice'] = array(
                'media_id' => $media_id,
            );

            return $this->_sendMsg($openid, 'video');
        }

        /**
         * 发送音乐消息
         *
         * @param $openid         接收用户的openid
         * @param $thumb_media_id 缩略图的媒体ID
         * @param $musicurl       音乐链接
         * @param $hqmusicurl     高品质音乐链接，wifi环境优先使用该链接播放音乐
         * @param $title          音乐消息的标题
         * @param $description    音乐消息的描述
         *
         * @return bool
         */
        public function sendMusic($openid, $thumb_media_id, $musicurl, $hqmusicurl, $title = '', $description = '')
        {
            $content = array(
                "thumb_media_id" => $thumb_media_id,
                "musicurl" => $musicurl,
                "hqmusicurl" => $hqmusicurl,
                "title" => $title,
                "description" => $description,
            );
            $this->send_data['music'] = $content;

            return $this->_sendMsg($openid, 'music');
        }

        /**
         * 图文消息（点击跳转到外链）,图文消息条数限制在8条以内，注意，如果图文数超过8，则将会无响应
         *
         * @param        $openid   接收用户的openid
         * @param  array $articles 要回复的图文内容,可以多条消息
         *
         * @return bool
         *
         * 示例:
         * {
         * "touser":"OPENID",
         * "msgtype":"news",
         * "news":{
         * "articles": [
         * {
         * "title":"Happy Day",
         * "description":"Is Really A Happy Day",
         * "url":"URL",
         * "picurl":"PIC_URL"
         * },
         * {
         * "title":"Happy Day",
         * "description":"Is Really A Happy Day",
         * "url":"URL",
         * "picurl":"PIC_URL"
         * }
         * ]
         * }
         * }
         */
        public function sendNews($openid, $articles)
        {
            if(count($articles) > 8) {
                $this->error = '图文消息条数最多8条';

                return FALSE;
            }
            $this->send_data['news']['articles'] = $articles;

            return $this->_sendMsg($openid, 'news');
        }

        /**
         * 图文消息（点击跳转到图文消息页面）
         *
         * @param  $openid   接收用户的openid
         * @param  $media_id 发送的图文（点击跳转到图文消息页）的媒体ID
         *
         * @return bool
         */
        public function sendMpnews($openid, $media_id)
        {
            $content = array(
                'media_id' => $media_id,
            );
            $this->send_data['mpnews'] = $content;

            return $this->_sendMsg($openid, 'mpnews');
        }

        /**
         * 卡券消息
         *
         * @param  $openid 接收用户的openid
         * @param  $card_id
         *
         * @return bool
         */
        public function sendWxcard($openid, $card_id)
        {
            $content = array(
                'card_id' => $card_id,
            );
            $this->send_data['wxcard'] = $content;

            return $this->_sendMsg($openid, 'wxcard');
        }

        /**
         * 小程序消息
         *
         * @param        $openid         接收用户的openid
         * @param        $appid          小程序的appid，要求小程序的appid需要与公众号有关联关系
         * @param        $pagepath       小程序的页面路径，跟app.json对齐，支持参数，比如pages/index/index?foo=bar
         * @param        $thumb_media_id 小程序卡片图片的媒体ID，小程序卡片图片建议大小为520*416
         * @param string $title          小程序卡片的标题
         *
         * @return bool
         */
        public function sendMiniprogrampage($openid, $appid, $pagepath, $thumb_media_id, $title = '')
        {
            $content = array(
                'title' => $title,
                'appid' => $appid,
                'pagepath' => $pagepath,
                'thumb_media_id' => $thumb_media_id,
            );
            $this->send_data['miniprogrampage'] = $content;

            return $this->_sendMsg($openid, 'miniprogrampage');
        }

        /**
         * 发送消息
         * @return boolean
         * @author 、lin
         */
        private function _sendMsg($openid, $msgtype = 'text')
        {
            /* 基础数据 */
            $this->send_data['touser'] = (string)$openid;
            $this->send_data['msgtype'] = $msgtype;
            $url = self::CUSTOM_SEND_URL . '?access_token=' . $this->_getAccessToken();

            return $this->_http($url, $this->send_data, 'POST');
        }
    }