<?php

namespace app\common\model;

use think\Model;

class Version extends Model
{

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'version';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 定义字段类型
    protected $type = [
    ];

    // 追加属性
    protected $append = [
        'type_text'
    ];

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }


    public function getTypeList()
    {
        return ['addon' => __('Addon'), 'agent' => __('Agent')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    /**
     * 检测版本号
     *
     * @param string $version 客户端版本号
     * @return array
     */
    public static function check($version)
    {
        $versionlist = self::where('status', 'normal')->cache('__version__')->order('weigh desc,id desc')->select();
        foreach ($versionlist as $k => $v) {
            // 版本正常且新版本号不等于验证的版本号且找到匹配的旧版本
            if ($v['status'] == 'normal' && $v['newversion'] !== $version && \fast\Version::check($version, $v['oldversion'])) {
                $updateversion = $v;
                break;
            }
        }
        if (isset($updateversion)) {
            $search = ['{version}', '{newversion}', '{downloadurl}', '{url}', '{packagesize}'];
            $replace = [$version, $updateversion['newversion'], $updateversion['downloadurl'], $updateversion['downloadurl'], $updateversion['packagesize']];
            $upgradetext = str_replace($search, $replace, $updateversion['content']);
            return [
                "enforce"     => $updateversion['enforce'],
                "version"     => $version,
                "newversion"  => $updateversion['newversion'],
                "downloadurl" => $updateversion['downloadurl'],
                "packagesize" => $updateversion['packagesize'],
                "upgradetext" => $upgradetext
            ];
        }
        return null;
    }


    /**
     * 按名称分组，获取当前版本、版本列表信息
     * @param null $name
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getVersionInfoByName($name = null)
    {
        $versionInfo = [];

        $versionlist = collection(self::where(function ($query) use ($name) {
            if(!is_null($name)){
                $query->where('name', '=', $name);
            }
        })->where('status','=','normal')->select());

        $versionGroup = array_group_by($versionlist,'name');

        foreach ($versionGroup as $k => $v){
            $version = '0';
            foreach ($v as $item){
                if ($item['newversion'] > $version){
                    $version = $item['newversion'];
                }
            }
            $versionInfo[$k] = [
                "name" => $k,
                "version" => $version,
                "releaselist" => $v
            ];
        }

        return $versionInfo;
    }
}
