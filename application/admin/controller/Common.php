<?php
/**
 * 控制登录的公共模块
 */
namespace app\admin\controller;

class Common extends Base
{
    //如果没有就跳转到登录页面
    protected function initialize()
    {
        parent::initialize();

        if(!session('?admin_id'))
        {
            $this->redirect('Login/index');
        }

        $admin_auth_list = model('AdminAuthorityGroup')->getAuthList(session('admin_gid'));
        $url = request()->module().'/'.request()->controller().'/'.request()->action();

        if($admin_auth_list != 0 || !in_array($url, $admin_auth_list) || !in_array($url,$this->commonAuth()))
        {
            //异步请求的时候
            if(request()->isAjax())
            {
            }
            else
            {
                //$this->error('你没有权限操作,请联系管理员');
            }
        }

        if(!request()->isAjax())
        {
            $this->assign('adminAuthList', $admin_auth_list);
        }
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
