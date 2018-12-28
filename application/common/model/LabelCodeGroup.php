<?php
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-28
 * Time: 10:50
 */
namespace app\common\model;

class LabelCodeGroup extends Base
{
    //是否已导出
    private $is_export_list = array(
        0 => '否',
        1 => '是',
    );

    //条形码类型
    public $code_type_list = array(
        1 => '纯数字',
        2 => '字母加数字',
    );

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
    }

    //输出字段处理
    public function getCompanyNameAttr($value,$data)
    {
        $CompanyModel = model('Company');
        $company_info = $CompanyModel->detail($data['company_id'], 'name');

        return empty($company_info) ? '' : $company_info['name'];
    }

    public function getIsExportNameAttr($value,$data)
    {
        return isset($this->is_export_list[$data['is_export']]) ? $this->is_export_list[$data['is_export']] : '';
    }
}