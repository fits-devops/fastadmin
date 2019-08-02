<?php

namespace app\api\controller\v3;



/**
 * 插件商店
 */
class Model extends BaseApi
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
    }

    /**
     * @ApiTitle    (获取插件列表)
     * @ApiSummary  (获取插件商店的插件列表信息)
     * @ApiMethod   (GET)
     * @ApiRoute    (/api/V3/Model/index)
     */
    public function index($params = [])
    {

        $url = config('fastadmin.cmdb_api_url')."/object/classification/0/objects";
        $result = self::sendRequest($url, $params, 'post');
        return  $result;

    }

    /**
     * @ApiTitle    (获取插件列表)
     * @ApiSummary  (获取插件商店的插件列表信息)
     * @ApiMethod   (DELETE)
     * @ApiParams   (name="id", type="integer", required=true, description="模型ID")
     * @ApiRoute    (/api/v3/Model/{id})
     * 这里返回的是json
     */
    public function delete($id)
    {

        $url = config('fastadmin.cmdb_api_url')."/delete/object/".$id;
        return self::sendRequest($url, $params=[], 'DELETE');
    }

    /**
     * @ApiTitle    (获取插件列表)
     * @ApiSummary  (获取插件商店的插件列表信息)
     * @ApiMethod   (PUT)
     * @ApiRoute    (/api/v3/Model/{id})
     * 这里返回的是json
     */
    public function update($id)
    {
        $content = $this->request->getInput();
        if($this->is_json($content)){
            $params_json = $content;
        }else{
            $params = $this->request->post("row/a");
            if(isset($params['bk_ispaused'])){
                if($params['bk_ispaused'] == 'true'){
                    $params['bk_ispaused'] = true;
                }else{
                    $params['bk_ispaused'] = false;
                }
            }
            $params_json = \GuzzleHttp\json_encode($params,JSON_UNESCAPED_UNICODE);
        }

        $url = config('fastadmin.cmdb_api_url')."/update/object/".$id;
        $result = self::sendRequest($url,$params_json, 'PUT');
        return  $result;
    }

    /**
     * @ApiTitle    (获取插件列表)
     * @ApiSummary  (获取插件商店的插件列表信息)
     * @ApiMethod   (GET)
     * @ApiParams   (name="bk_obj_id", type="string", required=true, description="对象模型的ID，只能用英文字母序列命名")
     * @ApiRoute    (/api/v3/Model/{bk_obj_id})
     * 这里返回的是josn字符串
     */
    public function read($bk_obj_id)
    {

        $params = array(
            "bk_obj_id"=> $bk_obj_id,
            "bk_supplier_account"=>"0",
        );
        $url = config('fastadmin.cmdb_api_url')."/objects";
        return  self::sendRequest($url, \GuzzleHttp\json_encode($params));
    }

    /**
     * @ApiTitle    (新增模型)
     * @ApiSummary  (新增模型)
     * @ApiMethod   (POST)
     * @ApiParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据")
     * @ApiRoute    (/api/v3/Model/{id})
     * 这里返回的是josn字符串
     */
    public function save()
    {
        $params_json = \GuzzleHttp\json_encode($this->request->post("row/a"),JSON_UNESCAPED_UNICODE);
        $url = config('fastadmin.cmdb_api_url')."/object";
        $result = self::sendRequest($url, $params_json);
        return  $result;
    }

    public function updateIcon($id)
    {
        $params = $this->request->post("row/a");
        $url = config('fastadmin.cmdb_api_url')."/update/object/".$id;
        return  self::sendRequest($url, \GuzzleHttp\json_encode($params), 'PUT');

    }
}
