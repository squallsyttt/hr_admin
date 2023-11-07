<?php

namespace app\common\model;

use think\Model;
use think\Session;
use traits\model\SoftDelete;


class News extends Model
{
    use SoftDelete;

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';
    protected $table = 'fa_news';


     public function getCreatetimeAttr($value)
    {
        return date("Y-m-d H:i:s", $value);
    }

    public function author()
    {
        # code...
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    public function getCoverAttr($value)
    {
        return cdnurl($value, true);
    }
}
