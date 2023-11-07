<?php

namespace app\api\controller;

use app\common\model\Activity as ModelActivity;
use app\common\model\ActivityParticipants;
use app\common\controller\Api;
use app\common\model\User;
use think\Db;

/**
 * 示例接口
 */
class Activity extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['index'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];

    public function index()
    {
        $params = $this->request->param();
        $per_page = ($params['per_page'] ?? '') ?: 10;
        $list = ModelActivity::field('id,title,cover,address,start_time,end_time,sign_start_time,sign_end_time,createtime')->paginate($per_page);
        $now = date("Y-m-d H:i:s");
        foreach ($list as $item) {
            $item['status'] = 0;
            if ($now >= $item['start_time'] && $now <= $item['end_time']) {
                $item['status'] = 1;
            } else if ($now >= $item['end_time']) {
                $item['status'] = 2;
            }
        }
        $this->success('请求成功', $list);
    }

    /**
     * 
     */
    public function show($id)
    {
        $info = ModelActivity::find($id);

        $participants = User::alias('u')->join('activity_participants', 'user_id=u.id')->where('activity_id', $id)->field(['u.id', 'activity_participants.name', 'u.avatar', 'u.nickname'])->limit(7)->select();
        $info->setRelation('participants', $participants);
        $info['signtime'] = ActivityParticipants::where('activity_id', $id)->where('user_id', $this->auth->id)->find()['signtime'] ?? 0;
        $info['sign_status'] = $this->signStatus($info);
        $count = ActivityParticipants::where('activity_id', $id)->count();
        $this->success('请求成功', ['data' => $info, 'count' => $count]);
    }

    public function join()
    {
        $params = $this->request->param();
        $per_page = ($params['per_page'] ?? '') ?: 10;
        $user_id = $this->auth->id;

        $where['user_id'] = ['=', $user_id];

        $list =   ModelActivity::alias('a')
            ->join('activity_participants', 'a.id = activity_participants.activity_id')
            ->order('a.start_time', 'desc')
            ->paginate($per_page);

        foreach ($list as $item) {
            $item['sign_status'] = $this->signStatus($item);
        }
        $this->success('请求成功', $list);
    }


    public function sign_list()
    {
        $user_id = $this->auth->id;
        $params = $this->request->param();
        $per_page = ($params['per_page'] ?? '') ?: 10;

        $where['user_id'] = ['=', $user_id];
        $where['sign_start_time'] = ['<=', time()];
        $where['sign_end_time'] = ['>=', time()];

        $list = ModelActivity::alias('a')
            ->join('activity_participants', 'a.id = activity_participants.activity_id')
            ->where($where)
            ->order('sign_start_time', 'asc')
            ->paginate($per_page);

        foreach ($list as $item) {
            $item['sign_status'] = $this->signStatus($item);
        }
        $this->success('请求成功', $list);
    }


    public function signStatus($item)
    {
        $now = date("Y-m-d H:i:s");

        if ($item['signtime'] > 0 || $item['is_sign'] != 1) {
            $signStatus = 1;    //已签到
        } elseif ($item['sign_start_time'] <= $now && $item['sign_end_time'] >= $now) {
            $signStatus = 2;    //可签到
        } elseif ($item['sign_start_time'] > $now) {
            $signStatus = 3;    //未到签到时间
        } elseif ($item['sign_end_time'] < $now) {
            $signStatus = 4;    //签到结束
        }
        return $signStatus;
    }

    public function apply()
    {
        $params = $this->request->param();
        $name = $params['name'] ?? '';
        $mobile = $params['mobile'] ?? '';
        $avatar = $params['avatar'] ?? '';
        $activity_id = $params['id'] ?? '';

        $now = date("Y-m-d H:i:s");
        if (empty($activity_id)) {
            $this->error('报名活动未选择！！');
        }
        if (empty($name)) {
            $this->error('姓名未填写！！');
        }

        if (empty($mobile)) {
            $this->error('手机号未填写！！');
        }
        if (empty($avatar)) {
            $this->error('头像未填写！！');
        }
        $activity = ModelActivity::get($activity_id);

        if (empty($activity)) {
            $this->error('活动不存在！！！');
        }
        if ($now > $activity['end_time'] || ($activity['is_sign'] == 1 && $now > $activity['sign_end_time'])) {
            $this->error('活动已结束报名');
        }

        if ($activity['max_num'] > 0 && $activity->current_participants >= $activity->max_num) {
            $this->error('活动参与人数已满');
        }

        $user_id = $this->auth->id;
        $is_join = ActivityParticipants::where('user_id', $user_id)->where('activity_id', $activity_id)->find();


        if (!empty($is_join)) {
            $this->error('已经报名活动，请勿重复报名');
        }
        ActivityParticipants::create([
            'activity_id' => $activity_id,
            'user_id' => $user_id,
            'name' => $name,
            'mobile' => $mobile,
            'avatar' => $avatar,
        ]);

        $activity->current_participants++;
        $activity->save();
        $this->success('报名成功');
    }

    /**
     * 签到
     */
    public function sign()
    {
        $params = $this->request->param();
        $activity_id = $params['id'] ?? '';
        if (empty($activity_id)) {
            $this->error('活动未选择！！');
        }
        $user_id = $this->auth->id;

        $sign = ActivityParticipants::where('user_id', $user_id)->where('activity_id', $activity_id)->find();

        if ($sign['signtime'] > 0) {
            $this->error('已经签过到啦~请勿重复签到');
        }
        $activity_info = ModelActivity::where('id', $activity_id)->find();

        $now = date("Y-m-d H:i:s");

        if ($activity_info['sign_start_time'] > $now) {
            $this->error('还未到签到时间哦~');
        }

        if ($activity_info['sign_end_time'] != 0 && $activity_info['sign_end_time'] < $now) {
            $this->error('签到时间已过！！');
        }

        ActivityParticipants::where('user_id', $user_id)->where('activity_id', $activity_id)->update(['signtime' => time()]);
        if ($activity_info['sign_score'] > 0) {
            User::score($activity_info['sign_score'], $user_id, '活动签到');
        }
        $this->success('请求成功', ['msg' => '签到成功' . ($activity_info['sign_score'] ? ',积分+' . $activity_info['sign_score'] : '')]);
    }
}
