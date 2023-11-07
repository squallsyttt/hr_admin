<?php

namespace app\common\model;

use think\Model;
use think\Session;

class SiteInformation extends Model
{


    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    public function getcreatetimeAttr($value)
    {
        return date("Y-m-d H:i:s", $value);
    }


}
