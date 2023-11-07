<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {

        $newsType = config('site.newsType');
        $banner = config('site.banner');
        foreach ($banner as &$value) {
            $value =  cdnurl($value, true);;
        }

        $this->success('请求成功', ['banner' => $banner, 'newsType' => $newsType]);
    }
}
