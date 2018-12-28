<?php
/**
 * 商品属性控制器
 */
namespace app\admin\controller;

class GoodsAttribute extends Common
{
    //列表
    public function index()
    {
        $field_list = array(
            'attr_name' => '属性名称',
        );

        $where = array();
        $field = input('field');
        $keyword = input('keyword');
        if(isset($field_list[$field]) && !empty($keyword))
        {
            $where[$field] = $keyword;
        }

        $list = model('GoodsAttribute')->pageList($where, self::PAGE_TOTAL, 'id,attr_name,attr_sort,update_time,create_time', 'attr_sort');

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
            $validate = validate('GoodsAttribute');

            if(!$validate->scene('add')->check($input_arr)){
                $this->error($validate->getError());
            }
            else
            {
                $attr_name = input('attr_name');

                $GoodsAttributeModel = model('GoodsAttribute');

                //验证属性名称与账号是否存在
                $count = $GoodsAttributeModel->getCount(array('attr_name' => $attr_name));

                if($count > 0)
                {
                    $this->error('属性名称已经存在!');
                }

                $result = $GoodsAttributeModel->addData($input_arr);

                if($result)
                {
                    //添加属性值,属性值相同的话,去掉相同的值
                    $attr_value_names = array_unique(input('attr_value_name/a'));
                    foreach ($attr_value_names as $k => $value)
                    {
                        $add_value_list[$k]['attr_value_name'] = $value;
                        $add_value_list[$k]['attr_id'] = $GoodsAttributeModel->id;
                    }

                    model('GoodsAttributeValue')->saveAllData($add_value_list);

                    $this->success('添加成功', url('goods_attribute/index'));
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
            $this->error('请选择需要修改的属性!');
        }

        $GoodsAttributeModel = model('GoodsAttribute');

        //判断属性是否存在
        $attrInfo = $GoodsAttributeModel->detail($id);
        if(empty($attrInfo))
        {
            $this->error('选择的属性不存在!');
        }

        if(request()->isPost())
        {
            $input_arr = input();
            $validate = validate('GoodsAttribute');

            if(!$validate->scene('edit')->check($input_arr)){
                $this->error($validate->getError());
            }
            else
            {
                $attr_name = input('attr_name');

                //验证属性名称与账号是否存在
                $nameWhere['attr_name'] = $attr_name;
                $nameWhere['id'] = array('neq', $id);
                $count = $GoodsAttributeModel->getCount($nameWhere);

                if($count > 0)
                {
                    $this->error('属性名称已经存在!');
                }

                $result = $GoodsAttributeModel->editData(array('id'=> $id), $input_arr);

                if($result)
                {
                    //修改属性值
                    $goodsAttributeValueModel = model('GoodsAttributeValue');
                    $attr_value_names = input('attr_value_name/a');
                    $attr_value_ids = input('value_ids/a');

                    //查找原来所有的属性值
                    $valueWhere['attr_id'] = $id;
                    $value_list_ids = $goodsAttributeValueModel->getColumnList($valueWhere, 'id');

                    foreach ($attr_value_names as $k => $value)
                    {
                        $value_id = isset($attr_value_ids[$k]) ? intval($attr_value_ids[$k]) : 0;
                        if(in_array($value_id, $value_list_ids))
                        {
                            //修改的数据
                            $edit_value_list[$k]['attr_value_name'] = $value;
                            $edit_value_list[$k]['id'] = $value_id;
                        }
                        else
                        {
                            //新增的数据
                            $edit_value_list[$k]['attr_value_name'] = $value;
                            $edit_value_list[$k]['attr_id'] = $GoodsAttributeModel->id;
                        }
                    }

                    //多条数据插入
                    $goodsAttributeValueModel->saveAllData($edit_value_list);

                    //删除多余的属性
                    if(!empty($value_list_ids) && !empty($attr_value_ids))
                    {
                        $del_value_ids = array_diff($value_list_ids,$attr_value_ids);
                        if(!empty($del_value_ids))
                        {
                            $delWhere['id'] = array('in', $del_value_ids);
                            $goodsAttributeValueModel->delData($delWhere);
                        }
                    }

                    $this->success('修改成功', url('goods_attribute/index'));
                }
                else
                {
                    $this->error('修改失败');
                }
            }
        }
        else
        {
            //获取属性值
            $valueList = model('GoodsAttributeValue')->allList(array('attr_id' => $id), "id,attr_value_name");

            $this->assign('attrInfo', $attrInfo);
            $this->assign('valueList', $valueList);
            return $this->fetch('edit');
        }
    }

    //删除
    public function del()
    {
        $id = input('id');

        if(empty($id))
        {
            $this->error('请选择需要删除的属性!');
        }

        $GoodsAttributeModel = model('GoodsAttribute');

        //判断属性是否存在
        $attrInfo = $GoodsAttributeModel->detail($id, 'id');

        if(empty($attrInfo))
        {
            $this->error('选择的属性不存在!');
        }

        $result = $GoodsAttributeModel->delData(array('id'=>$id));

        if($result)
        {
            //删除属性值
            model('GoodsAttributeValue')->delData(array('attr_id'=>$id));

            $this->success('删除成功!',url('goods_attribute/index'));
        }
        else
        {
            $this->error('删除失败!');
        }
    }
}
