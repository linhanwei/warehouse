<?php
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-28
 * Time: 10:50
 */
namespace app\common\model;

class Admin extends Base
{
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'admin_id';

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
    }

    //字段处理
    public function getAdminIsSuperTextAttr($value,$data)
    {
        $status = array( 0 => '否', 1 => '是');
        return $status[$data['admin_is_super']];
    }

    public function getAdminLoginTimeAttr($value,$data)
    {
        if($data['admin_login_time'] == 0)
        {
            return '';
        }

        return date('Y-m-d H:i:s', $data['admin_login_time']);
    }

}