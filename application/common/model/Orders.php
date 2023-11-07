<?php

namespace app\common\model;

use think\Model;

class Orders extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    // 定义主键和数据表
    protected $pk = 'id';
    protected $table = 'fa_orders';

    // 定义字段信息
    protected $schema = [
        'id'            => 'int',
        'sn'            => 'string',
        'redeem_code'   => 'string',
        'score'         => 'int',
        // 其他字段...
    ];

    public function getcreatetimeAttr($value)
    {
        return date("Y-m-d H:i:s", $value);
    }

    protected $auto = ['order_sn', 'redeem_code'];

    /**
     * 订单编号
     * @param $value
     * @param $data
     * @return string
     */
    public function setOrderSnAttr($value, $data)
    {
        return date('YmdHis') . rand(1000, 9999);
    }

    /**
     * 兑换码
     * @param $value
     * @param $data
     * @return string
     */
    public function setRedeemCodeAttr($value, $data)
    {
        return md5(uniqid(mt_rand(), true));
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->order_sn = $model->setOrderSnAttr(null, null);
            $model->redeem_code = $model->setRedeemCodeAttr(null, null);
        });
    }

    public function getGoodsImageAttr($value)
    {
        return cdnurl($value, true);
    }
}
