<?php

namespace app\common\model;

use think\Model;
use think\Session;

class FeedbackReply extends Model
{

    // use SoftDelete;

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $table = 'fa_feedback_reply';


     public function getCreatetimeAttr($value)
    {
        return date("Y-m-d H:i:s", $value);
    }

    public function feedback()
    {
        return $this->belongsTo(Feedback::class, 'feedback_id', 'id');
    }

}
