<?php

namespace app\common\model;

use think\Model;
use think\Session;

class Feedback extends Model
{

    // use SoftDelete;

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $table = 'fa_feedback';



    public function getCreatetimeAttr($value)
    {
        return date("Y-m-d H:i:s", $value);
    }

    public function replies()
    {
        return $this->hasMany(FeedbackReply::class, 'feedback_id', 'id');
    }

    public function reply()
    {
        return $this->replies()->count();
    }

    /**
     * 添加钩子，不然create的时候mobile字段存的是0
     */
    protected static function init()
    {
        // Before insert hook
        self::beforeInsert(function ($data) {
            // If the mobile field is empty, set it to null
            if (empty($data['mobile'])) {
                $data['mobile'] = null;
            }
        });
    }


    public function getImageAttr($value)
    {
        return $value ? cdnurl($value, true) : '';
    }
}
