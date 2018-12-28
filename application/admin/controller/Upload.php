<?php
/**
 * 图片与文件上传
 */
namespace app\admin\controller;

class Upload extends Common
{
    //百度插件上传文件
    public function webUpload()
    {
        // Make sure file is not cached (as it happens for example on iOS devices)
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        // 5 minutes execution time
        @set_time_limit(5 * 60);
        $cleanup_target_dir = true;     // Remove old files
        $max_file_age = 5 * 3600;       // Temp file age in seconds

        $guid       = input("guid");
        $tmp_path   = input("tmp_path", 'tmp');      // 文件上传临时目录
        $limit_type = input("limit_type");           // 文件上传限制类型
        $randname   = input("randname", 0, 'int');   // 是否生成随机名
        //$token      = input("token", "");          // token
        $chunk      = input("chunk", 0, 'int');
        $chunks     = input("chunks", 1, 'int');

        //$target_dir = ini_get("upload_tmp_dir")."/plupload";
        $target_dir = UPLOADS_TMP_PATH . $guid;
        $upload_dir = UPLOADS_ROOT_PATH . $tmp_path;

        // 目录不存在则生成
        make_dir($target_dir);
        make_dir($upload_dir);

        //验证文件上传信息
        $file = request()->file('file');
        $check_result = $file->check(['size'=>2*1024*1024,'ext'=>'jpg,png,gif,jpeg']);

        if(!$check_result)
        {
            die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "{'.$file->getError().'}"}, "id" : "id"}');
        }

        $info = $file->getInfo();
        $filename   = iconv("UTF-8", "GBK", $this->_unicodeToUtf8($info['name']));
        $filepath   = $target_dir . '/' . $filename;
        $uploadpath = $upload_dir . '/' . $filename;

        // Remove old temp files
        if ($cleanup_target_dir)
        {

            if (!is_dir($target_dir) || !$dir = opendir($target_dir))
            {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "上传失败"}, "id" : "id"}');
            }

            while (($file = readdir($dir)) !== false)
            {
                $tmpfile_path = $target_dir . '/' . $file;

                // If temp file is current file proceed to the next
                if ($tmpfile_path == "{$filepath}_{$chunk}.part" || $tmpfile_path == "{$filepath}_{$chunk}.parttmp")
                {
                    continue;
                }

                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.(part|parttmp)$/', $file)
                    && file_exists($tmpfile_path)
                    && (@filemtime($tmpfile_path) < time() - $max_file_age))
                {
                    @unlink($tmpfile_path);
                }
            }
            closedir($dir);
        }

        // Open temp file
        if (!$out = @fopen("{$filepath}_{$chunk}.parttmp", "wb"))
        {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "上传失败"}, "id" : "id"}');
        }

        if (!empty($info))
        {
            // Read binary input stream and append it to temp file
            if (!$in = @fopen($info['tmp_name'], "rb"))
            {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "上传失败"}, "id" : "id"}');
            }
        }
        else
        {
            if (!$in = @fopen("php://input", "rb"))
            {
                die('{"jsonrpc" : "2.0", "error" : {"code": 104, "message": "上传失败"}, "id" : "id"}');
            }
        }

        while ($buff = fread($in, 4096))
        {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);

        rename("{$filepath}_{$chunk}.parttmp", "{$filepath}_{$chunk}.part");

        $index = 0;
        $done = true;
        for( $index = 0; $index < $chunks; $index++ )
        {
            if ( !file_exists("{$filepath}_{$index}.part") )
            {
                $done = false;
                break;
            }
        }

        if ( $done )
        {
            if (!$out = @fopen($uploadpath, "wb"))
            {
                die('{"jsonrpc" : "2.0", "error" : {"code": 105, "message": "上传失败"}, "id" : "id"}');
            }

            if ( flock($out, LOCK_EX) )
            {
                for( $index = 0; $index < $chunks; $index++ )
                {
                    if (!$in = @fopen("{$filepath}_{$index}.part", "rb"))
                    {
                        break;
                    }

                    while ($buff = fread($in, 4096))
                    {
                        fwrite($out, $buff);
                    }

                    @fclose($in);
                    @unlink("{$filepath}_{$index}.part");
                }
                flock($out, LOCK_UN);
            }
            @fclose($out);
            // 删除目录
            @rmdir($target_dir);
            // 是否随机生成名称
            if ($randname)
            {
                $ext = strtolower(pathinfo($info['name'], PATHINFO_EXTENSION));
                $filename = uniqid() . "." . $ext;
                rename("{$uploadpath}", "{$upload_dir}/{$filename}");
            }

            $filelink = STATIC_URL . "/uploads/".$tmp_path."/".$filename;
            die('{"jsonrpc" : "2.0", "result" : {"filename":"' . get_date_path() . $filename.'","filelink":"'.$filelink.'"}, "id" : "id"}');
        }

        //Return Success JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }

    //删除图片
    public function dbDel()
    {
        $filename = input('filename');
        $return_data = array('status' => 1, 'msg' => '删除成功');
        $del_image = '';
        $filename_path_list = empty($filename) ? array() : explode('/', $filename);
        $list_count = count($filename_path_list) - 1;
        $tmp_image_filename = isset($filename_path_list[$list_count]) ? $filename_path_list[$list_count] : '';

        if(!empty($tmp_image_filename))
        {
            if(file_exists(UPLOADS_TMP_PATH.$tmp_image_filename))
            {
                $del_image = UPLOADS_TMP_PATH.$tmp_image_filename;
            }
        }

        if(file_exists(UPLOADS_IMAGES_PATH.$filename))
        {
            $del_image = UPLOADS_IMAGES_PATH.$filename;
        }

        if(!empty($del_image) && !unlink($del_image))
        {
            $return_data = array('status' => 0, 'msg' => '删除失败');
        }

        return json($return_data);
    }

    //unicode 转成 Utf8
    private function _unicodeToUtf8($str)
    {
        if (!$str) return $str;
        $decode = json_decode($str);
        if ($decode) return $decode;
        $str = '["' . $str . '"]';
        $decode = json_decode($str);
        if (count($decode) == 1)
        {
            return $decode[0];
        }
        return $str;
    }
}
