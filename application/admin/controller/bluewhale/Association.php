<?php

namespace app\admin\controller\bluewhale;

use app\common\controller\Backend;

use app\api\controller\v3\Model;
/**
 * 蓝鲸系统
 *
 * @icon fa fa-circle-o
 */
class Association extends Backend
{
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = '*';
    /**
     * Bluewhale模型对象
     * @var \app\admin\model\Bluewhale
     */
    protected $model = null;


    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\api\controller\v3\Association;

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

            $res =$this->model->index();
            $res =\GuzzleHttp\json_decode($res,true);
            $list = $res['data'];

            $result = array("total" =>count($list), "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * 添加
     */
    public function add($obj = null)
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
            'belong' =>'belong(属于)',
            'group' => 'group(组成)',
            'run' => 'run(运行)',
            'connect' => 'connect(上联)',
            'default' => 'default(默认关联)',
        );
        $mapping = array(
            '1:1' =>'1-1',
            '1:n' => '1-N',
            'n:n' => 'N-N'
        );
        $mode = new Model();
        $arr = $mode->index();
        $this->view->assign("item",$item);
        $this->view->assign("modelGroup",$arr['data']);
        $this->view->assign("mapping",$mapping);
        $this->view->assign("obj",$obj);
        return $this->view->fetch();
    }


    /**
     * 编辑
     */
    public function edit($ids = null, $obj=null)
    {
        $row = $this->model->read((int)$ids,$obj);
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
                    $this->error(__('No rows were updated'));
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

    public function changIcon($ids){
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
    }








}
