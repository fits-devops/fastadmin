<?php

namespace app\admin\controller\modelmanage;

use app\common\controller\Backend;
use app\api\addon;
use app\config;
use fast\Http;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * 模型的详情页面
 *
 * @icon fa fa-circle-o
 */
class Attr extends Backend
{
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = '*';
    /**
     * Modelmanage模型对象
     * @var \app\admin\model\Modelmanage
     */
    protected $model = null;
    protected $FieldModel = null;

    protected $obj = '';

    protected $typeArr = array(
        'singlechar' =>'短字符',
        'int' => '数字',
        'float' => '浮点数',
        'enum' => '枚举',
        'date' => '日期',
        'time' => '时间',
        'longchar' => '长字符',
        'objuser' => '用户',
        'timezone' => '时区',
        'bool' => 'bool',
    );

    public function _initialize()
    {
        parent::_initialize();
        $statusList = array(
           'first'=> '模型字段',
            'second'=>'模型关联',
            'third'=>'唯一校验',
           'fourth' => '字段分组',
        );
        $this->model = new \app\api\controller\v3\Attribute;
        $this->FieldModel = new \app\api\controller\v3\Fieldgroup;
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
        if($res['data'][0]['bk_ispaused']){
            $res['data'][0]['bk_ispaused'] = 1;
        }else{
            $res['data'][0]['bk_ispaused'] = 0;
        }
        $btnAttr = [
            'import'  => ['javascript:;', 'btn btn-info btn-import hidden', 'fa fa-upload', __('Import'), __('Import')],
        ];
        $this->view->assign("btnAttr", $btnAttr);
        $this->view->assign("res",$res['data'][0]);
        $table4 = $this->table4($res['data'][0]['bk_obj_id']);
        $this->view->assign("table4", $table4['data']);
        $this->view->assign("max_bk_group_index", $table4['max_bk_group_index']);
        return $this->view->fetch();
    }

    public function table1($obj)
    {
        if ($this->request->isAjax())
        {

            $res =$this->model->index();
            $res =\GuzzleHttp\json_decode($res,true);
            $list = $res['data'];
            $offset = $this->request->get("offset", 0);
            $limit = $this->request->get("limit", 0);

            $result = array("total" =>count($list), "rows" => array_slice($list,$offset,$limit));

            return json($result);
        }
        $this->view->assign("typeArr", $this->typeArr);
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

            // 模型名字
            $model = new \app\api\controller\v3\Model;
            $res = $model->index();
            $res = \GuzzleHttp\json_decode($res,true);
            $modelNameArr = [];
            foreach ($res['data'] as $val){
                foreach ($val['bk_objects'] as $v){
                    $modelNameArr[$v['bk_obj_id']] = $v['bk_obj_name'];
                }
            }
            foreach ($list as $key=>$value){
                $list[$key]['bk_obj_id'] = $modelNameArr[$value['bk_obj_id']];
                $list[$key]['bk_asst_obj_id'] = $modelNameArr[$value['bk_asst_obj_id']];
            }

            $offset = $this->request->get("offset", 0);
            $limit = $this->request->get("limit", 0);

            $result = array("total" =>count($list), "rows" => array_slice($list,$offset,$limit));

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
//        dump($maxarr['bk_group_index']);
//        echo $maxarr['bk_property_index'];
//        echo \GuzzleHttp\json_encode($groupdata['data']);
//        echo \GuzzleHttp\json_encode($res['data']);
//        die;
        //获得最大的bk_group_index值
        $maxarr = end($res['data']);
        $result['max_bk_group_index'] = $maxarr['bk_group_index'];
            foreach($res['data'] as $resK=>$resV){
                $data[$resK]['id'] = $resV['id'];
                $data[$resK]['bk_group_name'] = $resV['bk_group_name'];
                $data[$resK]['bk_group_id'] = $resV['bk_group_id'];
                foreach($groupdata['data'] as $gK =>$gV){
                    if($resV['bk_group_id'] == $gV['bk_property_group']){
                            $data[$resK]['data'][] = [
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
                            'id' =>$resV['id'],
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
            if(isset($resK)){
                $key = $resK+1;
                if(!empty($none)){
                    $data[$key]['id'] =$none[0]['id'];
                    $data[$key]['bk_group_name'] = '更多属性';
                    $data[$key]['bk_group_id'] = $none[0]['bk_group_id'];
                    $data[$key]['data']=$none;
                }else{
                    $data[$key]['id'] =$data[0]['id'];
                    $data[$key]['bk_group_name'] = '更多属性';
                    $data[$key]['bk_group_id'] = $data[0]['bk_group_id'];
//                    $data[$key]['data']=[];
                }

            }
            $result['data']=$data;
//        $data['max_bk_group_index'] = $maxarr['bk_group_index'];
//        echo \GuzzleHttp\json_encode($result);
//        die;
            return $result;
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
        $obj = $this->request->param('obj', 'host');
        $this->view->assign("typeArr",$this->typeArr);
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
                    $this->error(__('No rows were updated'));
                }

            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $option = array();
        if($row['bk_property_type'] === 'enum'){
            if(isset($row['option'])){
                foreach ($row['option'] as $key=>$value) {
                    $option[$value['id']] = $value['name'];
                }
                $row['option'] = '';
            }
        }
        if(in_array($row['bk_property_type'], array('int','float'))){
            if(isset($row['option'])){
                if(!is_array($row['option'])){
                    $row['option'] = \GuzzleHttp\json_decode($row['option'], true);
                }
                foreach ($row['option'] as $key=>$value) {
                    $row[$key] = $value;
                }

            }
            $row['option'] = '';
        }else{
            $row['max'] = '';
            $row['min'] = '';
        }
        $this->view->assign("typeArr",$this->typeArr);
        $this->view->assign("option",\GuzzleHttp\json_encode($option));
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

    /*
     * 导出字段
     */
    public function export($name = "cc_test_inst"){
        if ($this->request->isPost()) {
            set_time_limit(0);
            $params = array(
                "bk_obj_id" => $name,
                "bk_supplier_account" => "0",
            );
            $result = $this->model->exportExcelData($params);
            $data = $result['data'];
            if (empty($data)) {
                $this->error('无法下载');
            };

            $excel = new Spreadsheet();

            $excel->getProperties()
                ->setCreator("FitsAdmin")
                ->setLastModifiedBy("FitsAdmin")
                ->setTitle("标题")
                ->setSubject("Subject");
            $excel->getDefaultStyle()->getFont()->setName('Microsoft Yahei');
            $excel->getDefaultStyle()->getFont()->setSize(12);
            $excel->getActiveSheet()->getRowDimension('3')->setRowHeight(30);/*设置行高*/
            $myrow = 1;/*表头所需要行数的变量，方便以后修改*/
            $excel->setActiveSheetIndex(0)//设置一张sheet为活动表 添加表头信息
            ->setCellValue('A' . $myrow, '英文名(必填)')
                ->setCellValue('B' . $myrow, '中文名(必填)')
                ->setCellValue('C' . $myrow, '数据类型(必填)')
                ->setCellValue('D' . $myrow, '字段分组')
                ->setCellValue('E' . $myrow, '数据配置')
                ->setCellValue('F' . $myrow, '单位')
                ->setCellValue('G' . $myrow, '描述')
                ->setCellValue('H' . $myrow, '提示')
                ->setCellValue('I' . $myrow, '是否可编辑')
                ->setCellValue('J' . $myrow, '是否必填')
                ->setCellValue('K' . $myrow, '是否只读');

            $myrow = $myrow + 1; //刚刚设置的行变量

            $excel->setActiveSheetIndex(0)//设置一张sheet为活动表 添加表头信息
            ->setCellValue('A' . $myrow, '文本')
                ->setCellValue('B' . $myrow, '文本')
                ->setCellValue('C' . $myrow, '文本')
                ->setCellValue('D' . $myrow, '文本')
                ->setCellValue('E' . $myrow, '文本')
                ->setCellValue('F' . $myrow, '文本')
                ->setCellValue('G' . $myrow, '文本')
                ->setCellValue('H' . $myrow, '文本')
                ->setCellValue('I' . $myrow, '布尔')
                ->setCellValue('J' . $myrow, '布尔')
                ->setCellValue('K' . $myrow, '布尔');

            $myrow = $myrow + 1; //刚刚设置的行变量
            $keyArr = array('bk_property_id',
                'bk_property_name',
                'bk_property_type',
                'bk_property_group_name',
                'option',
                'unit',
                'description',
                'placeholder',
                'editable',
                'isrequired',
                'isreadonly',
                'isonly',
            );
            $num = 65;
            foreach ($keyArr as $v) {
                $excel->setActiveSheetIndex(0)
                    ->setCellValue(chr($num) . $myrow, $v);
                $num++;
            }

            $myrow = $myrow + 1; //刚刚设置的行变量
            $mynum = 1;//序号
            //遍历接收的数据，并写入到对应的单元格内
            foreach ($data as $key => $value) {
                $num = 65;
                foreach ($keyArr as $k1) {
                    if (is_array($value[$k1])) {
                        // JSON_UNESCAPED_UNICODE 解决中文乱码问题
                        $value[$k1] = \GuzzleHttp\json_encode($value[$k1], JSON_UNESCAPED_UNICODE);
                    }
                    $excel->setActiveSheetIndex(0)
                        ->setCellValue(chr($num) . $myrow, $value[$k1]);
                    $excel->getActiveSheet()->getRowDimension('' . $myrow)->setRowHeight(20);/*设置行高 不能批量的设置 这种感觉 if（has（蛋）！=0）{疼();}*/
                    $num++;
                }

                $myrow++;
                $mynum++;
            }

            $worksheet = $excel->setActiveSheetIndex(0);
            $worksheet->setTitle($name);
            $excel->createSheet();
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="inst_' . $name . '.xlsx"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');

            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0

            $objWriter = IOFactory::createWriter($excel, 'Xlsx');
            $objWriter->save('php://output');
            return;
        }

    }

    /*
     * 导入字段
     */

    public function import()
    {
        $file = $this->request->request('file');
        if (!$file) {
            $this->error(__('Parameter %s can not be empty', 'file'));
        }
//        $file = '/uploads/20190805/8c1fec454730d9ee631c97ef7fa76256.xlsx';
        $filePath = ROOT_PATH . DS . 'public' . DS . $file;
        if (!is_file($filePath)) {
            $this->error(__('No results were found'));
        }
        //实例化reader
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
            $this->error(__('Unknown data format'));
        }

        $sheet = 0;
        $columnCnt = 0;
        $options = [];
        try {
            /* 转码 */
          //  $file = iconv("utf-8", "gb2312", $file);

            /** @var Xlsx $objRead */
            $objRead = IOFactory::createReader('Xlsx');

            /* 如果不需要获取特殊操作，则只读内容，可以大幅度提升读取Excel效率 */
            empty($options) && $objRead->setReadDataOnly(true);
            /* 建立excel对象 */
            $obj = $objRead->load($filePath);
            /* 获取指定的sheet表 */
            $currSheet = $obj->getSheet($sheet);
            $objArr = $obj->getSheetNames();
            $objId = $objArr[0];
            if (isset($options['mergeCells'])) {
                /* 读取合并行列 */
                $options['mergeCells'] = $currSheet->getMergeCells();
            }

            if (0 == $columnCnt) {
                /* 取得最大的列号 */
                $columnH = $currSheet->getHighestColumn();
                /* 兼容原逻辑，循环时使用的是小于等于 */
                $columnCnt = Coordinate::columnIndexFromString($columnH);
            }

            /* 获取总行数 */
            $rowCnt = $currSheet->getHighestRow();
            $data   = [];

            /* 读取内容 */
            for ($_row = 1; $_row <= $rowCnt; $_row++) {
                $isNull = true;

                for ($_column = 1; $_column <= $columnCnt; $_column++) {
                    $cellName = Coordinate::stringFromColumnIndex($_column);
                    $cellId   = $cellName . $_row;
                    $cell     = $currSheet->getCell($cellId);

                    if (isset($options['format'])) {
                        /* 获取格式 */
                        $format = $cell->getStyle()->getNumberFormat()->getFormatCode();
                        /* 记录格式 */
                        $options['format'][$_row][$cellName] = $format;
                    }

                    if (isset($options['formula'])) {
                        /* 获取公式，公式均为=号开头数据 */
                        $formula = $currSheet->getCell($cellId)->getValue();

                        if (0 === strpos($formula, '=')) {
                            $options['formula'][$cellName . $_row] = $formula;
                        }
                    }

                    if (isset($format) && 'm/d/yyyy' == $format) {
                        /* 日期格式翻转处理 */
                        $cell->getStyle()->getNumberFormat()->setFormatCode('yyyy/mm/dd');
                    }

                    $data[$_row][$cellName] = trim($currSheet->getCell($cellId)->getFormattedValue());

                    if (!empty($data[$_row][$cellName])) {
                        $isNull = false;
                    }
                }

                /* 判断是否整行数据为空，是的话删除该行数据 */
                if ($isNull) {
                    unset($data[$_row]);
                }
            }
            // 处理数据
            $keyArr = $data[3];
            $newData = [];
            $i = 0;
            foreach ($data as $k1=> $value){
                if($k1>3){
                    $num = 65;
                    foreach ($keyArr as $k){
                        if(in_array($k, array('editable',
                            'isrequired',
                            'isreadonly',
                            'isonly'))){
                            $bool = $value[chr($num)] ? true:false;
                            $newData[$i][$k] = $bool;
                        }else{
                            $newData[$i][$k] = $value[chr($num)];
                        }

                        $num++;
                    }
                    $newData[$i]['bk_obj_id'] = $objId;
                    $i++;
                }
            }
            // 获取字段
            $params = array(
                "bk_obj_id"=> $objId
            );
            $url = config('fastadmin.cmdb_api_url')."/object/attr/search";
            $res =  $this->model->sendRequest($url, \GuzzleHttp\json_encode($params));

            // 更新或者 新增
            $result = \GuzzleHttp\json_decode($res,true);
            $oldData =  $result['data'];
            foreach ($oldData as $oldAttrValue){
                foreach ($newData as $newK=>$newAttrValue){
                    if($oldAttrValue['bk_property_id'] == $newAttrValue['bk_property_id']){
                        // 更新
                        $newAttrValue['id'] = $oldAttrValue['id'];
                        $newData[$newK] = $newAttrValue;
                    }
                }
            }

            foreach ($newData as $_value){
                if(isset($_value['id'])){
                    // 更新
                    $url = config('fastadmin.cmdb_api_url')."/object/attr/".$_value['id'];
                    $ret = $this->model->sendRequest($url, \GuzzleHttp\json_encode($_value),'PUT');
                }else{
                    // 新增
                    $url = config('fastadmin.cmdb_api_url')."/object/attr";
                    $ret = $this->model->sendRequest($url, \GuzzleHttp\json_encode($_value));
                }
                $res = \GuzzleHttp\json_decode($ret,true);
                if(!$res['result']){
                    $this->error($res['bk_error_msg']);
                }
            }

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('导入成功');
    }

    public function groupadd()
    {
        $res =$this->model->index();
        $res =\GuzzleHttp\json_decode($res,true);
        $datas = $res['data'];

        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $paramsArr = [];
            $i =0;
            if(!empty($params['ids']) && !empty($params['datas'])){
                foreach($params['ids'] as $key=>$pid){
                    foreach($params['datas'] as $kid => $v){
                        if($pid == $kid){
                            $paramsArr['bk_property_ids'][] = $v['bk_property_id'];
                            $paramsArr['data'][$i]['bk_obj_id'] = $v['bk_obj_id'];
                            $paramsArr['data'][$i]['bk_property_id'] = $v['bk_property_id'];
                            $paramsArr['data'][$i]['bk_supplier_account'] = "0";
                            $paramsArr['data'][$i]['bk_property_name'] = $v['bk_property_name'];
                            $paramsArr['data'][$i]['bk_property_group'] = $v['bk_property_group'];
                            $paramsArr['data'][$i]['bk_property_index'] = intval($v['bk_property_index']);
                            $i=$i+1;
                        }
                    }
                }
            }
            if(!empty($paramsArr)){
                $this->success('','',$paramsArr);
            }else{
                $this->error(__('Parameter %s can not be empty', ''));
            }
        }
        $this->view->assign("datas",$datas);
        return $this->view->fetch();
    }


}
