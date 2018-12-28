<?php
/**
 * 公众号配置控制器
 *
 */
namespace app\admin\controller;

use think\Route;

class WechatConfig extends Common
{
    //列表
    public function index()
    {
        $field_list = array(
            'company_name' => '公司名称',
        );

        $where = array();
        $field = input('field');
        $keyword = input('keyword');
        if(isset($field_list[$field]) && !empty($keyword))
        {
            $where[$field] = $keyword;
        }

        $list = model('WechatConfig')->pageList($where, self::PAGE_TOTAL);
//
//        //创建微信菜单
//        $id = input('id',1);
//        $WechatConfigModel = model('WechatConfig');
//        //判断配置是否存在
//        $wxconfigInfo = $WechatConfigModel->detail($id);
//        $WechatMaterial = new \Wechat\Material($wxconfigInfo);
//        $result = $WechatMaterial->batchget();
//        dump($result);

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
            $validate = validate('WechatConfig');

            if(!$validate->scene('add')->check($input_arr)){
                $this->error($validate->getError());
            }
            else
            {
                $company_id = input('company_id');
                $app_id = input('app_id');

                $WechatConfigModel = model('WechatConfig');

                //验证是否存在
                $count_where = array();
                $count_where['company_id'] = $company_id;
                $count_where['app_id'] = $app_id;
                $count = $WechatConfigModel->getCount($count_where);

                if($count > 0)
                {
                    $this->error('开发者ID(AppID)已经存在!');
                }
                
                $result = $WechatConfigModel->addData($input_arr);

                if($result)
                {
                    $this->success('添加成功', url('wechat_config/index'));
                }
                else
                {
                    $this->error('添加失败');
                }
            }
        }
        else
        {
            $this->assign('companyList', model('Company')->allList('', 'id,name'));
            $this->assign('decryptTypeList', model('WechatConfig')->decrypt_type_list);
            return $this->fetch('add');
        }
    }

    //修改
    public function edit()
    {
        $id = input('id');

        if(empty($id))
        {
            $this->error('请选择需要修改的配置!');
        }

        $WechatConfigModel = model('WechatConfig');

        //判断配置是否存在
        $wxconfigInfo = $WechatConfigModel->detail($id);

        if(empty($wxconfigInfo))
        {
            $this->error('选择的公众号信息不存在!');
        }

        if(request()->isPost())
        {
            $input_arr = input();
            $validate = validate('WechatConfig');

            if(!$validate->scene('edit')->check($input_arr)){
                $this->error($validate->getError());
            }
            else
            {
                $company_id = input('company_id');
                $app_id = input('app_id');

                //验证是否存在
                $nameWhere = array();
                $nameWhere['company_id'] = $company_id;
                $nameWhere['app_id'] = $app_id;
                $nameWhere['id'] = array('neq', $id);
                $count = $WechatConfigModel->getCount($nameWhere);

                if($count > 0)
                {
                    $this->error('开发者ID(AppID)已经存在!');
                }

                $result = $WechatConfigModel->editData(array('id'=> $id), $input_arr);

                if($result)
                {
                    $this->success('修改成功', url('wechat_config/index'));
                }
                else
                {
                    $this->error('修改失败');
                }
            }
        }
        else
        {
            $this->assign('wxconfigInfo', $wxconfigInfo);
            $this->assign('companyList', model('Company')->allList('', 'id,name'));
            $this->assign('decryptTypeList', model('WechatConfig')->decrypt_type_list);
            return $this->fetch('edit');
        }
    }

    //删除
    public function del()
    {
        $id = input('id');

        if(empty($id))
        {
            $this->error('请选择需要删除的公众号信息!');
        }

        $WechatConfigModel = model('WechatConfig');

        //判断配置是否存在
        $wxconfigInfo = $WechatConfigModel->detail($id, 'id');

        if(empty($wxconfigInfo))
        {
            $this->error('选择的公众号信息不存在!');
        }

        $result = $WechatConfigModel->delData(array('id'=>$id));

        if($result)
        {
            $this->success('删除成功!',url('wechat_config/index'));
        }
        else
        {
            $this->error('删除失败!');
        }
    }

    //查看
    public function show()
    {
        $id = input('id');

        if(empty($id))
        {
            $this->error('请选择需要修改的配置!');
        }

        $WechatConfigModel = model('WechatConfig');

        //判断配置是否存在
        $wxconfigInfo = $WechatConfigModel->detail($id);

        if(empty($wxconfigInfo))
        {
            $this->error('选择的公众号信息不存在!');
        }

        //公司名称
        $companyInfo = model('Company')->detail($wxconfigInfo['company_id']);
        $wxconfigInfo['company_name'] = empty($companyInfo) ? '' : $companyInfo['name'];

        //加密类型
        $decrypt_type_list = model('WechatConfig')->decrypt_type_list;
        $wxconfigInfo['decrypt_type_name'] = isset($decrypt_type_list[$wxconfigInfo['decrypt_type']]) ? $decrypt_type_list[$wxconfigInfo['decrypt_type']] : '';

        //服务器地址(URL)
        $wxconfigInfo['wechat_url'] = str_replace('admin', 'api', DOMAIN_URL).'/wechat/index/cid/'.$id.'.'.config('default_return_type');

        //IP白名单
        $wxconfigInfo['server_ip'] = config('server_ip');

        $this->assign('wxconfigInfo', $wxconfigInfo);
        return $this->fetch('show');
    }

    /**
     * 创建自定义公众号菜单
     *
     * @return mixed
     */
    public function customMenu()
    {
        if(request()->isPost())
        {
            $input_arr = input();
            $validate = validate('WechatConfig');

            if(!$validate->scene('customMenu')->check($input_arr)){
                $this->error($validate->getError());
            }
            else
            {
                $wechat_config_id = input('wechat_config_id');
                $wxconfigInfo = model('WechatConfig')->detail($wechat_config_id);
                if(empty($wxconfigInfo))
                {
                    $this->error('请选择公众号');
                }

                //菜单处理
                $menus = array();
                $model_menus = array();

                for($i = 1; $i <= 3; $i++)
                {
                    $name = input('name'.$i);
                    $decrypt_type = input('decrypt_type'.$i);
                    $content = input('content'.$i);

                    if(empty($name))
                    {
                        continue;
                    }

                    $child_list = array();
                    $model_child_list = array();
                    $child_name_list = input('child_name'.$i.'/a');
                    $child_decrypt_type_list = input('child_decrypt_type'.$i.'/a');
                    $child_content_list = input('child_content'.$i.'/a');

                    foreach($child_name_list as $k => $v)
                    {
                        if(empty($v))
                        {
                            continue;
                        }

                        $child_content = $child_content_list[$k];
                        if(empty($child_content))
                        {
                            continue;
                        }

                        $model_child_list[$k]['name'] = $child_list[$k]['name'] = $child_name_list[$k];
                        $model_child_list[$k]['type'] = $child_list[$k]['type'] = $child_decrypt_type_list[$k];
                        $model_child_list[$k]['content'] = $child_content;
                        if(in_array($child_decrypt_type_list[$k], array('view', 'miniprogram')))
                        {
                            $child_list[$k]['url'] = $child_content;
                        }
                        elseif(in_array($child_decrypt_type_list[$k], array('media_id', 'view_limited')))
                        {
                            $child_list[$k]['media_id'] = $child_content;
                        }
                        else
                        {
                            $child_list[$k]['key'] = $child_content;
                        }
                    }

                    $first_menu = array();
                    $model_first_menu = array();
                    $model_first_menu['name'] = $first_menu['name'] = $name;

                    if(!empty($child_list))
                    {
                        $model_first_menu['content'] = $content;
                        $first_menu['sub_button'] = $child_list;
                        $model_first_menu['sub_button'] = $model_child_list;
                    }
                    else
                    {
                        if(empty($content))
                        {
                            continue;
                        }

                        $model_first_menu['type'] = $first_menu['type'] = $decrypt_type;
                        $model_first_menu['content'] = $content;
                        if(in_array($decrypt_type, array('view', 'miniprogram')))
                        {
                            $first_menu['url'] = $content;
                        }
                        elseif(in_array($decrypt_type, array('media_id', 'view_limited')))
                        {
                            $first_menu['media_id'] = $content;
                        }
                        else
                        {
                            $first_menu['key'] = $content;
                        }
                    }

                    $menus[] = $first_menu;
                    $model_menus[] = $model_first_menu;
                }

                //创建微信菜单
                $WechatCustomMenu = new \Wechat\CustomMenu($wxconfigInfo);
                $result = $WechatCustomMenu->create($menus);

                if($result === FALSE)
                {
                    $this->error($WechatCustomMenu->getError());
                }

                $WechatMenuModel = model('WechatMenu');

                //验证是否存在
                $count_where = array();
                $count_where['wechat_config_id'] = $wechat_config_id;
                $count = $WechatMenuModel->getCount($count_where);

                $menu_data = array();
                $menu_data['wechat_config_id'] = $wechat_config_id;
                $menu_data['menus'] = $model_menus;

                //存在则修改
                if($count > 0)
                {
                    $update_where = array();
                    $update_where['wechat_config_id'] = $wechat_config_id;
                    $result = $WechatMenuModel->editData($update_where, $menu_data);
                }
                else
                {
                    $result = $WechatMenuModel->addData($menu_data);
                }

                if($result)
                {
                    $this->success('创建自定义菜单成功', url('wechat_config/index'));
                }
                else
                {
                    $this->error('创建自定义菜单失败');
                }
            }
        }
        else
        {
            $config_id = input('id');
            $wxconfigInfo = model('WechatConfig')->detail($config_id);

            if(empty($wxconfigInfo))
            {
                $this->error('请选择公众号');
            }

            $wxconfigMenus = model('WechatMenu')->getInfo(array('wechat_config_id' => $config_id));
            $menus = empty($wxconfigMenus) ? array() : $wxconfigMenus['menus'];
            //halt($menus);
            $WechatCustomMenu = new \Wechat\CustomMenu($wxconfigInfo);
            $this->assign('wxconfigInfo', $wxconfigInfo);
            $this->assign('menus', $menus);
            $this->assign('buttonTypeList', $WechatCustomMenu->getButtonTypeList());
            return $this->fetch('customMenu');
        }
    }
}
