<?php
/**
 * 统计分析
 */
namespace app\admin\controller;

class Statistics extends Common
{
    //总体概况
    public function index()
    {
        for($i = 6; $i >= 0; $i--)
        {
            $date_list[] = date('Y-m-d',strtotime('-'. $i .' day'));
            $data_list[] = rand(1,10000000);
        }

        $this->assign('date_list', json_encode($date_list));
        $this->assign('data_list', json_encode($data_list));
        return $this->fetch('index');
    }

    //扫码地区统计
    public function region()
    {
        $provinceList = $this->_handle_province();
        $field_list = array();
        $this->assign('field_list', $field_list);
        $this->assign('goodsList', $this->_getGoodsList());
        $this->assign('provinceList', $provinceList);
        return $this->fetch('region');
    }

    //扫码性别统计
    public function gender()
    {
        //性别比例
        $gender_proportion = array(
            array('name' => "男", 'value' =>50),
            array('name' => "女", 'value' =>20),
            array('name' => "未知", 'value' =>30),
        );

        //新用户比例
        $new_user = [
                        ['value' => rand(1,1000), 'name' => '新用户'],
                        ['value' => rand(1,10000), 'name' => '老用户'],
                    ];

        //地区分布前五
        $regional_distribution = [
            ['product', '男', '女', '未知'],
            ['第一名', 43.3, 85.8,10],
            ['第二名', 83.1, 73.4 , 12],
            ['第三名', 86.4, 65.2, 20],
            ['第四名', 72.4, 53.9, 30],
            ['第五名', 72.4, 53.9, 40]
        ];

        $this->assign('gender_proportion', json_encode($gender_proportion));
        $this->assign('new_user', json_encode($new_user));
        $this->assign('regional_distribution', json_encode($regional_distribution));
        $this->assign('goodsList', $this->_getGoodsList());
        return $this->fetch('gender');
    }

    //用户扫码记录
    public function record()
    {
        $where = array();
        $field = input('field');
        $keyword = input('keyword');
        if(isset($field_list[$field]) && !empty($keyword))
        {
            $where[$field] = $keyword;
        }

        $list = model('ScanRecord')->pageList($where, self::PAGE_TOTAL);

        $this->assign('list', $list);
        return $this->fetch('record');
    }

    //用户粉丝分析
    public function fans()
    {

    }

    //处理省份数据,将省份后面文字,如: 省,自治区
    private function _handle_province()
    {
        //需要去掉的文字
        $del_words = array(
            '省',
            '壮族自治区',
            '回族自治区',
            '维吾尔自治区',
            '特别行政区',
            '自治区',
        );

        $provinceList = $this->_getProvinceList();
        $newProvinceList = array();
        foreach ($provinceList as $k => $val)
        {
            $newProvinceList[$k]['id'] = $val['id'];
            foreach ($del_words as $word)
            {
                $newProvinceList[$k]['region_name'] = str_replace($word,'',$val['region_name']);
                //如果两个值不一样时推出循环
                if($val['region_name'] != $newProvinceList[$k]['region_name'])
                {
                    break;
                }
            }
        }

        return $newProvinceList;
    }

    //获取省份列表
    private function _getProvinceList()
    {
        return model('Region')->allList(array('level' => 1), 'id,region_name');
    }

    //获取产品
    private function _getGoodsList()
    {
        //获取所有的商品
        $goodsList = model('Goods')->allList('', "id,goods_name");
        return $goodsList;
    }
}
