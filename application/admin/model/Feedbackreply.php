<?php

namespace app\admin\model;

use think\Model;


class Feedbackreply extends Model
{

    

    

    // 表名
    protected $name = 'feedback_reply';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







    public function feedback()
    {
        return $this->belongsTo('Feedback', 'feedback_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
