<?php
/**
 * 控制登录的公共模块
 */
namespace app\index\controller;

class Common extends Base
{
    //如果没有就跳转到登录页面
    protected function _initialize()
    {
        parent::initialize();


    }

    //公共权限,不受权限控制,登录就可以操作
    protected function commonAuth()
    {
        $list = array(
            'admin/Index/index',
            'admin/Region/getRegionList',
            'admin/Goods/getAttrValueList',
        );
        return $list;
    }

}
