<?php
/**
 * Created by PhpStorm.
 * User: LHW
 * Date: 2018-02-28
 * Time: 10:50
 */
namespace app\common\model;

class WechatUser extends Base
{

    //字段处理
    protected $type = [
        'tagid_list'    =>  'serialize',
    ];

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
    }

}