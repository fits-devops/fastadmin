<?php

namespace app\admin\controller\bluewhale;

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
     * Bluewhale模型对象
     * @var \app\admin\model\Bluewhale
     */
    protected $model = null;
    protected $apiModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Bluewhale;
        $this->apiModel= new  apiModel;
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->headers = array('BK_USER:0', 'HTTP_BLUEKING_SUPPLIER_ID:0');

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
        $this->view->assign("rows", $result['data']);
        return $this->view->fetch();
    }


    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $datas_json = $this->apiModel->save();
            $result = json_decode($datas_json,true);
            if($result['result']!=false){
                $this->success('',null,$result['data']);
            }else{
                $this->error( $result['bk_error_msg']);
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        return $this->view->fetch();
    }


    /**
     * 编辑
     */
    public function edit($ids = null)
    {

        $row = '';

        if (!$ids) {
            $this->error(__('No Results were found'));
        }
        //调用接口获取该条记录的内容
        //注意参数id的值必须为整型
        $params = json_encode(['id'=>intval($ids)]);
        $datas_json = $this->apiModel->index($params);
        $datas_arr = json_decode($datas_json,true);
        if($datas_arr['result']){
            $data = $datas_arr['data'];
            $row = $data[0];
            $this->view->assign("row", $row);
        }
        if (!$row) {
            $this->error(__('No Results were found'));
        }

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
        }
        return $this->view->fetch();
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
