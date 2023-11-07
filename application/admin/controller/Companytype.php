<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 公司类型
 *
 * @icon fa fa-circle-o
 */
class Companytype extends Backend
{

    /**
     * Companytype模型对象
     * @var \app\admin\model\Companytype
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Companytype;

    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


     public function parent()
     {
        $result = [];
        $searchValue = $this->request->request("searchValue");
        if (!empty($searchValue)) {
            $result = $this->model->field('id,name')->where('id',$searchValue)->select();
        } else {
            $result = $this->model->where('parent_id',0)->select();
            $result = array_merge([['id'=>0,'parent_id'=>0,'name'=>'无']],json_decode(json_encode($result),true));
        }
        return ['list' => $result, 'total' => count($result)];
     }
}
