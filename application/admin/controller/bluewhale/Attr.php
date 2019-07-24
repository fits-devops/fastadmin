<?php

namespace app\admin\controller\bluewhale;

use app\common\controller\Backend;
use app\api\addon;
use app\config;

/**
 * 蓝鲸系统
 *
 * @icon fa fa-circle-o
 */
class Attr extends Backend
{
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = '*';
    /**
     * Bluewhale模型对象
     * @var \app\admin\model\Bluewhale
     */
    protected $model = null;

    protected $obj = '';


    public function _initialize()
    {
        parent::_initialize();
        $statusList = array(
           'first'=> '模型字段',
            'second'=>'模型关联',
            'three'=>'唯一校验',
           'four' => '字段分组',
        );
        $this->model = new \app\api\controller\v3\Attribute;
        $this->view->assign("statusList",$statusList);
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
        $obj = $this->request->param('obj', 'host');
        if ($this->request->isAjax())
        {

            $res =$this->model->index();
            $res =\GuzzleHttp\json_decode($res,true);
            $list = $res['data'];

            $result = array("total" =>count($list), "rows" => $list);

            return json($result);
        }
        $model = new \app\api\controller\v3\Model;
        $res = $model->read($obj);
        $res = \GuzzleHttp\json_decode($res,true);
        $this->view->assign("res",$res['data'][0]);
        return $this->view->fetch();
    }

    public function table1($obj)
    {
        if ($this->request->isAjax())
        {

            $res =$this->model->index();
            $res =\GuzzleHttp\json_decode($res,true);
            $list = $res['data'];

            $result = array("total" =>count($list), "rows" => $list);

            return json($result);
        }
        return $this->view->fetch('index');
    }

    public function table2($obj)
    {
        $this->obj = $obj;
        if ($this->request->isAjax())
        {
            $model = new \app\api\controller\v3\Association;
            $res = $model->index($obj);
            $res =\GuzzleHttp\json_decode($res,true);
            $list = $res['data'];

            $result = array("total" =>count($list), "rows" => $list,'d'=>$obj);

            return json($result);
        }
        return $this->view->fetch('index');
    }

    public function table3()
    {
        if ($this->request->isAjax())
        {
            $model = new \app\api\controller\v3\Association;
            $res = $model->index();
            $res =\GuzzleHttp\json_decode($res,true);
            $list = $res['data'];

            $result = array("total" =>count($list), "rows" => $list);

            return json($result);
        }
        return $this->view->fetch('index');
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
                $this->error(__('Parameter %s can not be empty', ''));
            }
        }
        $item = array(
            'singlechar' =>'短字符',
            'int' => '数字',
            'float' => '浮点数',
            'enum' => '枚举'
        );
        $obj = $this->request->param('obj', 'host');
        $this->view->assign("item",$item);
        $this->view->assign("obj",$obj);
        return $this->view->fetch();
    }


    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->read((int)$ids);
        $row = \GuzzleHttp\json_decode($row,true);
        $row = $row['data'][0];
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $res =$this->model->update((int)$ids);
                $res =\GuzzleHttp\json_decode($res,true);
                if($res['bk_error_msg']  == 'success'){
                    $this->success();
                }else{
                    $this->error(__('No rows were deleted'));
                }

            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
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
            $this->error(__('No rows were deleted'));
        }
    }


}
