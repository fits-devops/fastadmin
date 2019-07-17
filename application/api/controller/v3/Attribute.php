<?php

namespace app\api\controller\v3;


use fast\Http;
use think\Exception;
use think\Request;

/**
 * 字段
 */
class Attribute extends BaseApi
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

    private $path = '/object/attr';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * @ApiTitle    (查看属性列表)
     * @ApiSummary  (获取插件商店的插件列表信息)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/V3/Model/index)
     */
    public function index()
    {
        $params = $this->request->post("row/a");
        $url = config('fastadmin.cmdb_api_url')."/object/attr/search";
        return  self::sendRequest($url, $params);

    }

    /**
     * @ApiTitle    (删除字段)
     * @ApiSummary  (获取插件商店的插件列表信息)
     * @ApiMethod   (DELETE)
     * @ApiParams   (name="id", type="integer", required=true, description="模型ID")
     * @ApiRoute    (/api/v3/Model/{id})
     * 这里返回的是data数组
     */
    public function delete($id)
    {

        $url = config('fastadmin.cmdb_api_url').$this->path.'/'.$id;
        return  self::sendRequest($url, $params=[], 'DELETE');
    }

    /**
     * @ApiTitle    (更新字段)
     * @ApiSummary  (获取插件商店的插件列表信息)
     * @ApiMethod   (PUT)
     * @ApiRoute    (/api/v3/Model/{id})
     * 这里返回的是data数组
     */
    public function update($id)
    {

        $params = $this->request->post("row/a");
        $url = config('fastadmin.cmdb_api_url')."/object/attr/".$id;
        return  self::sendRequest($url, $params, 'PUT');
    }

    /**
     * @ApiTitle    (获取插件列表)
     * @ApiSummary  (获取插件商店的插件列表信息)
     * @ApiMethod   (GET)
     * @ApiParams   (name="bk_obj_id", type="string", required=true, description="对象模型的ID，只能用英文字母序列命名")
     * @ApiRoute    (/api/v3/Model/{id})
     * 这里返回的是data数组
     */
    public function read($id)
    {

        $params = array(
            "id"=> $id,
        );
        $url = config('fastadmin.cmdb_api_url')."/attr/search";
        return  self::sendRequest($url, \GuzzleHttp\json_encode($params));
    }

    /**
     * @ApiTitle    (获取插件列表)
     * @ApiSummary  (获取插件商店的插件列表信息)
     * @ApiMethod   (GET)
     * @ApiParams   (name="bk_obj_id", type="string", required=true, description="对象模型的ID，只能用英文字母序列命名")
     * @ApiRoute    (/api/v3/Model/{id})
     * 这里返回的是data数组
     */
    public function save()
    {

        $params = $this->request->post("row/a");
        $url = config('fastadmin.cmdb_api_url').$this->path;
        return  self::sendRequest($url, $params);
    }


}
