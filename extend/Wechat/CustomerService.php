<?php
    /**
     * Created by PhpStorm.
     * User: Darren
     * Date: 2018-05-07
     * Time: 18:48
     *
     *  微信客服管理
     *
     */

    namespace Wechat;

    class CustomerService extends Base
    {
        /* 客服相关URL */

        //获取所有客服账号
        const GETKFLIST_URL = 'https://api.weixin.qq.com/cgi-bin/customservice/getkflist';

        //初始化
        protected function _init()
        {
            parent::_init();
            //判断accessToken
            $this->_validAccessToken();
        }

        /**
         * 获取所有客服账号
         *
         * @return array
         *              array(1) {
         * ["kf_list"] => array(1) {
             * [0] => array(5) {
                 * ["kf_account"] => string(15) "kf2001@tcfw8080"
                 * ["kf_headimgurl"] => string(134)
                 * "http://mmbiz.qpic.cn/mmbiz_jpg/mA3ws440d0m3a5ZfmLNlWnRdNnpsXzGIaS8eTDUV3e9rKDPVWnia5MtPlTRTwaONgScFZZ5CgeoWeXsJXo8XtgA/300?wx_fmt=jpeg"
                 * ["kf_id"] => int(2001)
                 * ["kf_nick"] => string(4) "test"
                 * ["kf_wx"] => string(12) "lin282777041"
                 * }
             * }
         * }
         */
        public function getKfList()
        {
            $url = self::GETKFLIST_URL . '?access_token=' . $this->_getAccessToken();

            return $this->_http($url);
        }
    }