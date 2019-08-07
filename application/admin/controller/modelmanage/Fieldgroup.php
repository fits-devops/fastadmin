<?php

namespace app\admin\controller\modelmanage;

use app\common\controller\Backend;
//use app\api\addon;
//use app\config;
use fast\Http;
use app\api\controller\v3\FieldGroup as apiModel;

/**
 * 字段分类管理
 *
 * @icon fa fa-circle-o
 */
class Fieldgroup extends Backend
{
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = '*';

    protected $model = null;
    protected $apiModel = null;
    protected $FieldModel = null;

    protected $obj = '';


    public function _initialize()
    {
        parent::_initialize();
        $this->FieldModel = new \app\api\controller\v3\Fieldgroup;
        $this->apiModel= new  apiModel;
    }


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
        if($res['data'][0]['bk_ispaused']){
            $res['data'][0]['bk_ispaused'] = 1;
        }else{
            $res['data'][0]['bk_ispaused'] = 0;
        }
        $this->view->assign("res",$res['data'][0]);
        $table4 = $this->table4($res['data'][0]['bk_obj_id']);
        $this->view->assign("table4", $table4['data']);
        $this->view->assign("max_bk_group_index", $table4['max_bk_group_index']);
        return $this->view->fetch();
    }

    //table4的内容,返回为数组
    public function table4($obj)
    {
        $this->obj = $obj;
        $model = new \app\api\controller\v3\Association;
        $res = json_decode($model->showgroup($obj),true);
        $groupdata = json_decode($model->getgroupdata($obj),true);
        //输出的数组
        $result =[];
        //装主要信息的data
        $data = [];
        //不在分组内的none内容
        $none = [];
        //判断是否已经存好none
        $isAlreadyNone = true;
        array_multisort(array_column($groupdata['data'],'bk_property_index'),SORT_ASC,$groupdata['data']);
        array_multisort(array_column($res['data'],'bk_group_index'),SORT_ASC,$res['data']);
//        echo \GuzzleHttp\json_encode($res['data']);
        //获得最大的bk_group_index值
        $maxarr = end($res['data']);
        $result['max_bk_group_index'] = $maxarr['bk_group_index'];
//        dump($maxarr['bk_group_index']);
//        echo $maxarr['bk_property_index'];
//        echo \GuzzleHttp\json_encode($groupdata['data']);
//        die;
        foreach($res['data'] as $resK=>$resV){
            foreach($groupdata['data'] as $gK =>$gV){
                if($resV['bk_group_id'] == $gV['bk_property_group']){
                    $data[$resV['bk_group_name']][] = [
                        'bk_group_id'=>$resV['bk_group_id'],
                        'bk_property_id'=>$gV['bk_property_id'],
                        'bk_property_group'=>$gV['bk_property_group'],
                        'bk_property_index'=>$gV['bk_property_index'],
                        'bk_property_name'=>$gV['bk_property_name'],
                    ];
                }
                //还有一种情况就是不在分组内的,这时bk_property_group为none
                elseif($gV['bk_property_group'] == 'none' &&$isAlreadyNone ==true){
                    $none[] =[
                        'bk_group_id'=>$resV['bk_group_id'],
                        'bk_property_id'=>$gV['bk_property_id'],
                        'bk_property_group'=>$gV['bk_property_group'],
                        'bk_property_index'=>$gV['bk_property_index'],
                        'bk_property_name'=>$gV['bk_property_name'],
                    ];
                }
            }
            //none数组已完成,将$isAlreadyNone改为false阻止其下次继续加数组到none数组内
            $isAlreadyNone = false;
        }
        //将none数组加入$data后面.保证none数组是最后一个数组
        $data['更多属性']=$none;
        $result['data']=$data;
//            $data['max_bk_group_index'] = $maxarr['bk_group_index'];
//            echo \GuzzleHttp\json_encode($result);
//            die;
        return $result;
    }


    //分组字段添加
    public function add(){
        $paramsArr = $this->request->post("row/a");
        $params = \GuzzleHttp\json_encode($paramsArr,JSON_UNESCAPED_UNICODE);
        $datas_json =$this->apiModel->save($params);
//        $this->error($datas_json);
        $result = json_decode($datas_json,true);
        if($result['result']!=false){
            $this->success('',null,$result['data']);
        }else{
            $this->error( $result['bk_error_msg']);
        }
    }


    //分段组名编辑
    public function edit($ids = null){
        $datas_json =$this->apiModel->editGroupName();
        $result = json_decode($datas_json,true);
        if($result['result']!=false){
            $this->success('',null,$result['data']);
        }else{
            $this->error( $result['bk_error_msg']);
        }
    }


    //删除分组信息
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

    public function attrChangeGroup()
    {

        $res = $this->FieldModel->attrChangeGroup();
        $result = \GuzzleHttp\json_decode($res,true);
        if($result['result']!=false){
            $this->success();

        }else{
            $this->error( $result['bk_error_msg']);
        }
    }


}
