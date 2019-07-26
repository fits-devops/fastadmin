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
    public function index()
    {
        $obj = $this->request->param('obj', 'host');
        $url = config('fastadmin.cmdb_api_url')."/find/objectunique/object/".$obj;
        return  self::sendRequest($url);

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
        $obj = $this->request->param('obj', 'host');
        $url = config('fastadmin.cmdb_api_url').'/delete/objectunique/object/'.$obj.'/unique/'.$id;
        $params = [];
        return  self::sendRequest($url, $params, 'POST');
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

        $content = $this->request->getInput();
        $obj = $this->request->param('obj', 'host');
        if($this->is_json($content)){
            $newData =$content;
        }else{
            $params = $this->request->post("row/a");
            $newData = [];
            foreach ($params['bk_id'] as $val){
                $newData['keys'][] = array(
                    'key_kind' => 'property',
                    'key_id'=> (int)$val
                );
            }
            $bool = $params['must_check'] === 'yes' ? true :false;
            $newData['must_check'] = $bool;
            $newData = \GuzzleHttp\json_encode($newData);
        }

        $url = config('fastadmin.cmdb_api_url')."/update/objectunique/object/".$obj."/unique/".$id;
        return  self::sendRequest($url,$newData, 'PUT');
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
        $obj = $this->request->param('obj', 'host');
        $newData = [];
        foreach ($params['bk_id'] as $val){
            $newData['keys'][] = array(
                'key_kind' => 'property',
                'key_id'=> (int)$val
            );
        }
        $bool = $params['must_check'] === 'yes' ? true :false;
        $newData['must_check'] = $bool;
        $url = config('fastadmin.cmdb_api_url').'/create/objectunique/object/'.$obj;
        return  self::sendRequest($url,\GuzzleHttp\json_encode($newData));
    }

    /**
     * @ApiTitle    (获取插件列表)
     * @ApiSummary  (获取对象的所有字段)
     * @ApiMethod   (GET)
     * @ApiParams   (name="bk_obj_id", type="string", required=true, description="对象模型的ID，只能用英文字母序列命名")
     * @ApiRoute    (/api/v3/Model/{id})
     * 这里返回的是data数组
     */
    public function objectAttr()
    {
        $obj = $this->request->param('obj', 'host');
        $params = array(
            "bk_obj_id"=>$obj,
        );
        $url = config('fastadmin.cmdb_api_url')."/find/objectattr";
        return  self::sendRequest($url, \GuzzleHttp\json_encode($params));

    }

    public function test()
    {
        return '122';
    }


}
