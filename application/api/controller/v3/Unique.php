<?php
/**
 * Created by PhpStorm.
 * User: chaofu
 * Date: 2019/7/23
 * Time: 17:42
 */

namespace app\api\controller\v3;

/**
 * 插件商店
 */
class Unique extends BaseApi
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
    protected $bk_obj_id = '';

    private $path = '/object/attr';

    public function _initialize()
    {
        parent::_initialize();
        $this->bk_obj_id = 'cc_test_inst';
    }

    /**
     * @ApiTitle    (获取插件列表)
     * @ApiSummary  (获取插件商店的插件列表信息)
     * @ApiMethod   (GET)
     * @ApiRoute    (/api/V3/Model/index)
     */
    public function index($obj)
    {
        $params = array(
            "condition" => array( "bk_obj_id"=>$obj)
        );
        $url = config('fastadmin.cmdb_api_url')."/object/association/action/search";
        return  self::sendRequest($url, \GuzzleHttp\json_encode($params));

    }

    /**
     * @ApiTitle    (获取插件列表)
     * @ApiSummary  (获取插件商店的插件列表信息)
     * @ApiMethod   (DELETE)
     * @ApiParams   (name="id", type="integer", required=true, description="模型ID")
     * @ApiRoute    (/api/v3/Model/{id})
     * 这里返回的是data数组
     */
    public function delete($id)
    {

        $url = config('fastadmin.cmdb_api_url').'/object/association/'.$id.'/action/delete';
        return  self::sendRequest($url, $params=[], 'DELETE');
    }

    /**
     * @ApiTitle    (获取插件列表)
     * @ApiSummary  (获取插件商店的插件列表信息)
     * @ApiMethod   (PUT)
     * @ApiRoute    (/api/v3/Model/{id})
     * 这里返回的是data数组
     */
    public function update($id)
    {

        $params = $this->request->post("row/a");

        // 只允许的字段更新
        $filed = array(
            'bk_asst_id' =>$params['bk_asst_id'],
            'bk_obj_asst_name' =>$params['bk_obj_asst_name']
        );
        $url = config('fastadmin.cmdb_api_url')."/object/association/".$id."/action/update";
        return  self::sendRequest($url, \GuzzleHttp\json_encode($filed), 'PUT');
    }

    /**
     * @ApiTitle    (获取插件列表)
     * @ApiSummary  (获取插件商店的插件列表信息)
     * @ApiMethod   (GET)
     * @ApiParams   (name="bk_obj_id", type="string", required=true, description="对象模型的ID，只能用英文字母序列命名")
     * @ApiRoute    (/api/v3/Model/{id})
     * 这里返回的是data数组
     */
    public function read($id,$obj)
    {

        $params = array(
            "condition" =>array(
                "bk_obj_id"=>$obj,
                "id"=> $id
            )
        );
        $url = config('fastadmin.cmdb_api_url')."/object/association/action/search";
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
        $params['bk_obj_asst_id'] = $params['bk_obj_id'].'_'.$params['bk_asst_id'].'_'.$params['bk_asst_obj_id'];
        $url = config('fastadmin.cmdb_api_url').'/object/association/action/create';
        return  self::sendRequest($url,\GuzzleHttp\json_encode($params));
    }


}
