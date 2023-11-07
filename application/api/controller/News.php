<?php

namespace app\api\controller;

use app\common\model\News as ModelNews;
use app\common\controller\Api;
use think\Db;

/**
 * 示例接口
 */
class News extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];

    protected $map = [
        1 => '新联动态',
        2 => '统战活动',
        3 => '理论学习',
    ];

    public function index()
    {
        $params = $this->request->get();
        $per_page = ($params['per_page'] ?? '') ?: 10;
        $where = [];
        $type = $params['type'] ?? '';
        if (!empty($type)) {
            $where['type'] = ['=', $type];
        }

        // $list = Db::table('fa_news')->paginate(10);
        $list = ModelNews::field('id,title,cover,createtime')->where($where)->paginate($per_page);
        $this->success('返回成功', $list);
    }

    public function show($id)
    {
        $info = ModelNews::find($id);
        $this->success('返回成功', $info);
    }

    public function map()
    {

        var_dump(config('site.newsType'));
    }
}
