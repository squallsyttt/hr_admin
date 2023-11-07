<?php

namespace app\common\model;

use think\Model;
use think\Session;
// use traits\model\SoftDelete;

class Goods extends Model
{

    // use SoftDelete;

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // protected $deleteTime = 'deletetime';
    protected $table = 'fa_goods';


     public function getCreatetimeAttr($value)
    {
        return date("Y-m-d H:i:s", $value);
    }

    public function getGoodsImageAttr($value)
    {
        return cdnurl($value, true);
    }
}
