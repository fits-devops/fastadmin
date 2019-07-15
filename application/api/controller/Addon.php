<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Category;
use app\common\model\Version;

/**
 * 插件商店
 */
class Addon extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = '*';

    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Addonstore;
    }

    /**
     * @ApiTitle    (获取插件列表)
     * @ApiSummary  (获取蓝鲸系统的插件列表信息)
     * @ApiMethod   (GET)
     * @ApiRoute    (/api/addon/index)
     */
    public function index()
    {

        //获取启用的插件列表
        $addoninfo = $this->model->getAddonInfo();
        //获取分类数据
        $category = array();
        $categoryAll = Category::getCategoryArray("bluewhale");
        foreach ($categoryAll as $item){
            $category[] = array(
                "id" => $item["id"],
                "name" => $item["name"]
            );
        }

        //返回数据
        $data = [
            'total' => count($addoninfo),
            'rows'   => $addoninfo,
            'category' => $category
        ];

        $this->success('返回成功',$data);
    }

    /**
     * @ApiTitle    (获取插件列表)
     * @ApiSummary  (获取蓝鲸系统的插件列表信息)
     * @ApiMethod   (GET)
     * @ApiRoute    (/api/addon/index)
     * 这里返回的是data数组
     */
    public function addonslist()
    {
        //获取启用的插件列表
        $addoninfo = $this->model->getAddonInfo();
//        dump($addoninfo);
//die;
        //获取分类数据
        $category = array();
        $categoryAll = Category::getCategoryArray("bluewhale");
        foreach ($categoryAll as $item){
            $category[] = array(
                "id" => $item["id"],
                "name" => $item["name"]
            );
        }

        //返回数据
        $data = [
            'total' => count($addoninfo),
            'rows'   => $addoninfo,
            'category' => $category
        ];

//        $this->success('返回成功',$data);
        return $data;
    }
}
