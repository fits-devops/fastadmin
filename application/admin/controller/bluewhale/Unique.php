<?php

namespace app\admin\controller\bluewhale;

use app\common\controller\Backend;

/**
 * 蓝鲸系统
 *
 * @icon fa fa-circle-o
 */
class Unique extends Backend
{

    /**
     * Bluewhale模型对象
     * @var \app\admin\model\Bluewhale
     */
    protected $model = null;


    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\api\controller\v3\Unique;

    }

    /**
     * 默认生成的控制器所继承的父类s中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        if ($this->request->isAjax())
        {

            $objUnique = $this->model->index();
            $objAttr = $this->model->objectAttr();

            $objUnique = \GuzzleHttp\json_decode($objUnique,true);
            $objAttr = \GuzzleHttp\json_decode($objAttr,true);

            $objUnique = $objUnique['data'];
            $objAttr   = $objAttr['data'];

            $idArr = [];
            foreach ($objAttr as $value){
                $idArr[$value['id']] = $value['bk_property_name'];
            }
            $list = [];
            foreach ($objUnique as $key=>$uniqueArr){
                $arr = [];
                $arr['must_check'] = $uniqueArr['must_check'];
                $idKey = '';
                $name = '';
                foreach ($uniqueArr['keys'] as $val){
                    $idKey .= $val['key_id'].'_';
                    $name .= $idArr[$val['key_id']].'+';
                }
                $arr['ids'] = trim($idKey,'_');
                $arr['id'] = $uniqueArr['id'];
                $arr['name'] = trim($name,'+');
                $list[] = $arr;
            }

            $result = array("total" =>count($list), "rows" => $list);

            return json($result);
        }
        return $this->view->fetch('bluewhale/attr/index');
    }


    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $res =$this->model->save();
            $res =\GuzzleHttp\json_decode($res,true);
            if($res['bk_error_msg']  == 'success'){
                $this->success();
            }else{
                $this->error($res['bk_error_msg']);
            }
        }
        $objAttr = $this->model->objectAttr();
        $objAttr = \GuzzleHttp\json_decode($objAttr,true);
        $this->view->assign("objAttr",$objAttr['data']);
        return $this->view->fetch();
    }


    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $objUnique = $this->model->index();
        $objUnique = \GuzzleHttp\json_decode($objUnique,true);
        $objUnique = $objUnique['data'];
        $flag = false;
        $row = [] ;
        foreach ($objUnique as $value) {
            if ($value['id'] == $ids) {
                foreach ($value['keys'] as $val) {
                    $row['ids'][] = $val['key_id'];
                    $flag = true;
                }
                $row['must_check'] = $value['must_check'];
                break;
            }
        }
        if (!$flag) {
            $this->error(__('No Results were found'));
        }

        $objAttr = $this->model->objectAttr();
        $objAttr = \GuzzleHttp\json_decode($objAttr,true);
        $objAttr = $objAttr['data'];
        foreach ($objAttr as $key=>&$value){
            if(in_array($value['id'], $row['ids'])){
                $value['selected'] = 'selected';
            }else{
                $value['selected'] = '';
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $newData = [];
                foreach ($params['bk_id'] as $val){
                    $newData['keys'][] = array(
                        'key_kind' => 'property',
                        'key_id'=> (int)$val
                    );
                }
                $bool = $params['must_check'] === 'yes' ? true :false;
                $newData['must_check'] = $bool;
                $this->request->input = \GuzzleHttp\json_encode($newData);
                $res =$this->model->update((int)$ids);
                $res =\GuzzleHttp\json_decode($res,true);
                if($res['bk_error_msg']  == 'success'){
                    $this->success();
                }else{
                    $this->error($res['bk_error_msg']);
                }

            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        $this->view->assign("objAttr", $objAttr);
        return $this->view->fetch();
    }

    /**
     * 删除
     */
    public function del($ids = null)
    {

        $res =$this->model->delete((int)$ids);
        $res =\GuzzleHttp\json_decode($res,true);

        if($res['bk_error_msg']  == 'success'){
            $this->success();
        }else{
            $this->error($res['bk_error_msg']);
        }
    }

    public function changIcon($ids){
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $res =$this->model->update((int)$ids);
                $res =\GuzzleHttp\json_decode($res,true);
                if($res['bk_error_msg']  == 'success'){
                    $this->success();
                }else{
                    $this->error($res['bk_error_msg']);
                }

            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
    }

    protected function getUnique()
    {
        $objUnique = $this->model->index();
        $objAttr = $this->model->objectAttr();

        $objUnique = \GuzzleHttp\json_decode($objUnique,true);
        $objAttr = \GuzzleHttp\json_decode($objAttr,true);

        $objUnique = $objUnique['data'];
        $objAttr   = $objAttr['data'];

        $idArr = [];
        foreach ($objAttr as $value){
            $idArr[$value['id']] = $value['bk_property_name'];
        }
        $list = [];
        foreach ($objUnique as $key=>$uniqueArr){
            $arr = [];
            $arr['must_check'] = $uniqueArr['must_check'];
            $idKey = '';
            $name = '';
            foreach ($uniqueArr['keys'] as $val){
                $idKey .= $val['key_id'].'_';
                $name .= $idArr[$val['key_id']].'+';
            }
            $arr['ids'] = trim($idKey,'_');
            $arr['id'] = $uniqueArr['id'];
            $arr['name'] = trim($name,'+');
            $list[] = $arr;
        }
        return $list;
    }








}
