<?php
    /**
 * Created by PhpStorm.
 * User: Darren
 * Date: 2018-04-14
 * Time: 18:48
 *
 *  微信被动回复用户消息管理
 */
namespace Wechat;

class ReceiveMessage extends Base
{
    //接收公众号数据
    private $receive_data = array();

    /**
     * 接收用户向微信公众号发送过来的消息
     *
     * 获取微信推送的数据,将键值全部转换为小写后返回
     * @return array 转换为数组后的数据
     */
    public function receiveMsg()
    {
        $postStr = file_get_contents("php://input", 'r');
        if (!empty($postStr)) {
            //调试状态记录用户向公众号发送的消息
            if($this->debug)
            {
                \think\Log::write($postStr, 'wechat_receiveMsg');
            }

            $data = $this->_extractXml($postStr);
            if ($this->encode) {
                $data = $this->_decryptMsg($data['encrypt']);
            }

            $this->receive_data = $data;
            return $data;
        } else {
            return false;
        }
    }

    /**
     * 回复文本消息
     *
     * @param $content 回复内容
     */
    public function textMsg($content)
    {
        /* 基础数据 */
        $reply_data = array(
            'ToUserName' => $this->receive_data['fromusername'],
            'FromUserName' => $this->receive_data['tousername'],
            'CreateTime' => time(),
            'MsgType' => 'text',
            'Content' => $content,
        );

        /* 转换数据为XML */
        $response = self::_array2Xml($reply_data);

        $this->_replyMsg($response);
    }

    /**
     * 回复图文消息
     *
     * @param $articles 消息列表
     *
     * @return bool
     *
     *  格式示例:
     *
     *  $articles = array(
     *      0 => array(
     *             'title' => '',
     *             'description' => '',
     *             'pic_url' => '',
     *             'url' => ''
     *      ),
     *      1 => array(
     *             'title' => '',
     *             'description' => '',
     *             'pic_url' => '',
     *             'url' => ''
     *      ),
     * );
     */
    public function newsMsg($articles)
    {
        if(empty($articles))
        {
            $this->error = '请传入图文消息';
            return FALSE;
        }

        $article_count = count($articles);

        if($article_count> 8)
        {
            $this->error = '图文消息条数，限制为8条以内';
            return FALSE;
        }

        //图文消息处理
        $articles_xml = '';
        foreach($articles as $k => $v)
        {
            $format = '<item>
                            <Title><![CDATA[%s]]></Title> 
                            <Description><![CDATA[%s]]></Description>
                            <PicUrl><![CDATA[%s]]></PicUrl>
                            <Url><![CDATA[%s]]></Url>
                        </item>';
            if(isset($v['title'], $v['description'], $v['pic_url'], $v['url']))
            {
                $articles_xml .= sprintf($format, $v['title'], $v['description'], $v['pic_url'], $v['url']);
            }
        }

        if(empty($articles_xml))
        {
            $this->error = '图文消息格式不正确';
            return FALSE;
        }

        $data_format = '<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%d</CreateTime><MsgType><![CDATA[news]]></MsgType><ArticleCount>%d</ArticleCount><Articles>'.$articles_xml.'</Articles></xml>';
        $data_xml = sprintf($data_format, $this->receive_data['fromusername'], $this->receive_data['tousername'], time(), $article_count);

        $this->_replyMsg($data_xml);
    }

    /**
     * * 被动响应微信发送的信息（自动回复）
     * @param  xml $response 发送的数据
     *
     * @return string          XML字符串
     * @author 、lin
     */
    private function _replyMsg($response)
    {
        if($this->debug)
        {
            \think\Log::write($response, 'wechat_replyMsg');
        }
        if ($this->encode) {
            $response = $this->_encryptMsg($response);
            if($this->debug)
            {
                \think\Log::write($response, 'wechat_encryptReplyMsg');
            }
            /*
            $nonce = $_GET['nonce'];
            $xmlStr['Encrypt'] = $this->_AESencode($response);
            $xmlStr['MsgSignature'] = $this->_getSHA1($xmlStr['Encrypt'], $nonce);
            $xmlStr['TimeStamp'] = time();
            $xmlStr['Nonce'] = $nonce;
            $response = self::_array2Xml($xmlStr);
            */
        }
        exit($response);
    }

    /**
     * 检验消息的真实性，并且获取解密后的明文.
     * <ol>
     *    <li>利用收到的密文生成安全签名，进行签名验证</li>
     *    <li>若验证通过，则提取xml中的加密消息</li>
     *    <li>对消息进行解密</li>
     * </ol>
     *
     * @param $postData     string 密文，对应POST请求的数据
     *
     * @return int 成功0，失败返回对应的错误码
     */
    private function _decryptMsg($encrypt)
    {
        if(strlen($this->AESKey) != 43)
        {
            $this->error = '消息加解密密钥(EncodingAESKey)不正确';
            return FALSE;
        }

        $msgSignature = input('signature');
        $timestamp = input('timestamp');
        $nonce = input('nonce');

        if($timestamp == NULL) {
            $timestamp = time();
        }

        //验证安全签名
        $signature = $this->_getSHA1($timestamp, $nonce, $encrypt);

        if(empty($signature)) {
            $this->error = 'SHA1算法生成安全签名错误';
            return FALSE;
        }

        if($signature != $msgSignature) {
            $this->error = '本地签名与公众号签名不一致';
            return FALSE;
        }

        $result = $this->_decrypt($encrypt);
        if(empty($result)) {
            $this->error = '消息解密失败';
            return FALSE;
        }

        return $result;
    }

    /**
     * 对密文进行解密
     * @param string $encrypted 需要解密的密文
     * @return string 解密得到的明文
     */
    private function _decrypt($encrypted)
    {

        try {
            //使用BASE64对需要解密的字符串进行解码
            $ciphertext_dec = base64_decode($encrypted);
            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
            $iv = substr($this->AESKey, 0, 16);
            mcrypt_generic_init($module, $this->AESKey, $iv);

            //解密
            $decrypted = mdecrypt_generic($module, $ciphertext_dec);
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
        } catch (Exception $e) {
            return NULL;
        }

        try {
            //去除补位字符
            $result = $this->_decode($decrypted);

            //去除16位随机字符串,网络字节序和AppId
            if (strlen($result) < 16) return "";

            $content = substr($result, 16, strlen($result));
            $len_list = unpack("N", substr($content, 0, 4));
            $xml_len = $len_list[1];
            $xml_content = substr($content, 4, $xml_len);
            $from_appid = substr($content, $xml_len + 4);
        } catch (Exception $e) {
            return NULL;
        }
        if ($from_appid != $this->appid) return NULL;

        return $xml_content;
    }

    /**
     * 对明文进行加密
     *
     * @param string $text 需要加密的明文
     *
     * @return string 加密后的密文
     */
    private function _encryptMsg($text)
    {

        try {
            $key = base64_decode($this->AESKey . "=");

            //获得16位随机字符串，填充到明文之前
            $random = $this->_getRandomStr();
            $text = $random . pack("N", strlen($text)) . $text . $this->appid;

            // 网络字节序
            $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
            $iv = substr($key, 0, 16);

            //使用自定义的填充方式对明文进行补位填充
            $text = $this->_encode($text);

            mcrypt_generic_init($module, $key, $iv);
            //加密
            $encrypted = mcrypt_generic($module, $text);
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
            //print(base64_encode($encrypted));
            //使用BASE64对加密后的字符串进行编码
            $encrypt = base64_encode($encrypted);

            $timestamp = input('timestamp');
            $nonce = input('nonce');

            //生成安全签名
            $signature = $this->_getSHA1($timestamp, $nonce, $encrypt);

            $format = "<xml>
                <Encrypt><![CDATA[%s]]></Encrypt>
                <MsgSignature><![CDATA[%s]]></MsgSignature>
                <TimeStamp>%s</TimeStamp>
                <Nonce><![CDATA[%s]]></Nonce>
                </xml>";

            return sprintf($format, $encrypt, $signature, $timestamp, $nonce);

        } catch(Exception $e) {
            //print $e;
            return NULL;
        }
    }

    /**
     * 对需要加密的明文进行填充补位
     * @param $text 需要进行填充补位操作的明文
     * @return 补齐明文字符串
     */
    private function _encode($text)
    {
        $block_size = 32;
        $text_length = strlen($text);
        //计算需要填充的位数
        $amount_to_pad = $block_size - ($text_length % $block_size);
        if ($amount_to_pad == 0) {
            $amount_to_pad = PKCS7Encoder::block_size;
        }
        //获得补位所用的字符
        $pad_chr = chr($amount_to_pad);
        $tmp = "";
        for ($index = 0; $index < $amount_to_pad; $index++) {
            $tmp .= $pad_chr;
        }
        return $text . $tmp;
    }

    /**
     * 用SHA1算法生成安全签名
     * @param string $timestamp 时间戳
     * @param string $nonce 随机字符串
     * @param string $encrypt 密文消息
     */
    private function _getSHA1($timestamp, $nonce, $encrypt_msg)
    {
        //排序
        try {
            $array = array($encrypt_msg, $this->token, $timestamp, $nonce);
            sort($array, SORT_STRING);
            $str = implode($array);
            return sha1($str);
        } catch (Exception $e) {
            return FALSE;
        }
    }

    /**
     * 随机生成16位字符串
     * @return string 生成的字符串
     */
    private function _getRandomStr()
    {

        $str = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }

    /**
     * XML文档解析成数组，并将键值转成小写
     * @param  xml $xml
     * @return array
     */
    private function _extractXml($xml)
    {
        $data = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        return array_change_key_case($data, CASE_LOWER);
    }

    //格式转换: 数组转XML
    private function _array2Xml($array)
    {
        $xml = new \SimpleXMLElement('<xml></xml>');
        $this->_data2xml($xml, $array);
        return $xml->asXML();
    }

    /**
     * 数据XML编码
     * @param  object $xml XML对象
     * @param  mixed $data 数据
     * @param  string $item 数字索引时的节点名称
     * @return string xml
     * @author 、lin
     */
    private function _data2xml($xml, $data, $item = 'item')
    {
        foreach ($data as $key => $value) {
            /* 指定默认的数字key */
            is_numeric($key) && $key = $item;
            /* 添加子元素 */
            if (is_array($value) || is_object($value)) {
                $child = $xml->addChild($key);
                $this->_data2xml($child, $value, $item);
            } else {
                if (is_numeric($value)) {
                    $child = $xml->addChild($key, $value);
                } else {
                    $child = $xml->addChild($key);
                    $node = dom_import_simplexml($child);
                    $node->appendChild($node->ownerDocument->createCDATASection($value));
                }
            }
        }
    }
}