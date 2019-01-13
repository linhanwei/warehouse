<?php
    /**
     * Created by PhpStorm.
     * User: Darren
     * Date: 2018-05-07
     * Time: 18:48
     *
     *  微信素材管理
     *
     */

    namespace Wechat;

    class Material extends Base
    {
        /* 素材相关URL */
        //新增永久素材
        const ADD_NEWS_URL = 'https://api.weixin.qq.com/cgi-bin/material/add_news';
        //获取素材总数
        const GET_COUNT_URL = 'https://api.weixin.qq.com/cgi-bin/material/get_materialcount';
        //获取素材列表
        const BATCHGET_URL = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material';

        /**
         * 素材的类型
         *
         */
        private $type_list = array(
            array('type' => 'image', 'type_name' => '图片'),
            array('type' => 'video', 'type_name' => '视频'),
            array('type' => 'voice', 'type_name' => '语音'),
            array('type' => 'news', 'type_name' => '图文'),
        );

        //初始化
        protected function _init()
        {
            parent::_init();

            //判断accessToken
            $this->_validAccessToken();
        }

        /**
         * 获取 素材的类型
         *
         * @return array
         */
        public function getTypeList()
        {
            return $this->type_list;
        }

        /**
         * 获取素材总数
         * @return array
         *
         * 示例请参考:https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1444738733
         */
        public function getCount()
        {
            $params = array(
                'access_token' => $this->_getAccessToken(),
            );
            return $this->_http(self::GET_COUNT_URL, $params);
        }

        /**
         * 获取素材列表
         *
         * @param  string $type 素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
         * @param  integer $offset 从全部素材的该偏移位置开始返回，0表示从第一个素材 返回
         * @param  integer $count 返回素材的数量，取值在1到20之间
         *
         * @return array
         *
         * 示例请参考:https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1444738734
         */
        public function batchget($type = 'news', $offset = 0, $count = 20)
        {
            $params = array(
                'type' => $type,
                'offset' => $offset,
                'count' => $count,
            );

            $url = self::BATCHGET_URL.'?access_token='.$this->_getAccessToken();
            return $this->_http($url, $params, 'POST');
        }
    }