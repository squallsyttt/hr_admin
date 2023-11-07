<?php

namespace app\common\model;

use app\common\model\Activity;
use think\Model;
use think\Session;

class ActivityParticipants extends Model
{

     // 开启自动写入时间戳字段
     protected $autoWriteTimestamp = 'int';
     // 定义时间戳字段名
     protected $createTime = 'createtime';
     protected $updateTime = false;
    protected $table = 'fa_activity_participants';

    //设置关联
    protected $relation = [
        'activity' => [
            'type' => 'belongsTo',
            'model' => 'app\common\model\Activity',
            'foreign_key' => 'activity_id',
            'primary_key' => 'id',
        ],
    ];


     public function getCreatetimeAttr($value)
    {
        return date("Y-m-d H:i:s", $value);
    }

    // public function activity()
    // {
    //     return $this->belongsTo(Activity::class,'id','activity_id');
    // }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
