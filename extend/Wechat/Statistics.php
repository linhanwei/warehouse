<?php
    /**
     * Created by PhpStorm.
     * User: Darren
     * Date: 2018-05-07
     * Time: 18:48
     *
     *  微信数据统计管理
     *
     */

    namespace Wechat;

    class Statistics extends Base
    {
        /* 统计相关URL */
        //获取用户增减数据
        const GETUSERSUMMARY_URL = 'https://api.weixin.qq.com/datacube/getusersummary';

        //获取累计用户数据
        const GETUSERCUMULATE_URL = 'https://api.weixin.qq.com/datacube/getusercumulate';

        //获取接口分析数据
        const GETINTERFACESUMMARY_URL = 'https://api.weixin.qq.com/datacube/getinterfacesummary';

        //获取接口分析分时数据
        const GETINTERFACESUMMARYHOUR_URL = 'https://api.weixin.qq.com/datacube/getinterfacesummaryhour';

        //获取消息发送概况数据
        const GETUPSTREAMMSG_URL = 'https://api.weixin.qq.com/datacube/getupstreammsg';

        //获取消息分送分时数据
        const GETUPSTREAMMSGHOUR_URL = 'https://api.weixin.qq.com/datacube/getupstreammsghour';

        //获取消息发送周数据
        const GETUPSTREAMMSGWEEK_URL = 'https://api.weixin.qq.com/datacube/getupstreammsgweek';

        //获取消息发送月数据
        const GETUPSTREAMMSGMONTH_URL = 'https://api.weixin.qq.com/datacube/getupstreammsgmonth';

        //获取消息发送分布数据
        const GETUPSTREAMMSGDIST_URL = 'https://api.weixin.qq.com/datacube/getupstreammsgdist';

        //获取消息发送分布周数据
        const GETUPSTREAMMSGDISTWEEK_URL = 'https://api.weixin.qq.com/datacube/getupstreammsgdistweek';

        //获取消息发送分布月数据
        const GETUPSTREAMMSGDISTMONTH_URL = 'https://api.weixin.qq.com/datacube/getupstreammsgdistmonth';

        //初始化
        protected function _init()
        {
            parent::_init();
            //判断accessToken
            $this->_validAccessToken();
        }

        /**
         * 获取用户增减数据
         *
         * @param  string $begin_date 开始时间: 2018-05-12
         * @param string  $end_date   结束时间: 2018-05-12
         *
         * @return array
         *  ref_date    数据的日期
         * user_source    用户的渠道，数值代表的含义如下： 0代表其他合计 1代表公众号搜索 17代表名片分享 30代表扫描二维码 43代表图文页右上角菜单 51代表支付后关注（在支付完成页）
         * 57代表图文页内公众号名称 75代表公众号文章广告 78代表朋友圈广告 new_user    新增的用户数量 cancel_user
         * 取消关注的用户数量，new_user减去cancel_user即为净增用户数量
         */
        public function getUserSummary($begin_date = '', $end_date = '')
        {
            //最大时间跨度为7天
            $now_begin_date = date('Y-m-d', strtotime('-7 day'));
            $data['begin_date'] = ($begin_date < $now_begin_date || empty($begin_date)) ? $now_begin_date : $begin_date;
            //结束时间最大为昨天
            $data['end_date'] = ($end_date >= date('Y-m-d') || empty($end_date)) ? date('Y-m-d', strtotime('-1 day')) : $end_date;
            $url = self::GETUSERSUMMARY_URL . '?access_token=' . $this->_getAccessToken();

            return $this->_http($url, $data, 'POST');
        }

        /**
         * 获取累计用户数据
         *
         * @param  string $begin_date 开始时间: 2018-05-12
         * @param string  $end_date   结束时间: 2018-05-12
         *
         * @return array
         *
         *  ref_date    数据的日期
         * user_source    用户的渠道，数值代表的含义如下： 0代表其他合计 1代表公众号搜索 17代表名片分享 30代表扫描二维码 43代表图文页右上角菜单 51代表支付后关注（在支付完成页）
         * 57代表图文页内公众号名称 75代表公众号文章广告 78代表朋友圈广告 cumulate_user    总用户量
         */
        public function getUserCumulate($begin_date = '', $end_date = '')
        {
            //最大时间跨度为7天
            $now_begin_date = date('Y-m-d', strtotime('-7 day'));
            $data['begin_date'] = ($begin_date < $now_begin_date || empty($begin_date)) ? $now_begin_date : $begin_date;
            //结束时间最大为昨天
            $data['end_date'] = ($end_date >= date('Y-m-d') || empty($end_date)) ? date('Y-m-d', strtotime('-1 day')) : $end_date;
            $url = self::GETUSERCUMULATE_URL . '?access_token=' . $this->_getAccessToken();

            return $this->_http($url, $data, 'POST');
        }

        /**
         * 获取接口分析数据
         *
         * @param  string $begin_date 开始时间: 2018-05-12
         * @param string  $end_date   结束时间: 2018-05-12
         *
         * @return array
         *
         * ref_date    数据的日期
         * callback_count    通过服务器配置地址获得消息后，被动回复用户消息的次数
         * fail_count    上述动作的失败次数
         * total_time_cost    总耗时，除以callback_count即为平均耗时
         * max_time_cost    最大耗时
         */
        public function getInterfaceSummary($begin_date = '', $end_date = '')
        {
            //最大时间跨度为30天
            $now_begin_date = date('Y-m-d', strtotime('-30 day'));
            $data['begin_date'] = ($begin_date < $now_begin_date || empty($begin_date)) ? $now_begin_date : $begin_date;
            //结束时间最大为昨天
            $data['end_date'] = ($end_date >= date('Y-m-d') || empty($end_date)) ? date('Y-m-d', strtotime('-1 day')) : $end_date;
            $url = self::GETINTERFACESUMMARY_URL . '?access_token=' . $this->_getAccessToken();

            return $this->_http($url, $data, 'POST');
        }

        /**
         * 获取接口分析分时数据
         *
         * @param  string $date 当天时间 2018-05-12
         *
         * @return array
         *
         * ref_date    数据的日期
         * ref_hour    数据的小时
         * callback_count    通过服务器配置地址获得消息后，被动回复用户消息的次数
         * fail_count    上述动作的失败次数
         * total_time_cost    总耗时，除以callback_count即为平均耗时
         * max_time_cost    最大耗时
         */
        public function getInterfaceSummaryHour($date = '')
        {
            //最大时间跨度为1天
            $now_begin_date = date('Y-m-d', strtotime('-1 day'));
            $data['begin_date'] = ($date > $now_begin_date || empty($date)) ? $now_begin_date : $date;
            //结束时间最大为昨天
            $data['end_date'] = $data['begin_date'];
            $url = self::GETINTERFACESUMMARYHOUR_URL . '?access_token=' . $this->_getAccessToken();

            return $this->_http($url, $data, 'POST');
        }

        /**
         * 获取消息发送概况数据
         *
         * @param  string $begin_date 开始时间: 2018-05-12
         * @param string  $end_date   结束时间: 2018-05-12
         *
         * @return array
         *
         * ref_date    数据的日期，需在begin_date和end_date之间
         * user_source    用户的渠道，数值代表的含义如下： 0代表其他合计 1代表公众号搜索 17代表名片分享 30代表扫描二维码 43代表图文页右上角菜单 51代表支付后关注（在支付完成页）
         *                57代表图文页内公众号名称 75代表公众号文章广告 78代表朋友圈广告 new_user    新增的用户数量 cancel_user
         * msg_type    消息类型，代表含义如下： 1代表文字 2代表图片 3代表语音 4代表视频 6代表第三方应用消息（链接消息）
         * msg_user    上行发送了（向公众号发送了）消息的用户数
         * msg_count    上行发送了消息的消息总数
         */
        public function getUpstreamMsg($begin_date = '', $end_date = '')
        {
            //最大时间跨度为7天
            $now_begin_date = date('Y-m-d', strtotime('-7 day'));
            $data['begin_date'] = ($begin_date < $now_begin_date || empty($begin_date)) ? $now_begin_date : $begin_date;
            //结束时间最大为昨天
            $data['end_date'] = ($end_date >= date('Y-m-d') || empty($end_date)) ? date('Y-m-d', strtotime('-1 day')) : $end_date;
            $url = self::GETUPSTREAMMSG_URL . '?access_token=' . $this->_getAccessToken();

            return $this->_http($url, $data, 'POST');
        }

        /**
         * 获取接口分析分时数据
         *
         * @param  string $date 当天时间 2018-05-12
         *
         * @return array
         *
         * ref_date    数据的日期，需在begin_date和end_date之间
         * ref_hour    数据的小时，包括从000到2300，分别代表的是[000,100)到[2300,2400)，即每日的第1小时和最后1小时
         * user_source    用户的渠道，数值代表的含义如下： 0代表其他合计 1代表公众号搜索 17代表名片分享 30代表扫描二维码 43代表图文页右上角菜单 51代表支付后关注（在支付完成页）
         *                57代表图文页内公众号名称 75代表公众号文章广告 78代表朋友圈广告 new_user    新增的用户数量 cancel_user
         * msg_type    消息类型，代表含义如下： 1代表文字 2代表图片 3代表语音 4代表视频 6代表第三方应用消息（链接消息）
         * msg_user    上行发送了（向公众号发送了）消息的用户数
         * msg_count    上行发送了消息的消息总数
         */
        public function getUpstreamMsgHour($date = '')
        {
            //最大时间跨度为1天
            $now_begin_date = date('Y-m-d', strtotime('-1 day'));
            $data['begin_date'] = ($date > $now_begin_date || empty($date)) ? $now_begin_date : $date;
            //结束时间最大为昨天
            $data['end_date'] = $data['begin_date'];
            $url = self::GETUPSTREAMMSGHOUR_URL . '?access_token=' . $this->_getAccessToken();

            return $this->_http($url, $data, 'POST');
        }

        /**
         * 获取消息发送周数据
         *
         * @param  string $begin_date 开始时间: 2018-05-12
         * @param string  $end_date   结束时间: 2018-05-12
         *
         * @return array
         *
         * ref_date    数据的日期，需在begin_date和end_date之间
         * user_source    用户的渠道，数值代表的含义如下： 0代表其他合计 1代表公众号搜索 17代表名片分享 30代表扫描二维码 43代表图文页右上角菜单 51代表支付后关注（在支付完成页）
         *                57代表图文页内公众号名称 75代表公众号文章广告 78代表朋友圈广告 new_user    新增的用户数量 cancel_user
         * msg_type    消息类型，代表含义如下： 1代表文字 2代表图片 3代表语音 4代表视频 6代表第三方应用消息（链接消息）
         * msg_user    上行发送了（向公众号发送了）消息的用户数
         * msg_count    上行发送了消息的消息总数
         */
        public function getUpstreamMsgWeek($begin_date = '', $end_date = '')
        {
            //最大时间跨度为30天
            $now_begin_date = date('Y-m-d', strtotime('-30 day'));
            $data['begin_date'] = ($begin_date < $now_begin_date || empty($begin_date)) ? $now_begin_date : $begin_date;
            //结束时间最大为昨天
            $data['end_date'] = ($end_date >= date('Y-m-d') || empty($end_date)) ? date('Y-m-d', strtotime('-1 day')) : $end_date;
            $url = self::GETUPSTREAMMSGWEEK_URL . '?access_token=' . $this->_getAccessToken();

            return $this->_http($url, $data, 'POST');
        }

        /**
         * 获取消息发送月数据
         *
         * @param  string $begin_date 开始时间: 2018-05-12
         * @param string  $end_date   结束时间: 2018-05-12
         *
         * @return array
         *
         * ref_date    数据的日期，需在begin_date和end_date之间
         * user_source    用户的渠道，数值代表的含义如下： 0代表其他合计 1代表公众号搜索 17代表名片分享 30代表扫描二维码 43代表图文页右上角菜单 51代表支付后关注（在支付完成页）
         *                57代表图文页内公众号名称 75代表公众号文章广告 78代表朋友圈广告 new_user    新增的用户数量 cancel_user
         * msg_type    消息类型，代表含义如下： 1代表文字 2代表图片 3代表语音 4代表视频 6代表第三方应用消息（链接消息）
         * msg_user    上行发送了（向公众号发送了）消息的用户数
         * msg_count    上行发送了消息的消息总数
         */
        public function getUpstreamMsgMonth($begin_date = '', $end_date = '')
        {
            //最大时间跨度为30天
            $now_begin_date = date('Y-m-d', strtotime('-30 day'));
            $data['begin_date'] = ($begin_date < $now_begin_date || empty($begin_date)) ? $now_begin_date : $begin_date;
            //结束时间最大为昨天
            $data['end_date'] = ($end_date >= date('Y-m-d') || empty($end_date)) ? date('Y-m-d', strtotime('-1 day')) : $end_date;
            $url = self::GETUPSTREAMMSGMONTH_URL . '?access_token=' . $this->_getAccessToken();

            return $this->_http($url, $data, 'POST');
        }

        /**
         * 获取消息发送分布数据
         *
         * @param  string $begin_date 开始时间: 2018-05-12
         * @param string  $end_date   结束时间: 2018-05-12
         *
         * @return array
         *
         * ref_date    数据的日期，需在begin_date和end_date之间
         * user_source    用户的渠道，数值代表的含义如下： 0代表其他合计 1代表公众号搜索 17代表名片分享 30代表扫描二维码 43代表图文页右上角菜单 51代表支付后关注（在支付完成页）
         *                57代表图文页内公众号名称 75代表公众号文章广告 78代表朋友圈广告 new_user    新增的用户数量 cancel_user
         * count_interval    当日发送消息量分布的区间，0代表 “0”，1代表“1-5”，2代表“6-10”，3代表“10次以上”
         * msg_user    上行发送了（向公众号发送了）消息的用户数
         */
        public function getUpstreamMsgDist($begin_date = '', $end_date = '')
        {
            //最大时间跨度为30天
            $now_begin_date = date('Y-m-d', strtotime('-15 day'));
            $data['begin_date'] = ($begin_date < $now_begin_date || empty($begin_date)) ? $now_begin_date : $begin_date;
            //结束时间最大为昨天
            $data['end_date'] = ($end_date >= date('Y-m-d') || empty($end_date)) ? date('Y-m-d', strtotime('-1 day')) : $end_date;
            $url = self::GETUPSTREAMMSGDIST_URL . '?access_token=' . $this->_getAccessToken();

            return $this->_http($url, $data, 'POST');
        }

        /**
         * 获取消息发送分布周数据
         *
         * @param  string $begin_date 开始时间: 2018-05-12
         * @param string  $end_date   结束时间: 2018-05-12
         *
         * @return array
         *
         * ref_date    数据的日期，需在begin_date和end_date之间
         * user_source    用户的渠道，数值代表的含义如下： 0代表其他合计 1代表公众号搜索 17代表名片分享 30代表扫描二维码 43代表图文页右上角菜单 51代表支付后关注（在支付完成页）
         *                57代表图文页内公众号名称 75代表公众号文章广告 78代表朋友圈广告 new_user    新增的用户数量 cancel_user
         * count_interval    当日发送消息量分布的区间，0代表 “0”，1代表“1-5”，2代表“6-10”，3代表“10次以上”
         * msg_user    上行发送了（向公众号发送了）消息的用户数
         */
        public function getUpstreamMsgDistWeek($begin_date = '', $end_date = '')
        {
            //最大时间跨度为30天
            $now_begin_date = date('Y-m-d', strtotime('-30 day'));
            $data['begin_date'] = ($begin_date < $now_begin_date || empty($begin_date)) ? $now_begin_date : $begin_date;
            //结束时间最大为昨天
            $data['end_date'] = ($end_date >= date('Y-m-d') || empty($end_date)) ? date('Y-m-d', strtotime('-1 day')) : $end_date;
            $url = self::GETUPSTREAMMSGDISTWEEK_URL . '?access_token=' . $this->_getAccessToken();

            return $this->_http($url, $data, 'POST');
        }

        /**
         * 获取消息发送分布月数据
         *
         * @param  string $begin_date 开始时间: 2018-05-12
         * @param string  $end_date   结束时间: 2018-05-12
         *
         * @return array
         *
         * ref_date    数据的日期，需在begin_date和end_date之间
         * user_source    用户的渠道，数值代表的含义如下： 0代表其他合计 1代表公众号搜索 17代表名片分享 30代表扫描二维码 43代表图文页右上角菜单 51代表支付后关注（在支付完成页）
         *                57代表图文页内公众号名称 75代表公众号文章广告 78代表朋友圈广告 new_user    新增的用户数量 cancel_user
         * count_interval    当日发送消息量分布的区间，0代表 “0”，1代表“1-5”，2代表“6-10”，3代表“10次以上”
         * msg_user    上行发送了（向公众号发送了）消息的用户数
         */
        public function getUpstreamMsgDistMonth($begin_date = '', $end_date = '')
        {
            //最大时间跨度为30天
            $now_begin_date = date('Y-m-d', strtotime('-30 day'));
            $data['begin_date'] = ($begin_date < $now_begin_date || empty($begin_date)) ? $now_begin_date : $begin_date;
            //结束时间最大为昨天
            $data['end_date'] = ($end_date >= date('Y-m-d') || empty($end_date)) ? date('Y-m-d', strtotime('-1 day')) : $end_date;
            $url = self::GETUPSTREAMMSGDISTMONTH_URL . '?access_token=' . $this->_getAccessToken();

            return $this->_http($url, $data, 'POST');
        }

    }