<?php
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-28
 * Time: 10:50
 */
namespace app\common\model;

use think\Cache;

class AdminAuthorityGroup extends Base
{
    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
    }

    //获取权限
    public function getAuthList($group_id)
    {
        $newAuthList = [];
        $where = [];
        $where[] = ['status', '=', 1];
        if($group_id > 0) {
            $groupInfo = $this->detail($group_id, 'authorities');
            if (!empty($groupInfo)) {
                $where['id'] = array('in', $groupInfo['authorities']);
            }
        }

        $authList = model('AdminAuthority')->allList($where,'module,controller,function');
        if (!empty($authList)) {
            foreach ($authList as $k => $v) {
                $newAuthList[$k] = $v['module'] . '/' . $v['controller'] . '/' . $v['function'];
            }
        }

        return $newAuthList;
    }
}