<?php

namespace app\api\controller\v3;



/**
 * 字段分组
 */
class Fieldgroup extends BaseApi
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
     * @ApiTitle    (删除分组)
     * @ApiSummary  (删除分组)
     * @ApiMethod   (DELETE)
     * @ApiRoute    (/api/v3/Model/{id})
     * 这里返回的是json
     */
    public function delete($id)
    {

        $url = config('fastadmin.cmdb_api_url')."/delete/objectattgroup/".$id;
        $result = self::sendRequest($url, $params=[], 'DELETE');
        return  $result;
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
            $params_json = \GuzzleHttp\json_encode($this->request->post("row/a"),JSON_UNESCAPED_UNICODE);
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
    public function save($params)
    {
        $url = config('fastadmin.cmdb_api_url')."/objectatt/group/new";
        $result = self::sendRequest($url, $params);
        return  $result;
    }


    /**
     * @ApiTitle    (改变字段所在分组)
     * @ApiSummary  (改变字段所在分组)
     * @ApiMethod   (PUT)
     * @ApiParams   (Array)
     * @ApiRoute
     * 这里返回的是data数组
     */
    public function attrChangeGroup($params=null){
        if($params==null){
            $params = $this->request->post("row/a");
            if(isset($params['data'])){
                foreach ($params['data'] as &$val){
                    $val['data']['bk_property_index'] = (int)$val['data']['bk_property_index'];
                }
            }
        }
        $url = config('fastadmin.cmdb_api_url').'/objectatt/group/property';
        return self::sendRequest($url, \GuzzleHttp\json_encode($params), 'PUT');

    }


    /**
     * @ApiTitle    改变字段分组名称
     * @ApiSummary  改变字段分组名称
     * @ApiMethod   (PUT)
     * @ApiParams
     * @ApiRoute
     * 这里返回的是json
     *
     */
    public function editGroupName(){

        $paramsArr = $this->request->post("row/a");
        if(isset($paramsArr['condition']['id'])){
            $paramsArr['condition']['id'] = intval( $paramsArr['condition']['id']);
        }
        $params = \GuzzleHttp\json_encode($paramsArr,JSON_UNESCAPED_UNICODE);
        $url = config('fastadmin.cmdb_api_url').'/objectatt/group/update';
        return self::sendRequest($url, $params, 'PUT');

    }

}
