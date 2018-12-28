<?php
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-28
 * Time: 10:50
 */
namespace app\common\model;

class GoodsAttribute extends Base
{
    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
    }

    //输出字段处理
    public function getValueNameAttr($value,$data)
    {
        $where['attr_id'] = $data['id'];
        $value_list = model('GoodsAttributeValue')->getColumnList($where, 'attr_value_name');
        return implode(',', $value_list);
    }

}