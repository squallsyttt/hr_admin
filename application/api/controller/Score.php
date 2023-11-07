<?php

namespace app\api\controller;

use app\common\model\Goods;
use app\common\model\Orders;
use app\common\model\User;
use app\common\controller\Api;
use app\common\model\ScoreLog;
use think\Db;
use think\Validate;

/**
 * 示例接口
 */
class Score extends Api
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
        $per_page = ($params['per_page'] ?? '') ?: 20;
        $user = User::field('score')->find($this->auth->id);
        if (!$user) {
            $this->error('用户不存在！！！');
        }

        $time_start = empty($params['time_slot'] ?? '') ? date('Y-m') : $params['time_slot'];
        $start_time = strtotime($time_start);

        $end_time = strtotime('+1 month', $start_time);

        $where = [
            'createtime' => [
                ['>=', $start_time],
                ['<', $end_time]
            ],
            'user_id'   =>  ['=', $this->auth->id]
        ];

        $list = ScoreLog::field('*')->where($where)->order('createtime', 'DESC')->paginate($per_page);
        $this->success('返回成功', ['list' => $list, 'total_score' => $user->score]);
    }

    public function show($id)
    {
        $info = ScoreLog::where('user_id', $this->auth->id)->find($id);

        $this->success('返回成功', ['data' => $info]);
    }
}
