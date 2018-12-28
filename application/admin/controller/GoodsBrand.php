<?php
/**
 * 商品品牌控制器
 *
 */
namespace app\admin\controller;

class GoodsBrand extends Common
{
    //列表
    public function index()
    {
        $field_list = array(
            'brand_name' => '品牌名称',
        );

        $where = array();
        $field = input('field');
        $keyword = input('keyword');
        if(isset($field_list[$field]) && !empty($keyword))
        {
            $where[$field] = $keyword;
        }

        $list = model('GoodsBrand')->pageList($where, self::PAGE_TOTAL);

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
            $validate = validate('GoodsBrand');

            if(!$validate->scene('add')->check($input_arr)){
                $this->error($validate->getError());
            }
            else
            {
                $brand_name = input('brand_name');

                $GoodsBrandModel = model('GoodsBrand');

                //验证品牌名称与账号是否存在
                $count = $GoodsBrandModel->getCount(array('brand_name' => $brand_name));

                if($count > 0)
                {
                    $this->error('品牌名称已经存在!');
                }
                
                $result = $GoodsBrandModel->addData($input_arr);

                if($result)
                {
                    $this->success('添加成功', url('goods_brand/index'));
                }
                else
                {
                    $this->error('添加失败');
                }
            }
        }
        else
        {
            return $this->fetch('add');
        }
    }

    //修改
    public function edit()
    {
        $id = input('id');

        if(empty($id))
        {
            $this->error('请选择需要修改的品牌!');
        }

        $GoodsBrandModel = model('GoodsBrand');

        //判断品牌是否存在
        $brandInfo = $GoodsBrandModel->detail($id);

        if(empty($brandInfo))
        {
            $this->error('选择的品牌不存在!');
        }

        if(request()->isPost())
        {
            $input_arr = input();
            $validate = validate('GoodsBrand');

            if(!$validate->scene('edit')->check($input_arr)){
                $this->error($validate->getError());
            }
            else
            {
                $brand_name = input('brand_name');

                //验证品牌名称与账号是否存在
                $nameWhere['brand_name'] = $brand_name;
                $nameWhere['id'] = array('neq', $id);
                $count = $GoodsBrandModel->getCount($nameWhere);

                if($count > 0)
                {
                    $this->error('品牌名称已经存在!');
                }

                $result = $GoodsBrandModel->editData(array('id'=> $id), $input_arr);

                if($result)
                {
                    $this->success('修改成功', url('goods_brand/index'));
                }
                else
                {
                    $this->error('修改失败');
                }
            }
        }
        else
        {
            $this->assign('brandInfo', $brandInfo);
            return $this->fetch('edit');
        }
    }

    //删除
    public function del()
    {
        $id = input('id');

        if(empty($id))
        {
            $this->error('请选择需要删除的品牌!');
        }

        $GoodsBrandModel = model('GoodsBrand');

        //判断品牌是否存在
        $brandInfo = $GoodsBrandModel->detail($id, 'id');

        if(empty($brandInfo))
        {
            $this->error('选择的品牌不存在!');
        }

        $result = $GoodsBrandModel->delData(array('id'=>$id));

        if($result)
        {
            $this->success('删除成功!',url('goods_brand/index'));
        }
        else
        {
            $this->error('删除失败!');
        }
    }
}
