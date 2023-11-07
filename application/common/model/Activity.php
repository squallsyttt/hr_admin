<?php

namespace app\common\model;

use think\Model;
use think\Session;
// use traits\model\SoftDelete;

class Activity extends Model
{

    // use SoftDelete;

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // protected $deleteTime = 'deletetime';
    protected $table = 'fa_activity';


    public function getCreatetimeAttr($value)
    {
        return date("Y-m-d H:i:s", $value);
    }

    public function sponsor()
    {
        return $this->belongsTo(User::class, 'sponsor', 'id');
    }

    /**
     * 远程一对多
     */
    public function participants()
    {
        return $this->belongsToMany(User::class, 'activity_participants', 'activity_id', 'user_id');
    }

    // public function participants()
    // {
    //     return $this->hasMany('activity_participants', 'activity_id', 'id');
    // }


    public function getStartTimeAttr($value)
    {
        return date("Y-m-d H:i:s", $value);
    }

    public function getEndTimeAttr($value)
    {
        return date("Y-m-d H:i:s", $value);
    }

    public function getSignStartTimeAttr($value)
    {
        return date("Y-m-d H:i:s", $value);
    }

    public function getSignEndTimeAttr($value)
    {
        return date("Y-m-d H:i:s", $value);
    }

    public function getCoverAttr($value)
    {
        return cdnurl($value, true);
    }
    
    public function getBannerAttr($value)
    {
        return cdnurl($value, true);
    }
}
