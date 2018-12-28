<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
// 异常错误报错级别,
    //error_reporting(E_ERROR | E_PARSE);
// 应用公共文件
    use think\facade\Request;
    use think\facade\Config;

    /***************  定义自己的项目常量开始 ******************/
    define('DS', DIRECTORY_SEPARATOR);
    defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DS);
    defined('ROOT_PATH') or define('ROOT_PATH', dirname(realpath(APP_PATH)) . DS);
    //限制图片上传大小,单位: M
    define('UPLOAD_IMAGE_SIZE', 2);
    //验证手机号码正则规则
    define('VALIDATE_MOBILE_REGX', '/^((13[3-9]{1})|(14[7]{1})|(15[0123789]{1})|(17[3678]{1})|(18[0-9]{1})|(19[9]{1}))\d{8}$/');
    //上传根目录
    if(!defined('UPLOADS_ROOT_PATH')) define('UPLOADS_ROOT_PATH', str_replace('\\', '/', ROOT_PATH . 'public/uploads/'));
    //上传临时目录
    if(!defined('UPLOADS_TMP_PATH')) define('UPLOADS_TMP_PATH', str_replace('\\', '/', UPLOADS_ROOT_PATH . 'tmp/'));
    //上传文件目录
    if(!defined('UPLOADS_FILE_PATH')) define('UPLOADS_FILE_PATH', str_replace('\\', '/', UPLOADS_ROOT_PATH . 'file/'));
    //上传图片目录
    if(!defined('UPLOADS_IMAGES_PATH')) define('UPLOADS_IMAGES_PATH', str_replace('\\', '/', UPLOADS_ROOT_PATH . 'images/'));
    // 定义URL链接地址
    $domain_url = Request::instance()->domain();
    $uploads_url = Config::get('self_url.uploads_url');
    $static_url = Config::get('self_url.static_url');
    $image_url = Config::get('self_url.image_url');
    $uploads_url = empty($uploads_url) ? $domain_url : $uploads_url;
    $static_url = empty($static_url) ? $domain_url : $static_url;
    $image_url = empty($image_url) ? $domain_url : $image_url;
    define('DOMAIN_URL', $domain_url); //域名URL
    define('UPLOADS_URL', $uploads_url); //上传URL
    define('STATIC_URL', $static_url); //静态文件URL
    define('IMAGE_URL', $image_url . '/uploads/images/'); //图片URL
    /***************  定义自己的项目常量结束 ******************/
    /**
     * 生成密码
     *
     * @param $password 密码
     * @param $salt     密码盐
     *
     * @return string
     */
    function make_password($password, $salt)
    {
        return md5($password . '/' . $salt);
    }

    /**
     * 移动上传文件
     *
     * @param        $files     需要移动的文件,可以是数组,或者字符串
     * @param string $move_path 移动文件的目录
     *
     * @return bool
     */
    function move_file($files, $move_path = 'images')
    {
        if(empty($files)) {
            return FALSE;
        }
        //限定死上传的目录
        $path_list = array(
            'images' => UPLOADS_IMAGES_PATH . get_date_path(),
            'file' => UPLOADS_FILE_PATH . get_date_path(),
        );
        //没有该目录则不移动文件
        if(!isset($path_list[$move_path])) {
            return FALSE;
        } else {
            $new_file_path = $path_list[$move_path];
        }
        //没有该目录则创建
        make_dir($new_file_path);
        //移动文件
        if(is_array($files)) {
            foreach($files as $k => $file) {
                $file_path_arr = explode('/', $file);
                if(!isset($file_path_arr[count($file_path_arr) - 1])) {
                    return FALSE;
                }
                if(file_exists(UPLOADS_TMP_PATH . $file_path_arr[count($file_path_arr) - 1])) {
                    if(!rename(UPLOADS_TMP_PATH . $file_path_arr[count($file_path_arr) - 1], UPLOADS_IMAGES_PATH . $file)) {
                        return FALSE;
                    }
                } else {
                    continue;
                }
            }

            return TRUE;
        } else {
            $file_path_arr = explode('/', $files);
            if(!isset($file_path_arr[count($file_path_arr) - 1])) {
                return FALSE;
            }
            if(file_exists(UPLOADS_TMP_PATH . $file_path_arr[count($file_path_arr) - 1])) {
                return rename(UPLOADS_TMP_PATH . $file_path_arr[count($file_path_arr) - 1], UPLOADS_IMAGES_PATH . $files);
            } else {
                return FALSE;
            }
        }
    }

    /**
     * 创建目录
     *
     * @param $path 需要创建的目录路径
     *
     * @return bool
     */
    function make_dir($path)
    {
        if(!is_dir($path)) {
            if(!mkdir($path, 0777, TRUE)) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * 获取日期子目录
     *
     * @return string
     */
    function get_date_path()
    {
        return date('Ymd') . '/';
    }

    /**
     * 处理上传数据存放数据库路径
     *
     * @param $file
     *
     * @return string
     */
    function handle_upload_deposit_path($file)
    {
        return get_date_path() . $file;
    }

    /**
     * 判断元素是否在数组中, PHP内置的in_array()效率太慢
     *
     * @param $item
     * @param $array
     *
     * @return bool
     */
    function in_array_self($item, $array)
    {
        $str = implode(',', $array);
        $str = ',' . $str . ',';
        $item = ',' . $item . ',';

        return FALSE !== strpos($item, $str) ? TRUE : FALSE;
    }

    /**
     * 解密函数
     *
     * @param $value
     * @param $key
     *
     * @return string
     */
    function decrypt($value, $key)
    {
        return empty($value) ? '' : \Crypt\Think::decrypt($value, $key);
    }

    /**
     * 加密函数
     *
     * @param $value
     * @param $key
     *
     * @return string
     */
    function encrypt($value, $key)
    {
        return empty($value) ? '' : \Crypt\Think::encrypt($value, $key);
    }

    /**
     * 发送HTTP请求方法，目前只支持CURL发送请求
     *
     * @param  string $url    请求URL
     * @param  array  $params 请求参数
     * @param  string $method 请求方法GET/POST
     * @param  array  $header 发送头部信息 例如:$header = array('token:JxRaZezavm3HXM3d9pWnYiqqQC1SJbsU','language:zh','region:GZ');
     *
     * @return array   $data   响应数据
     * @author 、lin
     */
    function http($url, $params = array(), $method = 'GET', $header = array())
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

        $opts[CURLOPT_HTTPHEADER] = $header;

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        curl_close($ch);
        if($err > 0) {
            return $errmsg;
        } else {
            return json_decode($data, TRUE);
        }
    }

    /**
     * 获取自定义的header数据
     */
    function get_all_headers()
    {
        // 忽略获取的header数据
        $ignore = array('host', 'accept', 'content-length', 'content-type');
        $headers = array();
        foreach($_SERVER as $key => $value) {
            if(substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
                $key = str_replace('_', ' ', $key);
                $key = str_replace(' ', '-', $key);
                $key = strtolower($key);
                if(!in_array($key, $ignore)) {
                    $headers[$key] = $value;
                }
            }
        }

        return $headers;
    }

    /**
     * 利用百度地图接口获取当前访问网址的地区
     *
     * @return array
     */
    function get_address()
    {
        $ak = 'HUxmWKqGzEOHKgxGboDMupEvyno4YYXu';
        $url = 'https://api.map.baidu.com/location/ip?ak=' . $ak;
        $result = http($url);
        $address_list = array(
            'country' => '',
            'province' => '',
            'city' => '',
            'district' => '',
            'street' => '',
        );
        if($result['status'] == 0) {
            $address = explode('|', $result['address']);
            $country_list = array(
                'cn' => array('key' => array('HK', 'CN'), 'name' => '中国'),
            );
            $country = in_array($address[0], $country_list['cn']['key']) ? $country_list['cn']['name'] : $address[0];
            $address_list = array(
                'country' => $country,
                'province' => $address[1],
                'city' => $address[2],
                'district' => $address[3],
                'street' => $address[4],
            );
        }

        return $address_list;
    }

    /**
     * 获取服务器IP地址
     *
     * @return string
     */
    function server_ip()
    {
        return gethostbyname($_SERVER['SERVER_NAME']);
    }
