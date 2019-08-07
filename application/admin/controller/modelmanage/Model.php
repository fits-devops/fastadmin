<?php

namespace app\admin\controller\modelmanage;

use app\common\controller\Backend;
use think\Cache;
use think\Exception;
use app\api\addon;
use app\config;
use app\api\controller\v3\Model as apiModel;

/**
 * 蓝鲸系统
 *
 * @icon fa fa-circle-o
 */
class Model extends Backend
{
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['getAddonsList'];
    /**
     * Modelmanage模型对象
     * @var \app\admin\model\Modelmanage
     */
    protected $apiModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->apiModel= new  apiModel;
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {

        $datas_json = $this->apiModel->index();
        $result = json_decode($datas_json,true);
        $isPausedArr = [];
        foreach ($result['data'] as $key => $val){
            foreach ($val['bk_objects'] as $k => $value){
                // 过滤已经停用的模型
                if($value['bk_ispaused']){
                    unset($result['data'][$key]['bk_objects'][$k]);
                    $isPausedArr[$key]['bk_objects'][] = $value;
                    $isPausedArr[$key]['bk_classification_id'] = $val['bk_classification_id'];
                    $isPausedArr[$key]['bk_classification_name'] = $val['bk_classification_name'];
                    $isPausedArr[$key]['id'] = $val['id'];
                }
            }
        }
        $this->view->assign("rows", $result['data']);
        $this->view->assign("rows1", $isPausedArr);
        return $this->view->fetch();
    }


    /**
     * 添加
     */
    public function add()
    {


        if ($this->request->isPost()) {
            $datas_json =$this->apiModel->save();
            $result = json_decode($datas_json,true);
            if($result['result']!=false){
                $this->success('',null,$result['data']);
            }else{
                $this->error( $result['bk_error_msg']);
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $groupModel = new \app\api\controller\v3\Classification;
        $json = $groupModel->classifications();
        $groupArr = \GuzzleHttp\json_decode($json, true);
        if(isset($groupArr['data'])){
            foreach ($groupArr['data'] as $key => $val){
                if(in_array($val['bk_classification_id'],array('bk_host_manage','bk_biz_topo',
                    'bk_organization'))){
                    unset($groupArr['data'][$key]);
                }
            }
        }
        $this->view->assign("groupArr", $groupArr['data']);
        return $this->view->fetch();
    }


    /**
     * 编辑
     */
    public function edit($ids = null)
    {

        //修改内容
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $datas_json = $this->apiModel->update($ids);
            $result = json_decode($datas_json,true);
            if($result['result']!=false){
                $this->success('操作成功',null,$params);
            }else{
                $this->error( $result['bk_error_msg']);
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }else{
            $row = '';

            if (!$ids) {
                $this->error(__('No Results were found'));
            }
            //调用接口获取该条记录的内容
            //注意参数id的值必须为整型
            $datas_json = $this->apiModel->read(intval($ids));
            $datas_arr = json_decode($datas_json,true);
            if($datas_arr['result']){
                $data = $datas_arr['data'];
                $row = $data[0];
                $this->view->assign("row", $row);
            }
            if (!$row) {
                $this->error(__('No Results were found'));
            }
            return $this->view->fetch();
        }

    }

    /**
     * 删除
     */
    public function del($ids = null)
    {

        if($ids){
            $datas_json = $this->apiModel->delete($ids);
            $result = json_decode($datas_json,true);
            if($result['result']!=false){
                $this->success();
            }else{
                $this->error( $result['bk_error_msg']);
            }

        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
        return $this->view->fetch();
    }


    public function changIcon($ids){
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $res =$this->apiModel->update((int)$ids);
                $res =\GuzzleHttp\json_decode($res,true);
                if($res['bk_error_msg']  == 'success'){
                    $this->success();
                }else{
                    $this->error(__('No rows were deleted'));
                }

            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
    }




}
