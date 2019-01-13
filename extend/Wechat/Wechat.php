<?php
/**
 * 微信PHP-SDK
 * 服务器端必须要有 CURL 支持
 * 2015年7月修正版本
 * @author 、lin
 * https://coding.net/u/cjango/p/wechat_sdk/git
 * 7月10日，完善红包功能，
 */

namespace Wechat;

class Wechat
{
    /*JS-SDK使用权限签名算法*/
    const JS_SDK_URL = 'http://mp.weixin.qq.com';
    /* 用户及用户分组URL */
    const USER_GET_URL = 'https://api.weixin.qq.com/cgi-bin/user/get';
    const USER_INFO_URL = 'https://api.weixin.qq.com/cgi-bin/user/info';
    const USER_IN_GROUP = 'https://api.weixin.qq.com/cgi-bin/groups/getid';
    const GROUP_GET_URL = 'https://api.weixin.qq.com/cgi-bin/groups/get';
    const GROUP_CREATE_URL = 'https://api.weixin.qq.com/cgi-bin/groups/create';
    const GROUP_UPDATE_URL = 'https://api.weixin.qq.com/cgi-bin/groups/update';
    const GROUP_MEMBER_UPDATE_URL = 'https://api.weixin.qq.com/cgi-bin/groups/members/update';
    /* 发送客服消息URL */
    const CUSTOM_SEND_URL = 'https://api.weixin.qq.com/cgi-bin/message/custom/send';
    /* 二维码生成 URL*/
    const QRCODE_URL = 'https://api.weixin.qq.com/cgi-bin/qrcode/create';
    const QRCODE_SHOW_URL = 'https://mp.weixin.qq.com/cgi-bin/showqrcode';
    /* OAuth2.0授权地址 */
    const OAUTH_AUTHORIZE_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    const OAUTH_USER_TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    const OAUTH_GET_USERINFO = 'https://api.weixin.qq.com/cgi-bin/user/info';
    /* 消息模板 */
    const TEMPLATE_SEND = 'https://api.weixin.qq.com/cgi-bin/message/template/send';
    /* JSAPI_TICKET获取地址 */
    const JSAPI_TICKET_URL = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';
    /* 统一下单地址 */
    const UNIFIED_ORDER_URL = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    /* 订单状态查询 */
    const ORDER_QUERY_URL = 'https://api.mch.weixin.qq.com/pay/orderquery';
    /* 关闭订单 */
    const CLOSE_ORDER_URL = 'https://api.mch.weixin.qq.com/pay/closeorder';
    /* 退款地址 需要证书*/
    const PAY_REFUND_ORDER = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
    /* 退款查询地址 */
    const REFUND_QUERY_URL = 'https://api.mch.weixin.qq.com/pay/refundquery';
    /* 下载对账单 */
    const DOWNLOAD_BILL_URL = 'https://api.mch.weixin.qq.com/pay/downloadbill';
    /* 转换短链接 */
    const GET_SHORT_URL = 'https://api.mch.weixin.qq.com/tools/shorturl';
    /* 发放红包高级接口 */
    const SEND_RED_PACK = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
    /* 发送裂变红包接口 */
    const SEND_GROUP_RED_PACK = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendgroupredpack';
    /* 红包查询接口 */
    const GET_RED_PACK_INFO = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gethbinfo';
    /* 素材管理 */
    const MEDIA_UPLOAD_URL = 'https://api.weixin.qq.com/cgi-bin/media/upload';               // 新增临时素材
    const MEDIA_GET_URL = 'https://api.weixin.qq.com/cgi-bin/media/get';                  // 获取临时素材
    const MATERIAL_NEWS_URL = 'https://api.weixin.qq.com/cgi-bin/material/add_news';          // 新增永久图文素材
    const MATERIAL_MATERIAL_URL = 'https://api.weixin.qq.com/cgi-bin/material/add_material';      // 新增永久素材
    const MATERIAL_GET_URL = 'https://api.weixin.qq.com/cgi-bin/material/get_material';      // 获取永久素材 1
    const MATERIAL_DEL_URL = 'https://api.weixin.qq.com/cgi-bin/material/del_material';      // 删除永久素材 1
    const MATERIAL_UPDATE_URL = 'https://api.weixin.qq.com/cgi-bin/material/update_news';       // 修改永久图文素材
    const MATERIAL_COUNT_URL = 'https://api.weixin.qq.com/cgi-bin/material/get_materialcount'; // 获取永久素材数量 1
    const MATERIAL_LISTS_URL = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material'; // 获取永久素材列表 1

    /**
     * 从远端获取用户分组
     *
     * @return array|boolean
     * @author 、lin
     */
    public function getUserGroup()
    {
        $url = self::GROUP_GET_URL . '?access_token=' . $this->_getAccessToken();
        return $this->_http($url);
    }

    /**
     * 添加用户分组
     *
     * @param string $group_name 分组名称
     * @return boolean
     */
    public function addUserGroup($group_name)
    {
        $params = array(
            'group' => array(
                'name' => $group_name
            )
        );

        $url = self::GROUP_CREATE_URL . '?access_token=' . $this->_getAccessToken();
        return $this->_http($url, $params, 'POST');
    }

    /**
     * 修改分组名
     * @param integer $group_id 分组编号
     * @param string $group_name 分组名称
     * @return boolean
     */
    public function editUserGroup($group_id, $group_name)
    {
        $params = array(
            'group' => array(
                'id' => $group_id,
                'name' => $group_name
            )
        );
        $url = self::GROUP_UPDATE_URL . '?access_token=' . $this->_getAccessToken();
        return $this->_http($url, $params, 'POST');
    }

    /**
     * 获取关注者列表
     * @param  sting $next_openid 第一个拉取的OPENID，不填默认从头开始拉取
     * @return array|boolean 返回用户信息的一个数组
     * @author 、lin
     */
    public function getUserList($next_openid)
    {
        $params['next_openid'] = $next_openid;
        $params['access_token'] = $this->_getAccessToken();

        return $this->_http(self::USER_GET_URL, $params);
    }

    /**
     * 获取用户基本信息
     * @param  string $openid 用户的OPENID
     * @return array|boolean  返回用户信息的一个数组
     * @author 、lin
     */
    public function getUserInfo($openid)
    {
        $params = array(
            'access_token' => $this->_getAccessToken(),
            'lang' => 'zh_CN',
            'openid' => $openid
        );
        return $this->_http(self::USER_INFO_URL, $params);
    }

    /**
     * 查询用户所在分组
     * @param  string $openid 用户OPENID
     * @return integer|boolean 用户所在分组ID
     */
    public function getUserInGroup($openid)
    {
        $params = array(
            'openid' => $openid
        );
        $url = self::USER_IN_GROUP . '?access_token=' .$this->_getAccessToken();
        return $this->_http($url, $params, 'POST');
    }

    /**
     * 移动用户分组
     * @param string $openid 用户OPENID
     * @param integer $group_id 移动到的分组编号
     * @return boolean
     */
    public function moveUserToGroup($openid, $group_id)
    {
        $params = array(
            'openid' => $openid,
            'to_groupid' => $group_id
        );
        $url = self::GROUP_MEMBER_UPDATE_URL . '?access_token=' . $this->_getAccessToken();
        return $this->_http($url, $params, 'POST');
    }

    /**
     * * 被动响应微信发送的信息（自动回复）
     * @param  string $to 接收用户名
     * @param  string $from 发送者用户名
     * @param  array $content 回复信息，文本信息为string类型
     * @param  string $type 消息类型
     * @param  string $flag 是否新标刚接受到的信息
     * @return string          XML字符串
     * @author 、lin
     */
    public function response($content, $type = 'text', $flag = 0)
    {
        /* 基础数据 */
        $this->data = array(
            'ToUserName' => $this->data['fromusername'],
            'FromUserName' => $this->data['tousername'],
            'CreateTime' => time(),
            'MsgType' => $type,
            'Content' => $type,
        );
        /* 添加类型数据 */
        $this->data['text'] = $content;
        /* 添加状态 */
        $this->data['FuncFlag'] = $flag;
        /* 转换数据为XML */
        $response = $this->_data2xml($this->data);
        if ($this->encode) {
            $nonce = $_GET['nonce'];
            $xmlStr['Encrypt'] = $this->AESencode($response);
            $xmlStr['MsgSignature'] = self::getSHA1($xmlStr['Encrypt'], $nonce);
            $xmlStr['TimeStamp'] = time();
            $xmlStr['Nonce'] = $nonce;
            $response = '';
            $response = self::_array2Xml($xmlStr);
        }
        exit($response);
    }

    /**
     * JS-SDK使用权限签名算法
     * @param type $jsapi_ticket
     * @param type $timestamp 时间戳
     * @param type $url 当前网页的URL，不包含#及其后面部分
     * @param type $noncestr 随机字符串
     */
    public function getJSSDKSHA1($jsapi_ticket, $timestamp, $noncestr)
    {
        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $array = array('jsapi_ticket=' . $jsapi_ticket, 'noncestr=' . $noncestr, 'timestamp=' . $timestamp, 'url=' . $url);
        $str = implode('&', $array);

        return sha1($str);
    }




    /**
     * OAuth 授权跳转接口
     * @param string $callback 回调URI，填写完整地址，带http://
     * @param sting $state 重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值
     * @param string snsapi_userinfo获取用户授权信息，snsapi_base直接返回openid
     * @return string
     * @author 、lin
     */
    public function getOAuthRedirect($callback, $state = '', $scope = 'snsapi_base')
    {
        return self::OAUTH_AUTHORIZE_URL . '?appid=' . $this->appid . '&redirect_uri=' . urlencode($callback) . '&response_type=code&scope=' . $scope . '&state=' . $state . '#wechat_redirect';
    }

    /**
     * 通过code获取Access Token
     * @return array|boolean
     * @author 、lin
     */
    public function getOauthAccessToken()
    {
        $code = isset($_GET['code']) ? $_GET['code'] : '';
        if (!$code) return false;
        $params = array(
            'appid' => $this->appid,
            'secret' => $this->secret,
            'code' => $code,
            'grant_type' => 'authorization_code'
        );
        $jsonStr = $this->_http(self::OAUTH_USER_TOKEN_URL, $params);
        $jsonArr = $this->_parseJson($jsonStr);
        if ($jsonArr) {
            return $jsonArr;
        } else {
            return false;
        }
    }

    /**
     * 网页生成支付URL
     * @param  integer $product_id
     * @param  string $orderId
     * @param  float $money
     * @param  string $body
     * @param  string $notify_url
     * @return string  URL
     */
    public function webUnifiedOrder($product_id, $orderId, $money, $body, $notify_url = '', $extend = array())
    {
        if (strlen($body) > 127) $body = substr($body, 0, 127);
        $params = array(
            'appid' => $this->appid,
            'mch_id' => $this->mch_id,
            'nonce_str' => self::_getRandomStr(),
            'body' => $body,
            'out_trade_no' => $orderId,
            'total_fee' => $money * 100, // 转换成分
            'spbill_create_ip' => get_client_ip(),
            'notify_url' => $notify_url,
            'product_id' => $product_id,
            'trade_type' => 'NATIVE',
        );
        if (is_string($extend)) {
            $params['attach'] = $extend;
        } elseif (is_array($extend) && !empty($extend)) {
            $params = array_merge($params, $extend);
        }
        $params['sign'] = self::_getOrderMd5($params);
        $data = self::_array2Xml($params);
        $data = $this->_http(self::UNIFIED_ORDER_URL, $data, 'POST');
        $data = self::_extractXml($data);
        if ($data) {
            if ($data['return_code'] == 'SUCCESS') {
                if ($data['result_code'] == 'SUCCESS') {
                    return $data['code_url'];
                } else {
                    $this->error = $data['err_code'];
                    return false;
                }
            } else {
                $this->error = $data['return_msg'];
                return false;
            }
        } else {
            $this->error = '创建订单失败';
            return false;
        }

    }

    /**
     * 统一下单接口生成支付请求
     * @param  $openid      string  用户OPENID相对于当前公众号
     * @param  $body        string  商品描述 少于127字节
     * @param  $orderId     string  系统中唯一订单号
     * @param  $money       integer 支付金额
     * @param  $notify_url  string  通知URL
     * @param  $extend      array|string   扩展参数
     * @return json|boolean json 直接可赋给JSAPI接口使用，boolean错误
     */
    public function unifiedOrder($openid, $body, $orderId, $money, $notify_url = '', $extend = array())
    {
        if (strlen($body) > 127) $body = substr($body, 0, 127);
        $params = array(
            'openid' => (string)$openid,
            'appid' => $this->appid,
            'mch_id' => $this->mch_id,
            'nonce_str' => self::_getRandomStr(),
            'body' => $body,
            'out_trade_no' => $orderId,
            'total_fee' => $money * 100, // 转换成分
            'spbill_create_ip' => get_client_ip(),
            'notify_url' => $notify_url,
            'trade_type' => 'JSAPI',
        );
        if (is_string($extend)) {
            $params['attach'] = $extend;
        } elseif (is_array($extend) && !empty($extend)) {
            $params = array_merge($params, $extend);
        }
        // 生成签名
        $params['sign'] = self::_getOrderMd5($params);
        $data = self::_array2Xml($params);
        $data = $this->_http(self::UNIFIED_ORDER_URL, $data, 'POST');
        $data = self::_extractXml($data);
        if ($data) {
            if ($data['return_code'] == 'SUCCESS') {
                if ($data['result_code'] == 'SUCCESS') {
                    return $this->_createPayParams($data['prepay_id']);
                } else {
                    $this->error = $data['err_code'];
                    return false;
                }
            } else {
                $this->error = $data['return_msg'];
                return false;
            }
        } else {
            $this->error = '创建订单失败';
            return false;
        }
    }

    /**
     * 获取jsapi_ticket
     */
    public function getJsapiTicket()
    {

        $params = array(
            'access_token' => $this->access_token,
            'type' => 'jsapi'
        );
        $jsonStr = $this->_http(self::JSAPI_TICKET_URL, $params);

        $jsonArr = $this->_parseJson($jsonStr);
        if ($jsonArr) {
            return $this->result['ticket'];
        } else {
            return false;
        }
    }

    /**
     * 获取二维码图像地址
     * @param  integer $scene_id 场景值 1-100000整数
     * @param  boolean $limit true永久二维码 false 临时
     * @param  integer $expire 临时二维码有效时间
     * @return string|boolean    二维码图片地址
     * @author 、lin
     */
    public function getQRUrl($scene_id = '', $limit = true, $expire = 1800)
    {
        if (!isset($this->ticket)) {
            if (!$this->qrcode($scene_id, $limit, $expire)) return false;
        }
        return self::QRCODE_SHOW_URL . '?ticket=' . $this->ticket;
    }

    /**
     * 生成推广二维码
     * @param  integer $scene_id 场景值 1-100000整数
     * @param  boolean $limit true永久二维码 false 临时
     * @param  integer $expire 临时二维码有效时间
     * @return string|boolean
     * @author 、lin
     */
    private function qrcode($scene_id = '', $limit = true, $expire = 1800)
    {
        if (empty($scene_id) || !is_numeric($scene_id) || $scene_id > 100000 || $scene_id < 1) {
            $this->error = '场景值必须是1-100000之间的整数';
            return false;
        }
        $params['action_name'] = $limit ? 'QR_LIMIT_SCENE' : 'QR_SCENE';
        if (!$limit) $params['expire_seconds'] = $expire;
        $params['action_info'] = array('scene' => array('scene_id' => $scene_id));
        $params = json_encode($params);
        $url = self::QRCODE_URL . '?access_token=' . $this->access_token;
        $jsonStr = $this->_http($url, $params, 'POST');
        $jsonArr = $this->_parseJson($jsonStr);
        if ($jsonArr) {
            return $this->ticket = $jsonArr['ticket'];
        } else {
            return false;
        }
    }

    //取得用户TOKEN
    public function code2accesstoken($code)
    {
        return self::OAUTH_USER_TOKEN_URL . '?appid=' . $this->appid . '&secret=' . $this->secret . '&code=' . $code . '&grant_type=authorization_code';
    }

    /**
     * @param appid     是     公众号的唯一标识
     * @param redirect_uri     是     授权后重定向的回调链接地址，请使用urlencode对链接进行处理
     * @param response_type     是     返回类型，请填写code
     * @param scope     是     应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），snsapi_userinfo （未关注也可以得到信息）
     * @param state     否     重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值
     * @param fun     授权成功以后的地址
     * #wechat_redirect     是     无论直接打开还是做页面302重定向时候，必须带此参数
     */
    public function oauth2($userid = '', $scope = 'snsapi_base', $fun)
    {
        $arr = array(
            "appid" => $this->appid,
            "redirect_uri" => 'http://wx.cnskl.com/' . $fun,
            "response_type" => 'code',
            "scope" => $scope,
            'state' => $userid
        );
        return self::OAUTH_AUTHORIZE_URL . '?' . http_build_query($arr) . '#wechat_redirect';
    }

    /**
     * 生成一个20位的订单号,最好是使用1位的前缀
     * @param  string $prefix 订单号前缀，区分业务类型
     * @return string
     */
    public static function createOrderId($prefix = '')
    {
        $code = date('ymdHis') . sprintf("%08d", mt_rand(1, 99999999));
        if (!empty($prefix)) {
            $code = $prefix . substr($code, strlen($prefix));
        }
        return $code;
    }

    /**
     * 返回随机填充的字符串
     */
    private function _getRandomStr($lenght = 16)
    {
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        return substr(str_shuffle($str_pol), 0, $lenght);
    }

    /**
     * 生成支付参数
     */
    private function _createPayParams($prepay_id)
    {
        if (empty($prepay_id)) {
            $this->error = 'prepay_id参数错误';
            return false;
        }
        $params['appId'] = $this->appid;
        $params['timeStamp'] = (string)NOW_TIME;
        $params['nonceStr'] = self::_getRandomStr();
        $params['package'] = 'prepay_id=' . $prepay_id;
        $params['signType'] = 'MD5';
        $params['paySign'] = self::_getOrderMd5($params);
        return json_encode($params);
    }

    /**
     * 查询订单
     * @return boolean|array
     */
    public function getOrderInfo($orderId, $type = 0)
    {
        $params['appid'] = $this->appid;
        $params['mch_id'] = $this->mch_id;
        if ($type == 1) {
            $params['transaction_id'] = $orderId;
        } else {
            $params['out_trade_no'] = $orderId;
        }
        $params['nonce_str'] = self::_getRandomStr();
        $params['sign'] = self::_getOrderMd5($params);
        $data = self::_array2Xml($params);
        $data = $this->_http(self::ORDER_QUERY_URL, $data, 'POST');
        return self::parsePayRequest($data);
    }

    /**
     * 关闭订单
     * @return boolean|array
     */
    public function closeOrder($orderId)
    {
        $params['appid'] = $this->appid;
        $params['mch_id'] = $this->mch_id;
        $params['out_trade_no'] = $orderId;
        $params['nonce_str'] = self::_getRandomStr();
        $params['sign'] = self::_getOrderMd5($params);
        $data = self::_array2Xml($params);
        $data = $this->_http(self::CLOSE_ORDER_URL, $data, 'POST');
        return self::parsePayRequest($data);
    }

    /**
     * 申请退款 需要证书操作
     * @return boolean|array
     */
    public function refundOrder($orderId, $refundId, $total_fee, $refund_fee = '')
    {
        $params['appid'] = $this->appid;
        $params['mch_id'] = $this->mch_id;
        $params['nonce_str'] = self::_getRandomStr();
        $params['out_trade_no'] = $orderId;
        $params['out_refund_no'] = $refundId;
        $params['total_fee'] = $total_fee;
        $params['refund_fee'] = $refund_fee;
        $params['op_user_id'] = $this->mch_id;
        $params['sign'] = self::_getOrderMd5($params);
        $data = self::_array2Xml($params);
        $data = $this->_http(self::PAY_REFUND_ORDER, $data, 'POST', true);
        return self::parsePayRequest($data);
    }

    /**
     * 获取退款状态
     * @param  string $orderId 订单号
     * @return boolean|array
     */
    public function getRefundStatus($orderId)
    {
        $params['appid'] = $this->appid;
        $params['mch_id'] = $this->mch_id;
        $params['nonce_str'] = self::_getRandomStr();
        $params['out_trade_no'] = $orderId;
        $params['sign'] = self::_getOrderMd5($params);
        $data = self::_array2Xml($params);
        $data = $this->_http(self::REFUND_QUERY_URL, $data, 'POST');
        return self::parsePayRequest($data);
    }

    /**
     * 下载对账单
     * @param  date $date 20150710 对账单日期
     * @param  string $type ALL，返回所有(默认值) SUCCESS，成功支付 REFUND，退款订单 REVOKED，已撤销的订单
     * @return boolean|array
     */
    public function downloadBill($date = '', $type = 'ALL')
    {
        $date = $date ?: date('Ymd');
        $params['bill_date'] = $date;
        $params['bill_type'] = $type;
        $params['appid'] = $this->appid;
        $params['mch_id'] = $this->mch_id;
        $params['nonce_str'] = self::_getRandomStr();
        $params['sign'] = self::_getOrderMd5($params);

        $data = self::_array2Xml($params);
        $data = $this->_http(self::DOWNLOAD_BILL_URL, $data, 'POST');
        return self::parsePayRequest($data, false);
    }

    /**
     * 创建一个商户订单号
     * @return integer  28位订单号
     */
    private function createMchBillNo()
    {
        $micro = microtime(true) * 100;
        $micro = ceil($micro);
        $rand = substr($micro, -8) . \Util\StringSelf::randNumber(0, 99);
        return $this->mch_id . date('Ymd') . $rand;
    }

    /**
     * 发送分享红包
     * @param  string $openid 用户OPENID
     * @param  string $money 发送金额RMB元
     * @param  integer $num 裂变红包数量
     * @param  array $data 红包数据
     * @return boolean|array
     */
    public function sendGroupRedPack($openid, $money, $num = 1, $data)
    {
        $params['mch_billno'] = self::createMchBillNo();
        $params['send_name'] = $data['send_name'];
        $params['re_openid'] = (string)$openid;
        $params['total_amount'] = $money * 100;
        $params['total_num'] = $num;
        $params['amt_type'] = 'ALL_RAND';
        $params['wishing'] = $data['wishing'];
        $params['act_name'] = $data['act_name'];
        $params['remark'] = $data['remark'];
        $params['mch_id'] = $this->mch_id;
        $params['wxappid'] = $this->appid;
        $params['nonce_str'] = self::_getRandomStr();
        $params['sign'] = self::_getOrderMd5($params);
        $data = self::_array2Xml($params);
        $data = $this->_http(self::SEND_RED_PACK, $data, 'POST', true);
        return self::parsePayRequest($data, false);
    }

    /**
     * 发送红包接口
     * @param  string $openid 用户OPENID
     * @param  string $money 发送金额RMB元
     * @param  array $data 红包数据
     * @return boolean|array
     */
    public function sendRedPack($openid, $money, $data)
    {
        $params['mch_billno'] = self::createMchBillNo();
        $params['nick_name'] = $data['send_name'];
        $params['send_name'] = $data['send_name'];
        $params['re_openid'] = (string)$openid;
        $params['total_amount'] = $money * 100;
        $params['min_value'] = $money * 100;
        $params['max_value'] = $money * 100;
        $params['total_num'] = 1;
        $params['wishing'] = $data['wishing'];
        $params['act_name'] = $data['act_name'];
        $params['remark'] = $data['remark'];
        $params['client_ip'] = get_client_ip();
        $params['mch_id'] = $this->mch_id;
        $params['wxappid'] = $this->appid;
        $params['nonce_str'] = self::_getRandomStr();
        $params['sign'] = self::_getOrderMd5($params);
        $data = self::_array2Xml($params);
        $data = $this->_http(self::SEND_RED_PACK, $data, 'POST', true);
        return self::parsePayRequest($data, false);
    }

    /**
     * 获取红包信息
     * @param  string $billNo 商户发放红包的商户订单号
     * @return array
     */
    public function getRedPack($billNo)
    {
        $params['mch_billno'] = $billNo;
        $params['mch_id'] = $this->mch_id;
        $params['appid'] = $this->appid;
        $params['bill_type'] = 'MCHT';
        $params['nonce_str'] = self::_getRandomStr();
        $params['sign'] = self::_getOrderMd5($params);
        $data = self::_array2Xml($params);
        $data = $this->_http(self::GET_RED_PACK_INFO, $data, 'POST', true);
        return self::parsePayRequest($data, false);
    }

    /**
     * 解析支付接口的返回结果
     * @param  xmlstring $data 接口返回的数据
     * @param  boolean $checkSign 是否需要签名校验
     * @return boolean|array
     */
    private function parsePayRequest($data, $checkSign = true)
    {
        $data = self::_extractXml($data);
        if (empty($data)) {
            $this->error = '支付返回内容解析失败';
            return false;
        }
        if ($checkSign) {
            if (!self::_checkSign($data)) return false;
        }
        // 有返回结果 并且是SUCCESS的时候
        if ($data['return_code'] == 'SUCCESS') {
            if ($data['result_code'] == 'SUCCESS') {
                return $data;
            } else {
                $this->error = $data['err_code'];
                return false;
            }
        } else {
            $this->error = $data['return_msg'];
            return false;
        }
    }

    /**
     * 接口通知接收
     * @return array
     */
    public function getNotify()
    {
        $data = $GLOBALS["HTTP_RAW_POST_DATA"];
        return self::parsePayRequest($data);
    }

    /**
     * 对支付回调接口返回成功通知
     * @param  string $return_msg 错误信息
     * @return xmlstring
     */
    public function returnNotify($return_msg = true)
    {
        if ($return_msg == true) {
            $data = array(
                'return_code' => 'SUCCESS',
            );
        } else {
            $data = array(
                'return_code' => 'FAIL',
                'return_msg' => $return_msg
            );
        }
        exit(self::_array2Xml($data));
    }

    /**
     * 接收数据签名校验
     * @param  $data 接口返回的数据
     * @return boolean
     */
    private function _checkSign($data)
    {
        $sign = $data['sign'];
        unset($data['sign']);
        if (self::_getOrderMd5($data) != $sign) {
            $this->error = '签名校验失败';
            return false;
        } else {
            return true;
        }
    }

    /**
     * 本地MD5签名
     * @param  array $params 需要签名的数据
     * @return string        大写字母与数字签名（串32位）
     */
    private function _getOrderMd5($params)
    {
        ksort($params);
        $params['key'] = $this->payKey;
        return strtoupper(md5(urldecode(http_build_query($params))));
    }

    /**
     * AES 加密方法
     * @param  string $text 需要加密的字符串
     * @return boolean
     */
    private function _AESencode_demo($text)
    {
        $key = base64_decode($this->AESKey . "=");
        $random = $this->_getRandomStr();
        $text = $random . pack("N", strlen($text)) . $text . $this->appid;
        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        $iv = substr($key, 0, 16);
        // 使用自定义的填充方式对明文进行补位填充
        $text_length = strlen($text);
        //计算需要填充的位数
        $amount_to_pad = 32 - ($text_length % 32);
        if ($amount_to_pad == 0) {
            $amount_to_pad = 32;
        }
        //获得补位所用的字符
        $pad_chr = chr($amount_to_pad);
        $tmp = "";
        for ($index = 0; $index < $amount_to_pad; $index++) {
            $tmp .= $pad_chr;
        }
        $text = $text . $tmp;
        mcrypt_generic_init($module, $key, $iv);
        // 加密
        $encrypted = mcrypt_generic($module, $text);
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);
        // 使用BASE64对加密后的字符串进行编码
        return base64_encode($encrypted);
    }

    /**
     * AES 解密方法
     * @param  string $encrypted 加密后的字符串
     * @return xml|boolean
     */
    private function _AESdecode_demo($encrypted)
    {
        $key = base64_decode($this->AESKey . "=");
        // 使用BASE64对需要解密的字符串进行解码
        $ciphertext_dec = base64_decode($encrypted);
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        $iv = substr($key, 0, 16);
        mcrypt_generic_init($module, $key, $iv);
        // 解密
        $decrypted = mdecrypt_generic($module, $ciphertext_dec);
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);
        // 去除补位字符
        $pad = ord(substr($decrypted, -1));
        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }
        $result = substr($decrypted, 0, (strlen($decrypted) - $pad));
        // 去除16位随机字符串,网络字节序和AppId
        if (strlen($result) < 16) {
            $this->error = 'AESdecode Result Length Error';
            return false;
        }
        $content = substr($result, 16);
        $len_list = unpack("N", substr($content, 0, 4));
        $xml_len = $len_list[1];
        $xml_content = substr($content, 4, $xml_len);
        $from_appid = substr($content, $xml_len + 4);
        if ($from_appid != $this->appid) {
            $this->error = 'AESdecode AppId Error';
            return false;
        } else {
            return $this->_extractXml($xml_content);
        }
    }

    /**
     * 对数据进行SHA1签名
     */
    private function _getSHA1_demo($encrypt_msg, $nonce = '')
    {
        $array = array($encrypt_msg, $this->token, NOW_TIME, $nonce);
        sort($array, SORT_STRING);
        $str = implode($array);
        return sha1($str);
    }

}