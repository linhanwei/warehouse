<?php
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-28
 * Time: 10:50
 */
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
use think\facade\Cache;

class Base extends Model
{
    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';

    //自动完成
    protected $update = ['update_time'];

    //使用软删除功能
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    //缓存标签
    protected $cache_tag = '';

    //是否使用缓存
    protected $is_use_cache = false;

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        $this->cache_tag = config('database.prefix').strtolower($this->name);
    }

    //添加更新时间
    protected function setUpdateTimeAttr()
    {
        return time();
    }

    //根据ID查询数据详情
    public function detail($id, $field = '*')
    {
        return $this->getInfo(array($this->pk => $id), $field);
    }

    //根据条件查询数据详情
    public function getInfo($where, $field = '*')
    {
        if(empty($where))
        {
            return null;
        }

        $info = $this->where($where)->cache($this->is_use_cache,null,$this->cache_tag)->field($field)->find();
        return $info ? $info->toArray() : $info;
    }

    //获取分页列表
    public function pageList($where = '', $pageTotal = 10, $field = '*', $order_by = '')
    {
        $order_by = empty($order_by) ? $this->pk . ' ASC ' : $order_by;
        if(is_array($field))
        {
            return $this->where($where)->cache($this->is_use_cache,null,$this->cache_tag)->field($field['field'], $field['except'])->order($order_by)->paginate($pageTotal);
        }
        else
        {
            return $this->where($where)->cache($this->is_use_cache,null,$this->cache_tag)->field($field)->order($order_by)->paginate($pageTotal);
        }
    }

    //根据条件查询数据详情
    public function getColumnList($where, $field = '*')
    {
        return $this->where($where)->cache($this->is_use_cache,null,$this->cache_tag)->column($field);
    }

    //获取所有列表
    public function allList($where, $field = "*")
    {
        return $this->where($where)->cache($this->is_use_cache,null,$this->cache_tag)->field($field)->select();
    }

    //获取总数
    public function getCount($where)
    {
        return $this->where($where)->cache($this->is_use_cache,null,$this->cache_tag)->count();
    }

    //添加数据
    public function addData($data)
    {
        $result = $this->isUpdate(false)->allowField(true)->save($data);
        if($result)
        {
            // 清除tag标签的缓存数据
            Cache::clear($this->cache_tag);
        }

        return $result;
    }

    /**
     * 添加多条数据
     *
     * @param $dataList  注意:如果数据带有主键则更新,否则添加
     * @return array|false
     */
    public function saveAllData($dataList)
    {
        $result = $this->allowField(true)->saveAll($dataList);
        if($result)
        {
            // 清除tag标签的缓存数据
            Cache::clear($this->cache_tag);
        }

        return $result;
    }

    //修改数据
    public function editData($where, $data)
    {
        $result = $this->isUpdate(true)->allowField(true)->save($data,$where);
        if($result)
        {
            // 清除tag标签的缓存数据
            Cache::clear($this->cache_tag);
        }

        return $result;
    }

    //删除数据
    public function delData($where)
    {
        $editData[$this->deleteTime] = time();
        $result = $this->allowField(true)->isUpdate(true)->save($editData,$where);

        if($result)
        {
            // 清除tag标签的缓存数据
            Cache::clear($this->cache_tag);
        }
        return $result;
    }

    //清除缓存
    public function clearCache()
    {
        // 清除tag标签的缓存数据
        Cache::clear($this->cache_tag);
    }
}