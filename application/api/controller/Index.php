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
     * @ApiTitle    (首页)
     * @ApiSummary  (获取首页信息)
     * @ApiMethod   (GET)
     * @ApiRoute    (/api/index/index)
     */
    public function index()
    {
        $this->success('请求成功');
    }
}
