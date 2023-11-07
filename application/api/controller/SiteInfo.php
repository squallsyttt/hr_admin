<?php

namespace app\api\controller;

use app\common\model\SiteInformation;
use app\common\controller\Api;

/**
 * 首页接口
 */
class SiteInfo extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $params = $this->request->param();
        $where = ['type' => $params['type'] ?? 'blurb'];
        $info = SiteInformation::where($where)->order('createtime', 'desc')->find();
        $this->success('请求成功', ['data' => $info]);
    }

    public function show($type)
    {
        $where = ['type' => $type ?? 'blurb'];
        $info = SiteInformation::where($where)->order('createtime', 'desc')->find();
        $this->success('请求成功', $info ?: []);
    }
}
