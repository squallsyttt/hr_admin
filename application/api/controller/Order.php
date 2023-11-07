<?php

namespace app\api\controller;

use app\common\model\Goods;
use app\common\model\Orders;
use app\common\model\User;
use app\common\controller\Api;
use app\common\model\User as ModelUser;
use think\Db;
use think\Validate;

/**
 * 示例接口
 */
class Order extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    // protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];

    public function index()
    {
        $params = $this->request->param();
        $per_page = ($params['per_page'] ?? '') ?: 10;
        $list = Orders::field('*')->where('buyer_id', $this->auth->id)->paginate($per_page);
        $this->success('返回成功', $list);
    }

    public function show($id)
    {
        $info = Orders::where('buyer_id', $this->auth->id)->find($id);

        $this->success('返回成功', $info);
    }


    public function store()
    {
        // 获取用户提交的订单信息
        $data = input('post.');
        if (!Validate::regex($data['mobile'] ?? '', "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (empty($data['goods_id'] ?? '')) {
            $this->error('兑换商品信息未填写');
        }
        $goods = Goods::where('is_on', 1);
        $goods_info = $goods->find($data['goods_id']);
        if (empty($goods_info)) {
            $this->error('商品不存在');
        }
        if ($goods_info['inventory'] == 0) {
            $this->error('商品库存不足');
        }
        $user_id = $this->auth->id;
        $user_score = User::where('id', $user_id)->value('score');
        $num = ($data['num'] ?? '') ?: 1;
        $score = $goods_info['score'] * $num;

        if ($user_score < $score) {
            $this->error('用户积分不足');
        }

        // 在数据库中创建新的订单记录
        $order = new Orders();

        $insertData['goods_id'] = $goods_info['id'];
        $insertData['goods_name'] = $goods_info['goods_name'];
        $insertData['goods_image'] = $goods_info['goods_image'];
        $insertData['buyer_id'] = $this->auth->id;
        $insertData['mobile'] = $data['mobile'];
        $insertData['num'] = $num;
        $insertData['score'] = $goods_info['score'];

        // 在数据库中创建新的订单记录
        Db::startTrans();
        try {
            $order = $order->create($insertData);
            // 减少库存
            $goods_info->setDec('inventory');
            $goods_info->save(); // 提交更新到数据库
            $order_id = $order->id; // 获取新订单的自增 ID
            ModelUser::score(-$score, $user_id, '兑换' . $goods_info['goods_name'] . '商品');
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error('下单失败',$e->getMessage());
        }
        $this->success('下单成功', ['order_id' => $order_id]);
    }
}
