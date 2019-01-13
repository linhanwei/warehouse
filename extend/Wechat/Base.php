<?php
    /**
     * Created by PhpStorm.
     * User: Darren
     * Date: 2018-04-14
     * Time: 18:52
     *
     *  微信基础类
     *
     */

    namespace Wechat;

    class Base
    {
        /* 获取ACCESS_TOKEN URL */
        const AUTH_URL = 'https://api.weixin.qq.com/cgi-bin/token';
        //获取微信服务器IP地址
        const GETCALLBACKIP_URL = 'https://api.weixin.qq.com/cgi-bin/getcallbackip';

        //公众号配置令牌(Token)
        protected $token;
        //开发者ID(AppID)
        protected $appid;
        //开发者密码(AppSecret)
        protected $secret;
        //公众平台的API调用所需的access_token
        protected $access_token;
        //是否调试模式
        protected $debug = FALSE;
        //错误码
        protected $errcode = 0;
        //错误信息
        protected $error;
        //是否使用消息加密机制
        protected $encode;
        //消息加解密密钥(EncodingAESKey)
        protected $AESKey;
        protected $mch_id;
        protected $payKey;
        protected $pemCret;
        protected $pemKey;

        public function __construct($options = array())
        {
            $this->token = isset($options['token']) ? $options['token'] : '';
            $this->appid = isset($options['app_id']) ? $options['app_id'] : '';
            $this->secret = isset($options['app_secret']) ? $options['app_secret'] : '';
            $this->AESKey = isset($options['encoding_aes_key']) ? $options['encoding_aes_key'] : '';
            $this->debug = isset($options['debug']) ? $options['debug'] : FALSE;
            $this->encode = $options['decrypt_type'] != 1 ? TRUE : FALSE;
            $this->mch_id = isset($options['mch_id']) ? $options['mch_id'] : '';
            $this->payKey = isset($options['payKey']) ? $options['payKey'] : '';
            $this->pem = isset($options['pem']) ? $options['pem'] : '';

            if($this->encode && strlen($this->AESKey) != 43) {
                $this->error = 'AESKey Lenght Error';

                return FALSE;
            }

            //自定义初始化函数
            $this->_init();
        }

        //自定义初始化函数
        protected function _init()
        {

        }

        public function setConfig($config, $value)
        {
            $this->$config = $value;
        }

        public function __get($key)
        {
            return $this->$key;
        }

        public function __set($key, $value)
        {
            $this->$key = $value;
        }

        /**
         * 获取微信服务器IP地址
         *
         * @return array
         */
        public function getcallbackip()
        {
            $url = self::GETCALLBACKIP_URL . '?access_token=' . $this->getToken();
            $jsonArr = $this->_http($url);

            if(isset($jsonArr['ip_list']))
            {
                return $jsonArr['ip_list'];
            }

            return $jsonArr;
        }

        /**
         * 取得 access_token
         *
         * @param bool $is_refresh 是否刷新
         *
         * @return string|boolean
         */
        public function getToken($is_refresh = FALSE)
        {
            if($is_refresh === FALSE)
            {
                return $this->access_token ? $this->access_token : $this->_getAccessToken();
            }

            return $this->_getAccessToken($is_refresh);
        }

        /**
         *  验证配置URL有效性
         *
         * @author 、lin
         */
        public function valid()
        {
            $echoStr = input('echostr');
            if(!empty($echoStr)) {
                $this->_checkSignature() && exit($echoStr);
            }
            exit('Access Denied!');
        }

        /**
         * 捕获错误信息
         * @return string 错误信息
         */
        public function getError()
        {
            return $this->error;
        }

        /**
         * 捕获错误码信息
         * @return string 错误码
         */
        public function getErrorCode()
        {
            return $this->errcode;
        }

        /**
         * 从远端接口获取ACCESS_TOKEN, 有效期为2个小时
         * @author 、lin
         */
        /**
         * 从远端接口获取ACCESS_TOKEN, 有效期为2个小时
         *
         * @param bool $is_refresh 是否刷新
         *
         * @return mixed
         */
        protected function _getAccessToken($is_refresh = FALSE)
        {
            if(!$is_refresh) {
                $this->access_token = cache(md5($this->appid . '_' . $this->secret));

                if(!empty($this->access_token)){
                    return $this->access_token;
                }
            }

            $params = array(
                'grant_type' => 'client_credential',
                'appid' => $this->appid,
                'secret' => $this->secret,
            );
            $jsonArr = $this->_http(self::AUTH_URL, $params);
            if(isset($jsonArr['access_token'])) {
                $this->access_token = $jsonArr['access_token'];
                //存到缓存中
                cache(md5($this->appid . '_' . $this->secret), $this->access_token, 2 * 60 * 60 - 60);

                return $this->access_token;
            } else {
                return FALSE;
            }
        }

        /**
         * 验证AccessToken是否有效, 验证不通过则重新获取
         *
         */
        protected function _validAccessToken()
        {
            $result = $this->getcallbackip();
            if($result === FALSE)
            {
                $this->getToken(TRUE);
            }
        }

        /**
         * 发送HTTP请求方法，目前只支持CURL发送请求
         *
         * @param  string  $url    请求URL
         * @param  array   $params 请求参数
         * @param  string  $method 请求方法GET/POST
         * @param  boolean $ssl    是否进行SSL双向认证
         *
         * @return array   $data   响应数据
         * @author 、lin
         */
        protected function _http($url, $params = array(), $method = 'GET', $ssl = FALSE)
        {
            if($method != 'GET' && !empty($params)) $params = json_encode($params, JSON_UNESCAPED_UNICODE);
            $opts = array(
                CURLOPT_TIMEOUT => 30,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_SSL_VERIFYHOST => FALSE,
            );
            /* 根据请求类型设置特定参数 */
            switch(strtoupper($method)) {
                case 'GET':
                    $getQuerys = !empty($params) ? '?' . http_build_query($params) : '';
                    $opts[CURLOPT_URL] = $url . $getQuerys;
                    break;
                case 'POST':
                    $opts[CURLOPT_URL] = $url;
                    $opts[CURLOPT_POST] = 1;
                    $opts[CURLOPT_POSTFIELDS] = $params;
                    break;
            }
            if($ssl) {
                $pemPath = dirname(__FILE__) . '/Wechat/';
                $pemCret = $pemPath . $this->pem . '_cert.pem';
                $pemKey = $pemPath . $this->pem . '_key.pem';
                if(!file_exists($pemCret)) {
                    $this->error = '证书不存在';

                    return FALSE;
                }
                if(!file_exists($pemKey)) {
                    $this->error = '密钥不存在';

                    return FALSE;
                }
                $opts[CURLOPT_SSLCERTTYPE] = 'PEM';
                $opts[CURLOPT_SSLCERT] = $pemCret;
                $opts[CURLOPT_SSLKEYTYPE] = 'PEM';
                $opts[CURLOPT_SSLKEY] = $pemKey;
            }
            /* nodejs 控制台输出日志 */
            //$CSdata = ($method == 'POST' ? json_decode($params, true) : '');
            //halt($opts);
            /* 初始化并执行curl请求 */
            $ch = curl_init();
            curl_setopt_array($ch, $opts);
            $data = curl_exec($ch);
            $err = curl_errno($ch);
            $errmsg = curl_error($ch);
            curl_close($ch);
            if($err > 0) {
                $this->error = $errmsg;

                return FALSE;
            } else {
                return $this->_parseJson($data);
            }
        }

        /**
         * 不转义中文字符和\/的 json 编码方法  TODO: 暂时作废,以后用不到就删除
         *
         * @param  array $array
         *
         * @return json
         * @author 、lin
         */
        protected function _jsonEncode($array = array())
        {
            $array = str_replace("\\/", "/", json_encode($array));
            $search = '#\\\u([0-9a-f]+)#i';
            if(strpos(strtoupper(PHP_OS), 'WIN') === FALSE) {
                $replace = "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))";//LINUX
            } else {
                $replace = "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))";//WINDOWS
            }

            return preg_replace($search, $replace, $array);
        }

        /**
         * 解析JSON编码，如果有错误，则返回错误并设置错误信息d
         *
         * @param json $json json数据
         *
         * @return array
         * @author 、lin
         */
        protected function _parseJson($json)
        {
            $jsonArr = json_decode($json, TRUE);
            if(isset($jsonArr['errcode'])) {
                if($jsonArr['errcode'] == 0) {
                    return $jsonArr;
                } else {
                    $this->errcode = $jsonArr['errcode'];
                    $this->error = $this->_errorCode($jsonArr['errcode']);

                    return FALSE;
                }
            } else {
                return $jsonArr;
            }
        }

        /**
         * 检查公众号配置签名信息
         *
         * @author 、lin
         */
        private function _checkSignature()
        {
            //如果调试状态，直接返回真
            if($this->debug) return TRUE;
            $signature = input('signature');
            $timestamp = input('timestamp');
            $nonce = input('nonce');
            if(empty($signature) || empty($timestamp) || empty($nonce)) {
                return FALSE;
            }
            $token = $this->token;
            if(!$token) return FALSE;
            $tmpArr = array($token, $timestamp, $nonce);
            sort($tmpArr, SORT_STRING);
            $tmpStr = implode($tmpArr);

            return sha1($tmpStr) == $signature;
        }

        /**
         * 获取全局返回错误码
         *
         * @param integer $code 错误码
         *
         * @return string 错误信息
         * @author 、lin
         */
        protected function _errorCode($code)
        {
            switch($code) {
                case -1    :
                    return '系统繁忙，此时请开发者稍候再试 ';
                case 0    :
                    return '请求成功';
                case 40001 :
                    return '获取 access_token 时 AppSecret 错误，或者 access_token 无效。请开发者认真比对 AppSecret 的正确性，或查看是否正在为恰当的公众号调用接口';
                case 40002 :
                    return '不合法的凭证类型';
                case 40003 :
                    return '不合法的 OpenID ，请开发者确认 OpenID （该用户）是否已关注公众号，或是否是其他公众号的 OpenID';
                case 40004 :
                    return '不合法的媒体文件类型';
                case 40005 :
                    return '不合法的文件类型';
                case 40006 :
                    return '不合法的文件大小';
                case 40007 :
                    return '不合法的媒体文件id ';
                case 40008 :
                    return '不合法的消息类型 ';
                case 40009 :
                    return '不合法的图片文件大小';
                case 40010 :
                    return '不合法的语音文件大小';
                case 40011 :
                    return '不合法的视频文件大小';
                case 40012 :
                    return '不合法的缩略图文件大小';
                case 40013 :
                    return '不合法的 AppID ，请开发者检查 AppID 的正确性，避免异常字符，注意大小写';
                case 40014 :
                    return '不合法的 access_token ，请开发者认真比对 access_token 的有效性（如是否过期），或查看是否正在为恰当的公众号调用接口';
                case 40015 :
                    return '不合法的菜单类型 ';
                case 40016 :
                    return '不合法的按钮个数 ';
                case 40017 :
                    return '不合法的按钮个数';
                case 40018 :
                    return '不合法的按钮名字长度';
                case 40019 :
                    return '不合法的按钮KEY长度 ';
                case 40020 :
                    return '不合法的按钮URL长度 ';
                case 40021 :
                    return '不合法的菜单版本号';
                case 40022 :
                    return '不合法的子菜单级数';
                case 40023 :
                    return '不合法的子菜单按钮个数';
                case 40024 :
                    return '不合法的子菜单按钮类型';
                case 40025 :
                    return '不合法的子菜单按钮名字长度';
                case 40026 :
                    return '不合法的子菜单按钮KEY长度 ';
                case 40027 :
                    return '不合法的子菜单按钮URL长度 ';
                case 40028 :
                    return '不合法的自定义菜单使用用户';
                case 40029 :
                    return '不合法的oauth_code';
                case 40030 :
                    return '不合法的refresh_token';
                case 40031 :
                    return '不合法的openid列表 ';
                case 40032 :
                    return '不合法的openid列表长度 ';
                case 40033 :
                    return '不合法的请求字符，不能包含\uxxxx格式的字符 ';
                case 40035 :
                    return '不合法的参数';
                case 40038 :
                    return '不合法的请求格式';
                case 40039 :
                    return '不合法的URL长度 ';
                case 40050 :
                    return '不合法的分组id';
                case 40051 :
                    return '分组名字不合法';
                case 40060 :
                    return '删除单篇图文时，指定的 article_idx 不合法';
                case 40117 :
                    return '分组名字不合法';
                case 40118 :
                    return 'media_id 大小不合法';
                case 40119 :
                    return 'button 类型错误';
                case 40120 :
                    return 'button 类型错误';
                case 40121 :
                    return '不合法的 media_id 类型';
                case 40132 :
                    return '微信号不合法';
                case 40137 :
                    return '不支持的图片格式';
                case 40155 :
                    return '请勿添加其他公众号的主页链接';
                case 40164 :
                    return '您的服务器IP不在: 公众号->基本配置->IP白名单。请添加';
                case 41001 :
                    return '缺少access_token参数';
                case 41002 :
                    return '缺少appid参数';
                case 41003 :
                    return '缺少refresh_token参数';
                case 41004 :
                    return '缺少secret参数';
                case 41005 :
                    return '缺少多媒体文件数据';
                case 41006 :
                    return '缺少media_id参数';
                case 41007 :
                    return '缺少子菜单数据';
                case 41008 :
                    return '缺少oauth code';
                case 41009 :
                    return '缺少openid';
                case 42001 :
                    return 'access_token 超时，请检查 access_token 的有效期，请参考基础支持 - 获取 access_token 中，对 access_token 的详细机制说明';
                case 42002 :
                    return 'refresh_token超时';
                case 42003 :
                    return 'oauth_code超时';
                case 42007 :
                    return '用户修改微信密码， accesstoken 和 refreshtoken 失效，需要重新授权';
                case 43001 :
                    return '需要GET请求';
                case 43002 :
                    return '需要POST请求';
                case 43003 :
                    return '需要HTTPS请求';
                case 43004 :
                    return '需要接收者关注';
                case 43005 :
                    return '需要好友关系';
                case 44001 :
                    return '多媒体文件为空';
                case 44002 :
                    return 'POST的数据包为空';
                case 44003 :
                    return '图文消息内容为空';
                case 44004 :
                    return '文本消息内容为空';
                case 45001 :
                    return '多媒体文件大小超过限制';
                case 45002 :
                    return '消息内容超过限制';
                case 45003 :
                    return '标题字段超过限制';
                case 45004 :
                    return '描述字段超过限制';
                case 45005 :
                    return '链接字段超过限制';
                case 45006 :
                    return '图片链接字段超过限制';
                case 45007 :
                    return '语音播放时间超过限制';
                case 45008 :
                    return '图文消息超过限制';
                case 45009 :
                    return '接口调用超过限制';
                case 45010 :
                    return '创建菜单个数超过限制';
                case 45011 :
                    return 'API 调用太频繁，请稍候再试';
                case 45015 :
                    return '回复时间超过限制';
                case 45016 :
                    return '系统分组，不允许修改';
                case 45017 :
                    return '分组名字过长';
                case 45018 :
                    return '分组数量超过上限';
                case 45047 :
                    return '客服接口下行条数超过上限';
                case 46001 :
                    return '不存在媒体数据';
                case 46002 :
                    return '不存在的菜单版本';
                case 46003 :
                    return '不存在的菜单数据';
                case 46004 :
                    return '不存在的用户';
                case 47001 :
                    return '解析JSON/XML内容错误';
                case 48001 :
                    return 'api 功能未授权，请确认公众号已获得该接口，可以在公众平台官网 - 开发者中心页中查看接口权限';
                case 48002 :
                    return '粉丝拒收消息（粉丝在公众号选项中，关闭了 “ 接收消息 ” ）';
                case 48004 :
                    return 'api 接口被封禁，请登录 mp.weixin.qq.com 查看详情';
                case 48005 :
                    return 'api 禁止删除被自动回复和自定义菜单引用的素材';
                case 48006 :
                    return 'api 禁止清零调用次数，因为清零次数达到上限';
                case 48008 :
                    return '没有该类型消息的发送权限';
                case 50001 :
                    return '用户未授权该api';
                case 50002 :
                    return '用户受限，可能是违规后接口被封禁';
                case 61451 :
                    return '参数错误 (invalid parameter)';
                case 61452 :
                    return '无效客服账号 (invalid kf_account)';
                case 61453 :
                    return '客服帐号已存在 (kf_account exsited)';
                case 61454 :
                    return '客服帐号名长度超过限制 ( 仅允许 10 个英文字符，不包括 @ 及 @ 后的公众号的微信号 )(invalid kf_acount length)';
                case 61455 :
                    return '客服帐号名包含非法字符 ( 仅允许英文 + 数字 )(illegal character in kf_account)';
                case 61456 :
                    return '客服帐号个数超过限制 (10 个客服账号 )(kf_account count exceeded)';
                case 61457 :
                    return '无效头像文件类型 (invalid file type)';
                case 61450 :
                    return '系统错误 (system error)';
                case 61500 :
                    return '日期格式错误';
                case 65301 :
                    return '不存在此 menuid 对应的个性化菜单';
                case 65302 :
                    return '没有相应的用户';
                case 65303 :
                    return '没有默认菜单，不能创建个性化菜单';
                case 65304 :
                    return 'MatchRule 信息为空';
                case 65305 :
                    return '个性化菜单数量受限';
                case 65306 :
                    return '不支持个性化菜单的帐号';
                case 65307 :
                    return '个性化菜单信息为空';
                case 65308 :
                    return '包含没有响应类型的 button';
                case 65309 :
                    return '个性化菜单开关处于关闭状态';
                case 65310 :
                    return '填写了省份或城市信息，国家信息不能为空';
                case 65311 :
                    return '填写了城市信息，省份信息不能为空';
                case 65312 :
                    return '不合法的国家信息';
                case 65313 :
                    return '不合法的省份信息';
                case 65314 :
                    return '不合法的城市信息';
                case 65316 :
                    return '该公众号的菜单设置了过多的域名外跳（最多跳转到 3 个域名的链接）';
                case 65317 :
                    return '不合法的 URL';
                case 9001001 :
                    return 'POST 数据参数不合法';
                case 9001002 :
                    return '远端服务不可用';
                case 9001003 :
                    return 'Ticket 不合法';
                case 9001004 :
                    return '获取摇周边用户信息失败';
                case 9001005 :
                    return '获取商户信息失败';
                case 9001006 :
                    return '获取 OpenID 失败';
                case 9001007 :
                    return '上传文件缺失';
                case 9001008 :
                    return '上传素材的文件类型不合法';
                case 9001009 :
                    return '上传素材的文件尺寸不合法';
                case 9001010 :
                    return '上传失败';
                case 9001020 :
                    return '帐号不合法';
                case 9001021 :
                    return '已有设备激活率低于 50% ，不能新增设备';
                case 9001022 :
                    return '设备申请数不合法，必须为大于 0 的数字';
                case 9001023 :
                    return '已存在审核中的设备 ID 申请';
                case 9001024 :
                    return '一次查询设备 ID 数量不能超过 50';
                case 9001025 :
                    return '设备 ID 不合法';
                case 9001026 :
                    return '页面 ID 不合法';
                case 9001027 :
                    return '页面参数不合法';
                case 9001028 :
                    return '一次删除页面 ID 数量不能超过 10';
                case 9001029 :
                    return '页面已应用在设备中，请先解除应用关系再删除';
                case 9001030 :
                    return '一次查询页面 ID 数量不能超过 50';
                case 9001031 :
                    return '时间区间不合法';
                case 9001032 :
                    return '保存设备与页面的绑定关系参数错误';
                case 9001033 :
                    return '门店 ID 不合法';
                case 9001034 :
                    return '设备备注信息过长';
                case 9001035 :
                    return '设备申请参数不合法';
                case 9001036 :
                    return '查询起始值 begin 不合法';
                default    :
                    return '未知错误';
            }
        }
    }