<?php

namespace app\api\controller;

use app\common\model\Feedback as ModelFeedback;
use app\common\controller\Api;
use PSpell\Config;
use think\Db;
use think\Validate;


/**
 * 示例接口
 */
class Feedback extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    // protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];

    /**
     * 列表
     */
    public function index()
    {
        $id = $this->auth->id;
        $params = $this->request->param();
        $per_page = ($params['per_page'] ?? '') ?: 10;

        $type_name = $this->typeName();
        $list = ModelFeedback::field('*')->withCount('replies')->where('user_id', $id)->order('id','DESC')->paginate($per_page)->toArray();
        foreach ($list['data']  as  &$item) {
            $item['type_name'] = $type_name[$item['type']] ?? '其他';
        }
        $this->success('返回成功', ['list' => $list]);
    }

    /**
     * 详情
     */
    public function show($id)
    {
        $info = ModelFeedback::with('replies')->find($id);
        $type_name = $this->typeName();
        $info['type_name'] = $type_name[$info['type']] ?? '其他';
        $this->success('返回成功', $info);
    }

    /**
     * 新建
     */
    public function store()
    {
        $params = $this->request->param();
        $params['user_id'] = $this->auth->id ?? 1;

        if (empty($params['content'] ?? '')) {
            $this->error('未填写内容！！');
        }

        $params['mobile'] = $params['mobile'] ?? '';
        if (!empty($params['mobile'] ?? '') && !Validate::regex($params['mobile'] ?? '', "^1\d{10}$")) {
            $this->error('手机格式不正确');
        }
        ModelFeedback::create($params);
        $this->success("提交成功");
    }

    public function typeName()
    {
        return Config("site.feedbackType");
    }
}
