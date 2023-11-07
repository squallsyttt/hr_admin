<?php

namespace app\api\controller;

use app\common\model\Activity as ModelActivity;
use app\common\model\ActivityParticipants;
use app\common\model\Goods as ModelGoods;
use app\common\model\User;
use app\common\controller\Api;
use think\Db;

/**
 * 示例接口
 */
class Goods extends Api
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
        $list = ModelGoods::field('*')->paginate($per_page);
        $this->success('返回成功', $list);
    }

    public function show($id)
    {
        $info = ModelGoods::find($id);

        $user_score = User::where('id', $this->auth->id)->value('score');
        $this->success('返回成功', ['data' => $info, 'user_score' => $user_score]);
    }
}
