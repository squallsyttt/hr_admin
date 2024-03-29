<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Activity extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'activity';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'is_sign_text',
        'sign_start_time_text',
        'sign_end_time_text',
        'start_time_text',
        'end_time_text'
    ];
    

    
    public function getIsSignList()
    {
        return ['0' => __('Is_sign 0'), '1' => __('Is_sign 1')];
    }


    public function getIsSignTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_sign']) ? $data['is_sign'] : '');
        $list = $this->getIsSignList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getSignStartTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['sign_start_time']) ? $data['sign_start_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getSignEndTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['sign_end_time']) ? $data['sign_end_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStartTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['start_time']) ? $data['start_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getEndTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['end_time']) ? $data['end_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setSignStartTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setSignEndTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setStartTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setEndTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
