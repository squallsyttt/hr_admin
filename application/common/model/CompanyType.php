<?php

namespace app\common\model;

use think\Model;
use think\Session;
// use traits\model\SoftDelete;

class CompanyType extends Model
{

    // use SoftDelete;

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    // 定义时间戳字段名
    // protected $createTime = 'createtime';
    // protected $updateTime = 'updatetime';
    // protected $deleteTime = 'deletetime';
    protected $table = 'fa_company_type';

     // 定义子级关联关系
     public function children()
     {
         return $this->hasMany(get_class($this), 'parent_id', 'id');
     }
}
