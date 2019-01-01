<?php
/**
 * 商品管理
 */
namespace app\admin\controller;

class Goods extends Common
{
    //列表
    public function index()
    {
        $field_list = array(
            'goods_name' => '商品名称',
        );

        $where = array();
        $field = input('field');
        $keyword = input('keyword');
        if(isset($field_list[$field]) && !empty($keyword))
        {
            $where[$field] = $keyword;
        }

        $list = model('GoodsCommon')->pageList($where, self::PAGE_TOTAL);

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
            $validate = validate('Goods');

            if(!$validate->scene('add')->check($input_arr)){
                $this->error($validate->getError());
            }
            else
            {
                $goods_name = input('goods_name');

                $GoodsCommonModel = model('GoodsCommon');

                //验证商品名称是否存在
                $count = $GoodsCommonModel->getCount(array('goods_name' => $goods_name));

                if($count > 0)
                {
                    $this->error('商品名称已经存在!');
                }

                //开启事务
                $GoodsCommonModel->startTrans();

                //添加商品公共信息
                $result = $GoodsCommonModel->addData($input_arr);

                if($result) {
                    //添加商品表
                    $GoodsModel = model('Goods');

                    $addGoodsData['goods_commonid'] = $GoodsCommonModel->id;
                    $addGoodsData['goods_name'] = $input_arr['goods_name'];
                    $addGoodsData['brand_id'] = $input_arr['brand_id'];
                    $addGoodsData['goods_image'] = $input_arr['goods_image'][0];
                    $addGoodsData['company_id'] = $GoodsCommonModel->company_id;
                    $addGoodsData['goods_price'] = $input_arr['goods_costprice'];
                    $addGoodsData['goods_marketprice'] = $input_arr['goods_marketprice'];
                    //$addGoodsData['goods_promotion_price'] = $input_arr['goods_promotion_price'];
                    $result = $GoodsModel->addData($addGoodsData);

                    if ($result) {
                        //添加属性
                        $attr_ids = input('attr_id/a');
                        $attr_value_ids = input('attr_value_id/a');
                        if (!empty($attr_ids)) {
							$addAttrList = [];
                            foreach ($attr_ids as $k => $val) {
                                if(empty($val) || empty($attr_value_ids[$k]))
                                {
                                    continue;
                                }
                                $addAttrList[$k]['goods_id'] = $GoodsModel->id;
                                $addAttrList[$k]['goods_commonid'] = $GoodsCommonModel->id;
                                $addAttrList[$k]['attr_id'] = $val;
                                $addAttrList[$k]['attr_value_id'] = $attr_value_ids[$k];
                                $addAttrList[$k]['company_id'] = $GoodsCommonModel->company_id;
                            }

                            $result = model('GoodsAttrIndex')->saveAllData($addAttrList);
                        }
                    }
                }

                if($result)
                {
                    //事务提交
                    $GoodsCommonModel->commit();

                    //移动图片
                    move_file(input('goods_image/a'));

                    $this->success('添加成功', url('goods/index'));
                }
                else
                {
                    //事务回滚
                    $GoodsCommonModel->rollback();

                    $this->error('添加失败');
                }
            }
        }
        else
        {
            $this->assign('brankList', model('GoodsBrand')->allList('', 'id,brand_name'));
            $this->assign('companyList', model('Company')->allList('', 'id,name'));
            $this->assign('attrList', model('GoodsAttribute')->allList('', 'id,attr_name'));
            return $this->fetch('add');
        }
    }

    //修改
    public function edit()
    {
        $id = input('id');

        if(empty($id))
        {
            $this->error('请选择需要修改的商品!');
        }

        $GoodsCommonModel = model('GoodsCommon');

        //判断管理员是否存在
        $goodsCommonInfo = $GoodsCommonModel->detail($id);

        if(empty($goodsCommonInfo))
        {
            $this->error('选择的商品不存在!');
        }

        if(request()->isPost())
        {
            $input_arr = input();
            $validate = validate('Goods');

            if(!$validate->scene('add')->check($input_arr)){
                $this->error($validate->getError());
            }
            else
            {
                $goods_name = input('goods_name');

                $GoodsCommonModel = model('GoodsCommon');

                //验证商品名称是否存在
				$countWhere = [];
                $countWhere[] = ['id', 'neq', $id];
                $countWhere[] = ['goods_name', '=', $goods_name];
                $count = $GoodsCommonModel->getCount($countWhere);

                if($count > 0)
                {
                    $this->error('商品名称已经存在!');
                }

                //开启事务
                $GoodsCommonModel->startTrans();

                //修改商品公共信息
                $result = $GoodsCommonModel->editData(array('id' => $id), $input_arr);

                if($result) {
                    //修改商品表
                    $GoodsModel = model('Goods');

                    $editGoodsData['goods_name'] = $input_arr['goods_name'];
                    $editGoodsData['brand_id'] = $input_arr['brand_id'];
                    $editGoodsData['goods_image'] = $input_arr['goods_image'][0];
                    $editGoodsData['company_id'] = $GoodsCommonModel->company_id;
					$editGoodsData['goods_price'] = $input_arr['goods_costprice'];
					$editGoodsData['goods_marketprice'] = $input_arr['goods_marketprice'];
					//$editGoodsData['goods_promotion_price'] = $input_arr['goods_promotion_price'];
                    $result = $GoodsModel->editData(array('goods_commonid' => $id), $editGoodsData);

                    if ($result) {
                        $GoodsAttrIndexModel = model('GoodsAttrIndex');
                        //获取所有的商品属性ID
                        $save_attr_value_ids = $GoodsAttrIndexModel->getColumnList(array('goods_commonid' => $id), 'id');

                        //修改属性
                        $attr_ids = input('attr_id/a');
                        $attr_value_ids = input('attr_value_id/a');
                        $goods_attr_ids = input('goods_attr_ids/a');

                        if (!empty($attr_ids)) {
                            //获取商品
                            $goodsInfo = $GoodsModel->getInfo(array('goods_commonid' => $id));

                            foreach ($attr_ids as $k => $val) {
                                if(isset($goods_attr_ids[$k]))
                                {
                                    $addAttrList[$k]['id'] = $goods_attr_ids[$k];
                                    $addAttrList[$k]['attr_id'] = $val;
                                    $addAttrList[$k]['attr_value_id'] = $attr_value_ids[$k];
                                    $addAttrList[$k]['company_id'] = $GoodsCommonModel->company_id;
                                }
                                else
                                {
                                    $addAttrList[$k]['goods_id'] = $goodsInfo['id'];
                                    $addAttrList[$k]['goods_commonid'] = $id;
                                    $addAttrList[$k]['attr_id'] = $val;
                                    $addAttrList[$k]['attr_value_id'] = $attr_value_ids[$k];
                                    $addAttrList[$k]['company_id'] = $GoodsCommonModel->company_id;
                                }
                            }

                            $result = $GoodsAttrIndexModel->saveAllData($addAttrList);

                            //删除多余的属性
                            if(!empty($save_attr_value_ids) && !empty($goods_attr_ids))
                            {
                                $del_attr_value_ids = array_diff($save_attr_value_ids,$goods_attr_ids);
                                if(!empty($del_attr_value_ids))
                                {
                                    $delWhere['id'] = array('in', $del_attr_value_ids);
                                    $result = $GoodsAttrIndexModel->delData($delWhere);
                                }
                            }
                        }
                    }
                }

                if($result)
                {
                    //事务提交
                    $GoodsCommonModel->commit();

                    //移动图片
                    move_file(input('goods_image/a'));

                    $this->success('修改成功', url('goods/index'));
                }
                else
                {
                    //事务回滚
                    $GoodsCommonModel->rollback();

                    $this->error('修改失败');
                }
            }
        }
        else
        {
            //处理商品已添加的属性
            $saveAttrList = model('GoodsAttrIndex')->allList(array('goods_commonid' => $id), 'id,attr_id,attr_value_id');
            $attrSaveList = array();
            if(!empty($saveAttrList))
            {
                $GoodsAttributeValueModel = model('GoodsAttributeValue');
                foreach ($saveAttrList as $k => $val)
                {
                    $attrSaveList[$k]['id'] = $val['id'];
                    $attrSaveList[$k]['attr_id'] = $val['attr_id'];
                    $attrSaveList[$k]['attr_value_id'] = $val['attr_value_id'];
                    $attrSaveList[$k]['attr_value_list'] = $GoodsAttributeValueModel->allList(array('attr_id' => $val['attr_id']), 'id,attr_value_name');
                }
            }

            $this->assign('attrSaveList', $attrSaveList);
            $this->assign('goodsInfo', $goodsCommonInfo);
            $this->assign('brankList', model('GoodsBrand')->allList('', 'id,brand_name'));
            $this->assign('companyList', model('Company')->allList('', 'id,name'));
            $this->assign('attrList', model('GoodsAttribute')->allList('', 'id,attr_name'));
            return $this->fetch('edit');
        }
    }

    //删除
    public function del()
    {
        $id = input('id');

        if(empty($id))
        {
            $this->error('请选择需要删除的商品!');
        }

        $GoodsCommonModel = model('GoodsCommon');

        //判断商品是否存在
        $goodsCommonModelInfo = $GoodsCommonModel->detail($id, 'id');

        if(empty($goodsCommonModelInfo))
        {
            $this->error('选择的商品不存在!');
        }

        //开启事务
        $GoodsCommonModel->startTrans();

        //删除公共商品
        $result = $GoodsCommonModel->delData(array('id'=>$id));

        if($result)
        {
            //删除商品
            $result = model('Goods')->delData(array('goods_commonid'=>$id));

            if($result)
            {
                //删除商品属性
                $result = model('GoodsAttrIndex')->delData(array('goods_commonid'=>$id));
            }
        }
        if($result)
        {
            //提交事务
            $GoodsCommonModel->commit();

            $this->success('删除成功!',url('goods/index'));
        }
        else
        {
            //事务回滚
            $GoodsCommonModel->rollback();

            $this->error('删除失败!');
        }
    }

    //获取属性值
    public function getAttrValueList()
    {
        $attr_id = input('attr_id');
        $return_data = array('status' => 0, 'data'=> array());

        $list = model('GoodsAttributeValue')->allList(array('attr_id' => $attr_id), 'id,attr_value_name');

        if(!empty($list))
        {
            $return_data['status'] = 1;
            $return_data['data'] = $list;
        }

        return json($return_data);
    }
}
