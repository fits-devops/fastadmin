<?php

namespace app\api\controller\v3;


use fast\Http;
use think\Exception;
use think\Request;

/**
 * 字段
 */
class Attribute extends BaseApi
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

    private $path = '/object/attr';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * @ApiTitle    (查看属性列表)
     * @ApiSummary  (获取插件商店的插件列表信息)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/V3/Model/index)
     */
    public function index()
    {
        $params = $this->request->post("row/a");
        $url = config('fastadmin.cmdb_api_url')."/object/attr/search";
        return  self::sendRequest($url, $params);

    }

    /**
     * @ApiTitle    (删除字段)
     * @ApiSummary  (获取插件商店的插件列表信息)
     * @ApiMethod   (DELETE)
     * @ApiParams   (name="id", type="integer", required=true, description="模型ID")
     * @ApiRoute    (/api/v3/Model/{id})
     * 这里返回的是data数组
     */
    public function delete($id)
    {

        $url = config('fastadmin.cmdb_api_url').$this->path.'/'.$id;
        return  self::sendRequest($url, $params=[], 'DELETE');
    }

    /**
     * @ApiTitle    (更新字段)
     * @ApiSummary  (获取插件商店的插件列表信息)
     * @ApiMethod   (PUT)
     * @ApiRoute    (/api/v3/Model/{id})
     * 这里返回的是data数组
     */
    public function update($id)
    {

        $params = $this->request->post("row/a");
        $url = config('fastadmin.cmdb_api_url')."/object/attr/".$id;
        return  self::sendRequest($url, $params, 'PUT');
    }

    /**
     * @ApiTitle    (获取插件列表)
     * @ApiSummary  (获取插件商店的插件列表信息)
     * @ApiMethod   (GET)
     * @ApiParams   (name="bk_obj_id", type="string", required=true, description="对象模型的ID，只能用英文字母序列命名")
     * @ApiRoute    (/api/v3/Model/{id})
     * 这里返回的是data数组
     */
    public function read($id)
    {

        $params = array(
            "id"=> $id,
        );
        $url = config('fastadmin.cmdb_api_url')."/attr/search";
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
        $url = config('fastadmin.cmdb_api_url').$this->path;
        return  self::sendRequest($url, $params);
    }

    public function exportExcel($name = "cc_test_inst"){

        $params = array(
            "bk_obj_id"=> $name,
            "bk_supplier_account"=>"0",
        );
        // 获取字段数据
        $url = config('fastadmin.cmdb_api_url')."/object/attr/search";
        $res = self::sendRequest($url, \GuzzleHttp\json_encode($params));
        $result = \GuzzleHttp\json_decode($res,true);
        $data =  $result['data'];
        if(empty($data)){
            return $res;
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
        $excel->setActiveSheetIndex(0)  //设置一张sheet为活动表 添加表头信息
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

        $excel->setActiveSheetIndex(0)  //设置一张sheet为活动表 添加表头信息
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
        foreach ($keyArr as $v){
            $excel->setActiveSheetIndex(0)
                ->setCellValue(chr($num) . $myrow, $v);
            $num++;
        }

        $myrow = $myrow + 1; //刚刚设置的行变量
        $mynum = 1;//序号
        //遍历接收的数据，并写入到对应的单元格内
        foreach ($data as $key => $value) {
            $num = 65;
            foreach ($keyArr as $k1){
                if(is_array($value[$k1]))
                {
                    // JSON_UNESCAPED_UNICODE 解决中文乱码问题
                    $value[$k1] = \GuzzleHttp\json_encode($value[$k1],JSON_UNESCAPED_UNICODE);
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


    /**
     * 使用PHPEXECL导入
     *
     * @param string $file      文件地址
     * @return array
     * @throws Exception
     */
    public function importExcel(string $file = 'uploads/20190715/inst_cc_test_inst.xlsx')
    {
        $sheet = 0;
        $columnCnt = 0;
        $options = [];
        //  $file .= ROOT_PATH . DS.$file;
        dump($file);
        try {
            /* 转码 */
            $file = iconv("utf-8", "gb2312", $file);

            if (empty($file) OR !file_exists($file)) {
                throw new \Exception('文件不存在!');
            }

            /** @var Xlsx $objRead */
            $objRead = IOFactory::createReader('Xlsx');

            if (!$objRead->canRead($file)) {
                /** @var Xls $objRead */
                $objRead = IOFactory::createReader('Xls');

                if (!$objRead->canRead($file)) {
                    throw new \Exception('只支持导入Excel文件！');
                }
            }

            /* 如果不需要获取特殊操作，则只读内容，可以大幅度提升读取Excel效率 */
            empty($options) && $objRead->setReadDataOnly(true);
            /* 建立excel对象 */
            $obj = $objRead->load($file);
            /* 获取指定的sheet表 */
            $currSheet = $obj->getSheet($sheet);

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
                    $newData[$i]['bk_obj_id'] ='cc_test_inst';
                    $i++;
                }
            }
            // 获取字段
            $params = array(
                "bk_obj_id"=> 'cc_test_inst'
            );
            $url = config('fastadmin.cmdb_api_url')."/object/attr/search";
            $res =  self::sendRequest($url, \GuzzleHttp\json_encode($params));
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
                    $res =  self::sendRequest($url, \GuzzleHttp\json_encode($_value),'PUT');
                    dump($res);
                }else{
                    // 新增
                    $url = config('fastadmin.cmdb_api_url')."/object/attr";
                    $res =  self::sendRequest($url, \GuzzleHttp\json_encode($_value));
                    dump($res);
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }


}
