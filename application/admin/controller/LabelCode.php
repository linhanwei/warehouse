<?php
/**
 * 条形码控制器
 *
 */
namespace app\admin\controller;
//压缩类
use ZipArchive;
class LabelCode extends Common
{
    //循环单位数量,最小单位: 万, 既是万的整数倍
    private $loop_unit_number = 5000;

    //插入数据库的数量,例如设置 500,那么需要是 500 的倍数才能执行插入操作
    private $insert_data_number = 500;

    //一次最多只能生成的条码数量
    private $make_max_number = 500000;

    //列表
    public function groupIndex()
    {
        $field_list = array(
            'company_name' => '公司名称',
        );

        $where = array();
        $field = input('field');
        $keyword = input('keyword');

        if($field = 'company_name' && !empty($keyword))
        {
            $company_where = array();
            $company_where['name'] = array('like', $keyword. '%');
            $company_ids = model('Company')->getColumnList($company_where, 'id');
            $where['company_id'] = array('in', $company_ids);
        }
        else
        {
            if(isset($field_list[$field]) && !empty($keyword))
            {
                $where[$field] = $keyword;
            }
        }

        $where = array();
        $field = input('field');
        $keyword = input('keyword');
        if(isset($field_list[$field]) && !empty($keyword))
        {
            $where[$field] = $keyword;
        }

        $list = model('LabelCodeGroup')->pageList($where, self::PAGE_TOTAL);

        $this->assign('field_list', $field_list);
        $this->assign('list', $list);
        return $this->fetch('groupIndex');
    }

    //列表
    public function index()
    {
        $group_id = input('group_id');
        $company_id = input('company_id');
        $field = input('field');
        $keyword = input('keyword');

        $field_list = array(
            'code' => '条形码',
        );

        $where = array();

        if(isset($field_list[$field]) && !empty($keyword))
        {
            $where[$field] = $keyword;
        }
        if(!empty($company_id))
        {
//            $company_where = array();
//            $company_where['name'] = array('like', $keyword. '%');
//            $company_ids = model('Company')->getColumnList($company_where, 'id');
            $where['company_id'] = $company_id;
        }

        if(!empty($group_id))
        {
            $where['group_id'] = $group_id;
        }
        //model('LabelCode')->clearCache();
        $list = model('LabelCode')->pageList($where, self::PAGE_TOTAL);

        $company_where = array();
        $companyList = model('Company')->allList($company_where, "id,name");

        $this->assign('companyList', $companyList);
        $this->assign('field_list', $field_list);
        $this->assign('list', $list);
        return $this->fetch('index');
    }

    //添加
    public function add()
    {
        if(request()->isPost())
        {
            $input_arr = input();
            $validate = validate('LabelCode');

            if(!$validate->scene('add')->check($input_arr)){
                $this->error($validate->getError());
            }
            else
            {
                //条形码类型
                $code_type = input('code_type', 1);
                $make_number = input('number');
                if($make_number%$this->insert_data_number != 0)
                {
                    $this->error('生成条形码的数量只能是以万为单位!');
                }

                if($make_number > $this->make_max_number)
                {
                    $this->error('生成条形码一次最多'.($this->make_max_number/10000).'万!');
                }

                $LabelCodeGroupModel = model('LabelCodeGroup');

                if(!isset($LabelCodeGroupModel->code_type_list[$code_type]))
                {
                    $this->error('请选择条形码类型!');
                }

                //插入分组数据
                $code_group_add_data = array();
                $code_group_add_data['company_id'] = input('company_id');;
                $code_group_add_data['code_count'] = $make_number;
                $code_group_add_data['qr_code_type'] = 1;
                $code_group_add_data['templet_id'] = 1;
                $code_group_add_data['code_type'] = $code_type;
                $result = $LabelCodeGroupModel->addData($code_group_add_data);

                if(empty($result))
                {
                    $this->error('添加失败');
                }

                $code_group_id = $LabelCodeGroupModel->id;
                switch ($code_type)
                {
                    case 1:
                            $result = $this->_makeNumberCode($make_number, $code_group_id);
                        break;
                    case 2:
                            $result = $this->_makeMixCode($make_number, $code_group_id);
                        break;
                }

                if($result)
                {
                    $this->success('添加成功', url('label_code/groupIndex'));
                }
                else
                {
                    $this->error('添加失败');
                }
            }
        }
        else
        {
            $company_where = array();
            $companyList = model('Company')->allList($company_where, "id,name");
            $this->assign('companyList', $companyList);
            return $this->fetch('add');
        }
    }

    //修改
    public function edit()
    {
        $this->error('非法操作');

        $id = input('id');

        if(empty($id))
        {
            $this->error('请选择需要修改的品牌!');
        }

        $LabelCodeModel = model('LabelCode');

        //判断品牌是否存在
        $brandInfo = $LabelCodeModel->detail($id);

        if(empty($brandInfo))
        {
            $this->error('选择的品牌不存在!');
        }

        if(request()->isPost())
        {
            $input_arr = input();
            $validate = validate('LabelCode');

            if(!$validate->scene('edit')->check($input_arr)){
                $this->error($validate->getError());
            }
            else
            {
                $brand_name = input('brand_name');

                //验证品牌名称与账号是否存在
                $nameWhere['brand_name'] = $brand_name;
                $nameWhere['id'] = array('neq', $id);
                $count = $LabelCodeModel->getCount($nameWhere);

                if($count > 0)
                {
                    $this->error('品牌名称已经存在!');
                }

                $result = $LabelCodeModel->editData(array('id'=> $id), $input_arr);

                if($result)
                {
                    $this->success('修改成功', url('label_code/index'));
                }
                else
                {
                    $this->error('修改失败');
                }
            }
        }
        else
        {
            $this->assign('brandInfo', $brandInfo);
            return $this->fetch('edit');
        }
    }

    //删除
    public function del()
    {
        $this->error('非法操作');

        $id = input('id');

        if(empty($id))
        {
            $this->error('请选择需要删除的品牌!');
        }

        $LabelCodeModel = model('LabelCode');

        //判断品牌是否存在
        $brandInfo = $LabelCodeModel->detail($id, 'id');

        if(empty($brandInfo))
        {
            $this->error('选择的品牌不存在!');
        }

        $result = $LabelCodeModel->delData(array('id'=>$id));

        if($result)
        {
            $this->success('删除成功!',url('label_code/index'));
        }
        else
        {
            $this->error('删除失败!');
        }
    }

    //导出条形码
    public function exportCode()
    {
        $group_id = input('group_id');

        if(empty($group_id))
        {
            if(request()->isPost()) {
                $input_arr = input();
                $validate = validate('LabelCode');

                if (!$validate->scene('export')->check($input_arr)) {
                    $this->error($validate->getError());
                }

                $company_id = input('company_id');
                $start_time = input('start_time');
                $end_time = input('end_time');

                //获取公司信息
                $companyInfo = model('Company')->detail($company_id, 'name');

                if (empty($companyInfo)) {
                    $this->error('请选择公司');
                }

                $LabelCodeModel = db('label_code');

                $where = array();
                $where['company_id'] = $company_id;
                $where['is_export'] = 0;

                //如果有开始与结束时间则不管是否导出过都重新导出
                if (!empty($start_time) && !empty($end_time)) {
                    unset($where['is_export']);
                    $start_time_stamp = strtotime(date('Y-m-d', strtotime($start_time)) . ' 00:00:00');
                    $end_time_stamp = strtotime(date('Y-m-d', strtotime($end_time)) . ' 23:59:59');
                    $where['create_time'] = array(array('>=', $start_time_stamp), array('<=', $end_time_stamp));
                }

                $this->_export($where, $companyInfo['name']);
            }
            else
            {
                $company_where = array();
                $companyList = model('Company')->allList($company_where, "id,name");
                $this->assign('companyList', $companyList);
                return $this->fetch('exportCode');
            }
        }
        else
        {
            $where = array();
            $where['group_id'] = $group_id;

            //获取公司信息
            $labelCodeGroupInfo = model('LabelCodeGroup')->detail($group_id);

            if (empty($labelCodeGroupInfo)) {
                $this->error('请选择需要导出的数据');
            }

            //获取公司信息
            $companyInfo = model('Company')->detail($labelCodeGroupInfo['company_id'], 'name');

            if (empty($companyInfo)) {
                $this->error('请选择公司');
            }

            $this->_export($where, $companyInfo['name']);
        }
    }

    //生成纯数字条形码
    private function _makeNumberCode($make_number, $group_id)
    {
        ini_set("max_execution_time", 0);
        ini_set ('memory_limit', '256M');

        //公司ID
        $company_id = input('company_id');
        $code_type = input('code_type');
        //循环单位数量,最小单位: 万, 既是万的整数倍
        $loop_unit_number = $this->loop_unit_number;
        //插入数据库的数量,例如设置 500,那么需要是 500 的倍数才能执行插入操作
        $insert_data_number = $this->insert_data_number;
        $insert_data_number = $insert_data_number > $loop_unit_number ? $loop_unit_number : $insert_data_number;

        $LabelCodeModel = db('label_code');

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
                    //暂停 0.2 秒 再执行, 防止数字重复
                    usleep(200000);
                }

                $add_list[$i]['company_id'] = $company_id;
                $add_list[$i]['is_export'] = 0;
                $add_list[$i]['code_type'] = $code_type;
                $add_list[$i]['group_id'] = $group_id;
                $add_list[$i]['code'] = date('ymdHis').$rand_list[$rand_key];
                //$add_list[$i]['code'] = mb_substr(date('y'), -1 ,1).date('mdHis').$rand_list[$i];
                $add_list[$i]['year'] = date('Y');
                $add_list[$i]['month'] = date('m');
                $add_list[$i]['update_time'] = $add_list[$i]['create_time'] = time();

                $rand_key++;

                $ii = $i + 1;
                $mod_ii = $ii%$insert_data_number;
                if($mod_ii == 0)
                {
                    $LabelCodeModel->insertAll($add_list);
                    $add_list = array();
                }
            }
        }

        return true;
    }

    //生成字母与数字混合条形码
    private function _makeMixCode($make_number, $group_id)
    {
        ini_set("max_execution_time", 0);
        ini_set ('memory_limit', '256M');

        $company_id = input('company_id');
        $code_type = input('code_type');
        //循环单位数量,最小单位: 万, 既是万的整数倍
        $loop_unit_number = 5000;
        //插入数据库的数量,例如设置 500,那么需要是 500 的倍数才能执行插入操作
        $insert_data_number = 1000;
        $insert_data_number = $insert_data_number > $loop_unit_number ? $loop_unit_number : $insert_data_number;

        $LabelCodeModel = db('label_code');

        //获取需要循环的最大次数
        $make_code_max_number = ceil($make_number/$loop_unit_number);
        //插入数据数组
        $add_list = array();
        //随机数组键值
        $rand_key = 0;

        for ($k = 0; $k < $make_code_max_number; $k++)
        {
            for ($i = 0; $i < $loop_unit_number; $i++)
            {
                $add_list[$i]['company_id'] = $company_id;
                $add_list[$i]['is_export'] = 0;
                $add_list[$i]['code_type'] = $code_type;
                $add_list[$i]['group_id'] = $group_id;
                $add_list[$i]['code'] = \Util\StringSelf::randString(16, 6);
                $add_list[$i]['year'] = date('Y');
                $add_list[$i]['month'] = date('m');
                $add_list[$i]['update_time'] = $add_list[$i]['create_time'] = time();

                $ii = $i + 1;
                $mod_ii = $ii%$insert_data_number;
                if($mod_ii == 0)
                {
                    $result = $LabelCodeModel->insertAll($add_list);
                    $add_list = array();
                }
            }
        }

        return true;
    }

    //导出
    private function _export($where, $company_name)
    {
        set_time_limit(0);
        ini_set ('memory_limit', '512M');


        $LabelCodeModel = db('label_code');

        $allCount = $LabelCodeModel->where($where)->count();

        //每次取条形码数量
        $max_limit = 500000;
        $max_limit = $allCount >= $max_limit ? $max_limit : $allCount;

        //生成文件数组
        $file_name_arr = array();

        // 逐行取出数据，不浪费内存
        $loop_number = ceil($allCount / $max_limit);

        //文件名称
        $file_name = $company_name .'_'. date('YmdHis');

        //第一行
        $header_title_arr = array(
            '条形码ID',
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

        $group_update_data = array();
        $group_update_data['is_export'] = 1;
        $group_update_data['update_time'] = time();

        if(isset($where['group_id']))
        {
            $where['id'] = $where['group_id'];
            unset($where['group_id']);
        }

        model('LabelCodeGroup')->editData($where, $group_update_data);

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
}
