<?php
/**
 * 错误控制器
 */
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-28
 * Time: 17:02
 */
namespace app\admin\controller;

class Error extends Base
{
    //如果没有就跳转到登录页面
    protected function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        //Log::record('非法操作:模块 = '.request()->module().'中, 控制器 = '.request()->controller().' 不存在','error');
        return $this->fetch('common@public/error404');;
    }

}