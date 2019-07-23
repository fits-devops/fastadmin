<?php

namespace app\admin\model;

use think\Model;
use app\common\model\Version;
use think\Request;
use \fast\Http;

class Bluewhale extends Model
{

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'bluewhale';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text'

    ];

    public function getStatusList()
    {
        return ['1' => __('Status 1'),'0' => __('Status 0')];
    }













}
