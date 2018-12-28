<?php
/**
 * 基础控制器
 */
namespace app\index\controller;

class Base extends \think\Controller
{
    //每页数据条数
    const PAGE_TOTAL = 10;

    //如果没有就跳转到登录页面
    protected function _initialize()
    {
        if(!request()->isAjax())
        {
            $this->assign('DOMAIN_URL', DOMAIN_URL);
            $this->assign('STATIC_URL', STATIC_URL);
        }
    }

    public function _empty()
    {
        //\think\Log::record('非法操作:模块 = '.request()->module().' 控制器 = '.request()->controller().'中,方法 = '.request()->action().'不存在','error');
        return $this->fetch('public/error404');;
    }
}
