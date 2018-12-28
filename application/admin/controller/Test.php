<?php
/**
 * 基础控制器
 */
namespace app\admin\controller;

use ZipArchive;
class Test extends \think\Controller
{
   public function index()
   {

       //$this->export();
       //$this->makeNumberCode();
       //$this->makeMixCode();
        $key = \Util\StringSelf::randString(16,'', '~!@#$%^&*()_+');

       /*$key = 'mG&8*A2P5#TmCpR';
       $string = 'wx24fa6d6ac3d0c3e5';
       $enstring = \Crypt\Think::encrypt($string, $key);
       print($enstring);
       dump(strlen($enstring));
       $destring = \Crypt\Think::decrypt($enstring, $key);
       dump($destring);
       halt(input('wechat'));*/
       $this->wechat();
   }

   //公众号测试
   public function wechat()
   {
       $options = array(
            'appid' => 'wx24fa6d6ac3d0c3e5',
            'secret' => 'bae3f2bb50827581f43e7261479593a1',
            'debug' => false,
       );
       $Wechat = new \Wechat\CustomMenu($options);

       $openid = 'oET5F0m1ePVvspsVCIEmQ7FawEc0';
       $openid = 'oET5F0joOE0MYsKDZ4EAq5Ch0x60';
       //$user_info = $Wechat->getUserInfo($openid);
       //$group_info = $Wechat->getUserInGroup($openid);

       //dump($group_info);
       $menus = array(
                    array(
                        'type' => 'click',
                        'name' => '今日歌曲单',
                        'key' => 'V10022',
                    ),
                    array(
                        'name' => '菜单',
                        'sub_button' => array(
                            array(
                                'type' => 'view',
                                'name' => '今日歌曲单搜索',
                                'url' => 'http://www.soso.com/',
                            ),
                        ),
                    ),
                   array(
                       'type' => 'click',
                       'name' => 'click click click click click',
                       'key' => 'V1001',
                   ),
           );
       //$menus = array( 'type' => 'click',);
       //halt(json_encode($menus,TRUE));
       dump($Wechat->create($menus));
       //dump($Wechat->get());
       //dump($Wechat->delete());
       dump($Wechat->getToken());
       dump($Wechat->getError());
       halt(11);
   }

   //生成纯数字条形码测试
    public function makeNumberCode()
    {
        ini_set("max_execution_time", 0);
        ini_set ('memory_limit', '256M');

        debug('begin');

        //需要生成条码数量
        $make_number = input('number', 100000);
        $company_id = input('company_id');
        //循环单位数量,最小单位: 万, 既是万的整数倍
        $loop_unit_number = 5000;
        //插入数据库的数量,例如设置 500,那么需要是 500 的倍数才能执行插入操作
        $insert_data_number = 500;
        $insert_data_number = $insert_data_number > $loop_unit_number ? $loop_unit_number : $insert_data_number;

        if($make_number%$insert_data_number != 0)
        {
            $this->error('生成条形码的数量只能是以万为单位!');
        }

        if($make_number > 500000)
        {
            $this->error('生成条形码一次最多50万!');
        }

        $LabelCodeModel = db('label_code');

        /*
        $String = new \Util\StringSelf();
        $rand_number = $String->randString(16,1);
        */

        //生成随机数组
        $rand_max_min_list = array(
            4 => array(1000, 9999),
            5 => array(10000, 99999),
        );
        $rand_list = array();
        $rand_min = $rand_max_min_list[strlen($loop_unit_number)][0];
        $rand_max = $rand_max_min_list[strlen($loop_unit_number)][1];

        for($ri = $rand_min; $ri <= $rand_max; $ri++)
        {
            $rand_list[] = $ri;
        }

        //将生成的随机数打乱
        shuffle($rand_list);

        //获取需要循环的最大次数
        $make_code_max_number = ceil($make_number/$loop_unit_number);
        //插入数据数组
        $add_list = array();
        //随机数组键值
        $rand_key = 0;
        //随机数组最大键值
        $rand_max_key = $rand_max - mb_substr($rand_max, 0 ,strlen($loop_unit_number) - 1) - 1;

        for ($k = 0; $k < $make_code_max_number; $k++)
        {
            for ($i = 0; $i < $loop_unit_number; $i++)
            {
                //如果随机数键值达到最大,则归零
                if($rand_key == $rand_max_key)
                {
                    $rand_key = 0;
                    //暂停 0.2 秒 再执行
                    usleep(200000);
                }

                $add_list[$i]['company_id'] = $company_id;
                $add_list[$i]['is_export'] = 0;
                $add_list[$i]['code'] = date('ymdHis').$rand_list[$rand_key];
                //$add_list[$i]['code'] = mb_substr(date('y'), -1 ,1).date('mdHis').$rand_list[$i];
                $add_list[$i]['create_time'] = time();

                $rand_key++;

                $ii = $i + 1;
                $mod_ii = $ii%$insert_data_number;
                if($mod_ii == 0)
                {
                    $result = $LabelCodeModel->insertAll($add_list);
                    $add_list = array();
                }
            }
        }

        dump($make_number);
        debug('end');
        dump(debug('begin','end').'s');
        dump(debug('begin','end',6).'s');
        dump(debug('begin','end','m').'kb');
        exit();
    }

    //生成纯数字条形码测试
    public function makeMixCode()
    {
        ini_set("max_execution_time", 0);
        ini_set ('memory_limit', '256M');

        debug('begin');

        //需要生成条码数量
        $make_number = input('number', 100000);
        $company_id = input('company_id', 1);
        //循环单位数量,最小单位: 万, 既是万的整数倍
        $loop_unit_number = 5000;
        //插入数据库的数量,例如设置 500,那么需要是 500 的倍数才能执行插入操作
        $insert_data_number = 1000;
        $insert_data_number = $insert_data_number > $loop_unit_number ? $loop_unit_number : $insert_data_number;

        if($make_number%$insert_data_number != 0)
        {
            $this->error('生成条形码的数量只能是以万为单位!');
        }

        if($make_number > 500000)
        {
           // $this->error('生成条形码一次最多50万!');
        }

        $LabelCodeModel = db('label_code');

        $String = new \Util\StringSelf();
        /*$list = array();
       for($i=0;$i<1000000;$i++)
       {
           //$rand_number = $String->keyGen();
           $rand_number = $String->randString(16);
           $list[] = $rand_number;
       }
        dump(count(array_unique($list)));
        debug('end');
        dump(debug('begin','end').'s');
        dump(debug('begin','end',6).'s');
        dump(debug('begin','end','m').'kb');
        exit();*/
/*
        //生成随机数组
        $rand_max_min_list = array(
            4 => array(1000, 9999),
            5 => array(10000, 99999),
        );
        $rand_list = array();
        $rand_min = $rand_max_min_list[strlen($loop_unit_number)][0];
        $rand_max = $rand_max_min_list[strlen($loop_unit_number)][1];

        for($ri = $rand_min; $ri <= $rand_max; $ri++)
        {
            $rand_list[] = $ri;
        }

        //将生成的随机数打乱
        shuffle($rand_list);*/

        //获取需要循环的最大次数
        $make_code_max_number = ceil($make_number/$loop_unit_number);
        //插入数据数组
        $add_list = array();
        //随机数组键值
        $rand_key = 0;
        //随机数组最大键值
        //$rand_max_key = $rand_max - mb_substr($rand_max, 0 ,strlen($loop_unit_number) - 1) - 1;

        for ($k = 0; $k < $make_code_max_number; $k++)
        {
            for ($i = 0; $i < $loop_unit_number; $i++)
            {
                /*
                //如果随机数键值达到最大,则归零
                if($rand_key == $rand_max_key)
                {
                    $rand_key = 0;
                    //暂停 0.2 秒 再执行
                    usleep(200000);
                }*/

                $add_list[$i]['company_id'] = $company_id;
                $add_list[$i]['is_export'] = 0;
                $add_list[$i]['code'] = $String->randString(16, 6);
                $add_list[$i]['create_time'] = time();

                $rand_key++;

                $ii = $i + 1;
                $mod_ii = $ii%$insert_data_number;
                if($mod_ii == 0)
                {
                    $result = $LabelCodeModel->insertAll($add_list);
                    $add_list = array();
                }
            }
        }

        dump($make_number);
        debug('end');
        dump(debug('begin','end').'s');
        dump(debug('begin','end',6).'s');
        dump(debug('begin','end','m').'kb');
        exit();
    }

    private static function _getCode($rand_list, $insert_code_list, &$rand_key)
    {
        $code = date('ymdHis').$rand_list[$rand_key];
        if(!in_array_self($code, $insert_code_list))
        {
            return $code;
        }

        $rand_key++;
        self::_getCode($rand_list, $insert_code_list, $rand_key);
    }

    //导出excel
    public function export()
    {
        $this->putCsv();
        exit();
        ini_set("max_execution_time", 0);
        ini_set ('memory_limit', '512M');

        $where = array();
        $datas = db('label_code')->where($where)->field('code')->limit(10)->order('id ASC')->select();

        $filename = date('YmdHis').'.csv'; //设置文件名

        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        $fp = fopen('php://output', 'a');

        $header_title_arr = array(
            '条形码'
        );

        //因为Excel不能查看utf-8编码
        foreach ($header_title_arr as $k => $val)
        {
            $header_title_arr[$k] = (string)iconv('utf-8','gb2312', $val)."\t";
        }
        fputcsv($fp, $header_title_arr);

        foreach ($datas as $key => $value)
        {
            foreach ($value as $k => $val)
            {
                $value[$k] = (string)iconv('utf-8','gb2312',$value[$k])."\t";
            }

            fputcsv($fp, $value);
        }

        fclose($fp);
        exit();
    }

    function putCsv()
    {
        set_time_limit(0);
        ini_set ('memory_limit', '512M');

        $LabelCodeModel = db('label_code');

        $where = array();
        $where['company_id'] = 1;
        $where['is_export'] = 0;

        $allCount = $LabelCodeModel->where($where)->count();

        //每次取数量
        $max_limit = 500000;
        $max_limit = $allCount >= $max_limit ? $max_limit : $allCount;

        //生成文件数组
        $file_name_arr = array();

        // 逐行取出数据，不浪费内存
        $loop_number = ceil($allCount / $max_limit);

        //文件名称
        $file_name = date('YmdHis');

        //第一行
        $header_title_arr = array(
            'ID',
            '条形码'
        );

        //因为Excel不能查看utf-8编码
        foreach ($header_title_arr as $k => $val)
        {
            $header_title_arr[$k] = (string)iconv('utf-8','gb2312', $val)."\t";
        }

        for ($i = 0; $i < $loop_number; $i++)
        {
            $fp = fopen($file_name . '_' . $i . '.csv', 'w'); //生成临时文件
            //chmod($file_name . '_' . $i . '.csv',777);//修改可执行权限
            $file_name_arr[] = $file_name . '_' .  $i . '.csv';

            // 将数据通过fputcsv写到文件句柄
            fputcsv($fp, $header_title_arr);

            //获取数据
            $list = $LabelCodeModel->where($where)->page($i + 1, $max_limit)->field('id,code')->order('id ASC')->select();
            foreach ($list as $k => $value) {
                //转码
                foreach ($value as $h => $val)
                {
                    $value[$h] = (string)iconv('utf-8','gb2312',$value[$h])."\t";
                }

                fputcsv($fp, $value);
            }

            //把内存的数据取出
            ob_flush();
            flush();

            //每生成一个文件关闭
            fclose($fp);
        }

        //进行多个文件压缩
        $zip = new ZipArchive();
        $file_zip_name = $file_name . ".zip";

        //打开压缩包
        $zip->open($file_zip_name, ZipArchive::CREATE);
        foreach ($file_name_arr as $file) {
            //向压缩包中添加文件
            $zip->addFile($file, basename($file));
        }

        //关闭压缩包
        $zip->close();

        //删除csv临时文件
        foreach ($file_name_arr as $file) {
            unlink($file);
        }

        //修改条形码导出状态
        $update_data = array();
        $update_data['is_export'] = 1;
        $update_data['update_time'] = time();
        $LabelCodeModel->where($where)->update($update_data);

        //输出压缩文件提供下载
        header("Cache-Control: max-age=0");
        header("Content-Description: File Transfer");
        // 文件名
        header('Content-disposition: attachment; filename=' . basename($file_zip_name));
        // zip格式
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: binary");
        header('Content-Length: ' . filesize($file_zip_name));
        //输出文件;
        readfile($file_zip_name);
        //删除压缩包临时文件
        unlink($file_zip_name);
    }

    //excel导出示例
    public function export_excel()
    {
        include '../core/library/phpexcel/PHPExcel.php';
        include '../core/library/phpexcel/PHPExcel/Writer/Excel2007.php';
        $objPHPExcel = new PHPExcel();


        $datas = array(
            array('王城', '男', '18', '1997-03-13', '18948348924'),
            array('李飞虹', '男', '21', '1994-06-13', '159481838924'),
            array('王芸', '女', '18', '1997-03-13', '18648313924'),
            array('郭瑞', '男', '17', '1998-04-13', '15543248924'),
            array('李晓霞', '女', '19', '1996-06-13', '18748348924'),
        );

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '名字')
            ->setCellValue('B1', '性别')
            ->setCellValue('C1', '年龄')
            ->setCellValue('D1', '出生日期')
            ->setCellValue('E1', '电话号码');

        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('标题Phpmarker-' . date('Y-m-d'));

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);
        $objPHPExcel->getActiveSheet()->freezePane('A2');

        $i = 2;
        foreach($datas as $data){
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $data[0])->getStyle('A'.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $data[1]);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $data[2]);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D'. $i, $data[3],PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle('D' . $i)->getNumberFormat()->setFormatCode("@");

            // 设置文本格式
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('E'. $i, $data[4],PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle('E' . $i)->getAlignment()->setWrapText(true);
            $i++;
        }

        //22222
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(1)
            ->setCellValue('A1', '名字')
            ->setCellValue('B1', '性别')
            ->setCellValue('C1', '年龄')
            ->setCellValue('D1', '出生日期')
            ->setCellValue('E1', '电话号码');

        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('标题Phpmarker-' . date('Y-m-d'));

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);
        $objPHPExcel->getActiveSheet()->freezePane('A2');

        $k = 2;
        foreach($datas as $data){
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $k, $data[0])->getStyle('A'.$k)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $k, $data[1]);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $k, $data[2]);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D'. $k, $data[3],PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle('D' . $k)->getNumberFormat()->setFormatCode("@");

            // 设置文本格式
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('E'. $k, $data[4],PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle('E' . $k)->getAlignment()->setWrapText(true);
            $k++;
        }

        $objActSheet = $objPHPExcel->getActiveSheet();

        //设置宽度
        $objActSheet->getColumnDimension('A')->setWidth(18.5);
        $objActSheet->getColumnDimension('B')->setWidth(23.5);
        $objActSheet->getColumnDimension('C')->setWidth(12);
        $objActSheet->getColumnDimension('D')->setWidth(12);
        $objActSheet->getColumnDimension('E')->setWidth(12);

        $filename = '2015030423';
        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        $objWriter->save('php://output');
    }
}
