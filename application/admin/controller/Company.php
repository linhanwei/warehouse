<?php
/**
 * 公司管理
 */
namespace app\admin\controller;

class Company extends Common
{
    //列表
    public function index()
    {
        $whereField = array(
            'field' => 'business_license_img,trademark_img,other_img',
            'except' => true,
        );

        $field_list = array(
            'name' => '供应商名称',
            'legal_person' => '法人',
            'mobile' => '手机号码',
        );

        $where = array();
        $field = input('field');
        $keyword = input('keyword');
        if(isset($field_list[$field]) && !empty($keyword))
        {
            $where[$field] = $keyword;
        }

        $list = model('Company')->pageList($where, self::PAGE_TOTAL, $whereField);
        $this->assign('field_list', $field_list);
        $this->assign('list', $list);

        return $this->fetch('index');
    }

    //添加
    public function add()
    {
        if(request()->isPost())
        {
            /*//图片处理
            $trademark_img_arr = input('trademark_img/a');
            if(move_file($trademark_img_arr))
            {
                $trademark_img = implode(',', array_map('handle_upload_deposit_path', $trademark_img_arr));
            }*/

            $input_arr = input();
            $validate = validate('Company');

            if(!$validate->scene('add')->check($input_arr)){
                $this->error($validate->getError());
            }
            else
            {
                $name = input('name');

                $companyModel = model('Company');

                //验证名称是否已经存在
                $count = $companyModel->getCount(array('name' => $name));

                if($count > 0)
                {
                    $this->error('该供应商已经存在!');
                }

                $result = $companyModel->addData($input_arr);

                if($result)
                {
                    //移动图片
                    move_file(input('business_license_img/a'));
                    move_file(input('trademark_img/a'));
                    move_file(input('other_img/a'));

                    $this->success('添加成功', url('company/index'));
                }
                else
                {
                    $this->error('添加失败');
                }
            }
        }
        else
        {
            $this->assign('bankList',$this->_getBankList());
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
            $this->error('请选择需要修改的供应商!');
        }

        $companyModel = model('Company');

        //判断管理员是否存在
        $companyInfo = $companyModel->detail($id);

        if(empty($companyInfo))
        {
            $this->error('选择的供应商不存在!');
        }

        if(request()->isPost())
        {
            $input_arr = input();
            $validate = validate('Company');

            if(!$validate->scene('edit')->check($input_arr)){
                $this->error($validate->getError());
            }
            else
            {
                $name = input('name');

                //验证供应商是否已存在
                $countWhere['id'] = array('neq', $id);
                $countWhere['name'] = $name;
                $count = $companyModel->getCount($countWhere);
                if($count > 0)
                {
                    $this->error('供应商名称已经存在!');
                }

                //删除图片数据
                $input_arr['business_license_img'] = input('business_license_img/a');
                $input_arr['trademark_img'] = input('trademark_img/a');
                $input_arr['other_img'] = input('other_img/a');

                $result = $companyModel->editData(array('id'=> $id), $input_arr);

                if($result)
                {
                    //移动图片
                    move_file(input('business_license_img/a'));
                    move_file(input('trademark_img/a'));
                    move_file(input('other_img/a'));

                    $this->success('修改成功', url('company/index'));
                }
                else
                {
                    $this->error('修改失败');
                }
            }
        }
        else
        {
            $this->assign('companyInfo', $companyInfo);
            $this->assign('bankList',$this->_getBankList());
            $this->assign('provinceList',$this->_getProvinceList());
            $this->assign('cityList',$this->_getCityList($companyInfo['province_id']));
            $this->assign('districtList',$this->_getDistrictList($companyInfo['city_id']));

            return $this->fetch('edit');
        }
    }

    //删除
    public function del()
    {
        $id = input('id');

        if(empty($id))
        {
            $this->error('请选择需要删除的供应商!');
        }

        $companyModel = model('Company');

        //判断管理员是否存在
        $companyInfo = $companyModel->detail($id, 'id');

        if(empty($companyInfo))
        {
            $this->error('选择的供应商不存在!');
        }

        $result = $companyModel->delData(array('id'=>$id));

        if($result)
        {
            $this->success('删除成功!', url('company/index'));
        }
        else
        {
            $this->error('删除失败!');
        }
    }

    //获取银行列表
    private function _getBankList()
    {
        return model('Bank')->allList(array(), 'id,name');
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

    //获取区县列表
    private function _getDistrictList($city_id)
    {
        return model('Region')->allList(array('pid' => $city_id), 'id,region_name');
    }

}
