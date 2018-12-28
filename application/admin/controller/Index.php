<?php
/*
 * 首页控制器
 *
 */
namespace app\admin\controller;

class Index extends Common
{
    //首页
    public function index()
    {
        $this->assign('menu_list', json_encode($this->menuList(),JSON_UNESCAPED_UNICODE));
        return $this->fetch('index');
    }

    //主页
    public function home()
    {
        return $this->fetch('home');
    }

    //菜单处理
    private function menuList()
    {
        $newMenuList = array();
        $menuList = $this->getMenu();

        $id = 1;

        foreach ($menuList as $key => $val)
        {
            $newMenuList[$key]['name'] = $val['name'];
            $newMenuList[$key]['id'] = $id;
            $newMenuList[$key]['parentid'] = 0;
            $newMenuList[$key]['topid'] = 0;
            $newMenuList[$key]['class'] = $val['class'];
            $newMenuList[$key]['url'] = $val['url'] ? url($val['url']) : '';

            $children = $val['children'];

            if(!empty($children))
            {
                $parentid = $topid = $id;
                $newChildren = array();
                foreach ($children as $key1 => $val)
                {
                    $id++;

                    $newChildren[$key1]['name'] = $val['name'];
                    $newChildren[$key1]['id'] = $id;
                    $newChildren[$key1]['parentid'] = $parentid;
                    $newChildren[$key1]['topid'] = $topid;
                    $newChildren[$key1]['class'] = $val['class'];
                    $newChildren[$key1]['reload'] = isset($val['reload']) ? $val['reload'] : 0;
                    $newChildren[$key1]['default'] = isset($val['default']) ? $val['default'] : 0;
                    $newChildren[$key1]['url'] = $val['url'] ? url($val['url']) : '';

                    if(isset($val['children']))
                    {
                        $children = $val['children'];
                        $newChildren1 = array();

                        if(!empty($children))
                        {
                            $parentid = $id;

                            foreach ($children as $key2 => $val)
                            {
                                $id++;

                                $newChildren1[$key2]['name'] = $val['name'];
                                $newChildren1[$key2]['id'] = $id;
                                $newChildren1[$key2]['parentid'] = $parentid;
                                $newChildren1[$key2]['topid'] = $topid;
                                $newChildren1[$key2]['class'] = $val['class'];
                                $newChildren1[$key2]['url'] = $val['url'] ? url($val['url']) : '';
                            }
                        }

                        $newChildren[$key1]['children'] = $newChildren1;
                    }

                    $newMenuList[$key]['children'] = $newChildren;
                }
            }

            $id++;
        }

        return $newMenuList;
    }

    //获取菜单
    private function getMenu()
    {
        $menuList = array(
            array(
                'name' => '企业管理','class' => '','url' => '',
                'children'=> array(
                    array('name' => '企业列表','class' => '','auth' => 'admin/Company/index', 'url' => 'company/index',
                        'children'=> array(
                            //array('name' => '权限管理1','class' => '','auth' => 'admin/AdminAuthority/index','url' =>'/admin_authority/index',),
                        ),
                    ),
                ),
            ),
            array(
                'name' => '商品管理','class' => '','url' => '',
                'children'=> array(
                    array('name' => '商品列表','class' => '','auth' => 'admin/Goods/index', 'url' => 'goods/index', 'default' => 1,
                        'children'=> array(
                            //array('name' => '权限管理1','class' => '','auth' => 'admin/AdminAuthority/index','url' =>'/admin_authority/index',),
                        ),
                    ),
                    array('name' => '品牌列表','class' => '','auth' => 'admin/GoodsBrand/index', 'url' => 'goods_brand/index',
                        'children'=> array(),
                    ),
                    array('name' => '属性列表','class' => '','auth' => 'admin/GoodsAttribute/index', 'url' => 'goods_attribute/index',
                        'children'=> array(),
                    ),
                ),
            ),
            array(
                'name' => '微信管理','class' => '','url' => '',
                'children'=> array(
                    array('name' => '公众号配置列表','class' => '','auth' => 'admin/WechatConfig/index', 'url' => 'wechat_config/index',
                        'children'=> array(),
                    ),
                ),
            ),
            array(
                'name' => '防伪码管理','class' => '','url' => '',
                'children'=> array(
                    array('name' => '生码记录','class' => '','auth' => 'admin/LabelCode/groupIndex', 'url' => 'label_code/groupIndex',
                        'children'=> array(
                            //array('name' => '权限管理1','class' => '','auth' => 'admin/AdminAuthority/index','url' =>'/admin_authority/index',),
                        ),
                    ),
                    array('name' => '条形码列表','class' => '','auth' => 'admin/LabelCode/index', 'url' => 'label_code/index','reload' => 1,
                        'children'=> array(),
                    ),
                    array('name' => '条形码导出','class' => '','auth' => 'admin/LabelCode/exportCode', 'url' => 'label_code/exportCode',
                        'children'=> array(),
                    ),
                ),
            ),
            array(
                'name' => '大数据分析','class' => '','url' => '',
                'children'=> array(
                    array('name' => '总体概况','class' => '','auth' => 'admin/Statistics/index', 'url' => 'statistics/index',
                        'children'=> array(),
                    ),
                    array('name' => '扫码地区统计','class' => '','auth' => 'admin/Statistics/region', 'url' => 'statistics/region',
                        'children'=> array(),
                    ),
                    array('name' => '扫码性别统计','class' => '','auth' => 'admin/Statistics/gender', 'url' => 'statistics/gender',
                        'children'=> array(),
                    ),
                    array('name' => '用户扫码记录','class' => '','auth' => 'admin/Statistics/record', 'url' => 'statistics/record',
                        'children'=> array(),
                    ),
                    array('name' => '用户粉丝分析','class' => '','auth' => 'admin/Statistics/fans', 'url' => 'statistics/fans',
                        'children'=> array(),
                    ),
                ),
            ),
            array(
                'name' => '设置','class' => '','url' => '',
                'children'=> array(
                    array('name' => '管理员管理','class' => '','auth' => 'admin/Admin/index', 'url' => 'admin/index',
                        'children'=> array(),
                    ),
                    array('name' => '权限管理','class' => '','auth' => 'admin/AdminAuthority/index','url' =>'admin_authority/index',
                        'children'=> array(),
                    ),
                    array('name' => '地址管理','class' => '','auth' => 'admin/Region/index','url' =>'region/index',
                        'children'=> array(),
                    ),
                ),
            ),
        );

        return $menuList;
    }
}
