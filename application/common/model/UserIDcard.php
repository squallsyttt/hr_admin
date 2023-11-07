<?php

namespace app\common\model;

use think\Model;
use think\Session;

class UserIDcard extends Model
{


    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $table = 'fa_user_id_card';


     public function getCreatetimeAttr($value)
    {
        return date("Y-m-d H:i:s", $value);
    }

    public function participants()
    {
        # code...
    }

}
