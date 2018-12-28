<?php
/**
 * 地区管理控制器
 *
 */
namespace app\admin\controller;

class Region extends Common
{
    //列表
    public function index()
    {
        $field_list = array(
            'region_name' => '地区名称',
        );

        $where = array();
        $field = input('field');
        $keyword = input('keyword');
        if(isset($field_list[$field]) && !empty($keyword))
        {
            $where[$field] = array('like', $keyword.'%');
        }

        $list = model('Region')->pageList($where, self::PAGE_TOTAL);

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
            $validate = validate('Region');

            if(!$validate->scene('add')->check($input_arr)){
                $this->error($validate->getError());
            }
            else
            {
                $region_name = input('region_name');

                $RegionModel = model('Region');

                //验证地区名称与账号是否存在
                $count = $RegionModel->getCount(array('region_name' => $region_name));

                if($count > 0)
                {
                    $this->error('地区名称已经存在!');
                }

                //处理上级ID
                $pids = input('pid/a');
                $input_arr['pid'] = empty($pids[1]) ? $pids[0] : $pids[1];

                //处理等级
                $input_arr['level'] = 1;
                if(!empty($input_arr['pid']))
                {
                    //获取上级信息
                    $parentInfo = $RegionModel->getInfo(array('id' => $input_arr['pid']));
                    $input_arr['level'] = $parentInfo['level'] + 1;
                }

                $result = $RegionModel->addData($input_arr);

                if($result)
                {
                    $this->success('添加成功', url('region/index'));
                }
                else
                {
                    $this->error('添加失败');
                }
            }
        }
        else
        {
            $this->assign('provinceList',$this->_getProvinceList());
            return $this->fetch('add');
        }
    }

    //修改
    public function edit()
    {
        $id = input('id');

        if(empty($id))
        {
            $this->error('请选择需要修改的地区!');
        }

        $RegionModel = model('Region');

        //判断地区是否存在
        $regionInfo = $RegionModel->detail($id);

        if(empty($regionInfo))
        {
            $this->error('选择的地区不存在!');
        }

        if(request()->isPost())
        {
            $input_arr = input();
            $validate = validate('Region');

            if(!$validate->scene('edit')->check($input_arr)){
                $this->error($validate->getError());
            }
            else
            {
                $region_name = input('region_name');

                //验证地区名称与账号是否存在
                $nameWhere['region_name'] = $region_name;
                $nameWhere['id'] = array('neq', $id);
                $count = $RegionModel->getCount($nameWhere);

                if($count > 0)
                {
                    $this->error('地区名称已经存在!');
                }

                //处理上级ID
                $pids = input('pid/a');
                $input_arr['pid'] = 0;
                if(!empty($pids))
                {
                    $input_arr['pid'] = empty($pids[1]) ? $pids[0] : $pids[1];
                }

                //处理等级
                $input_arr['level'] = 1;
                if(!empty($input_arr['pid']))
                {
                    //获取上级信息
                    $parentInfo = $RegionModel->getInfo(array('id' => $input_arr['pid']));
                    $input_arr['level'] = $parentInfo['level'] + 1;
                }

                $result = $RegionModel->editData(array('id'=> $id), $input_arr);

                if($result)
                {
                    $this->success('修改成功', url('region/index'));
                }
                else
                {
                    $this->error('修改失败');
                }
            }
        }
        else
        {
            //判断修改地区信息是否是区县
            $cityList = array();
            $city_id = 0;
            if($regionInfo['level'] == 3)
            {
                //获取市信息
                $cityInfo = $RegionModel->getInfo(array('id' => $regionInfo['pid']));

                $cityList = $this->_getCityList($cityInfo['pid']);
                $city_id = $regionInfo['pid'];
                $province_id = $cityInfo['pid'];
            }
            else
            {
                $province_id = $regionInfo['pid'];
            }

            $this->assign('regionInfo', $regionInfo);
            $this->assign('provinceList',$this->_getProvinceList());
            $this->assign('cityList',$cityList);
            $this->assign('province_id',$province_id);
            $this->assign('city_id',$city_id);
            return $this->fetch('edit');
        }
    }

    //删除
    public function del()
    {
        $id = input('id');

        if(empty($id))
        {
            $this->error('请选择需要删除的地区!');
        }

        $RegionModel = model('Region');

        //判断地区是否存在
        $regionInfo = $RegionModel->detail($id, 'id');

        if(empty($regionInfo))
        {
            $this->error('选择的地区不存在!');
        }

        $result = $RegionModel->delData(array('id'=>$id));

        if($result)
        {
            $this->success('删除成功!',url('region/index'));
        }
        else
        {
            $this->error('删除失败!');
        }
    }

    //根据上级ID获取子集地区
    public function getRegionList()
    {
        $pid = input('pid');

        $return_data = array('status' => 0, 'data'=> array());

        $list = model('Region')->allList(array('pid' => $pid), 'id,region_name');
        if(!empty($list))
        {
            $return_data['status'] = 1;
            $return_data['data'] = $list;
        }

        return json($return_data);
    }

    //获取省份列表
    private function _getProvinceList()
    {
        return model('Region')->allList(array('level' => 1), 'id,region_name');
    }

    //获取市列表
    private function _getCityList($province_id)
    {
        return model('Region')->allList(array('pid' => $province_id), 'id,region_name');
    }
}
