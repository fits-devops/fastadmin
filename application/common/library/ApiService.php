<?php

namespace app\common\library;

use app\common\library\token\Driver;
use think\addons\Service;
use fast\Http;
use think\Db;
use think\Exception;

/**
 * Token操作类
 */
class ApiService extends Service
{
    /**
     * 安装插件
     *
     * @param   string $name 插件名称
     * @param   boolean $force 是否覆盖
     * @param   array $extend 扩展参数
     * @return  boolean
     * @throws  Exception
     * @throws  AddonException
     */
    public static function install($name, $force = false, $extend = [])
    {
        if (!$name || (is_dir(ADDON_PATH . $name) && !$force)) {
            throw new Exception('Addon already exists');
        }

        // 远程下载插件
        $tmpFile = ApiService::download($name, $extend);

        // 解压插件
        $addonDir = ApiService::unzip($name);

        // 移除临时文件
        @unlink($tmpFile);

        try {
            // 检查插件是否完整
            ApiService::check($name);

            if (!$force) {
                ApiService::noconflict($name);
            }
        } catch (AddonException $e) {
            @rmdirs($addonDir);
            throw new AddonException($e->getMessage(), $e->getCode(), $e->getData());
        } catch (Exception $e) {
            @rmdirs($addonDir);
            throw new Exception($e->getMessage());
        }

        // 复制文件
        $sourceAssetsDir = self::getSourceAssetsDir($name);
        $destAssetsDir = self::getDestAssetsDir($name);
        if (is_dir($sourceAssetsDir)) {
            copydirs($sourceAssetsDir, $destAssetsDir);
        }
        foreach (self::getCheckDirs() as $k => $dir) {
            if (is_dir($addonDir . $dir)) {
                copydirs($addonDir . $dir, ROOT_PATH . $dir);
            }
        }

        try {
            // 默认启用该插件
            $info = get_addon_info($name);
            if (!$info['state']) {
                $info['state'] = 1;
                set_addon_info($name, $info);
            }

            // 执行安装脚本
            $class = get_addon_class($name);
            if (class_exists($class)) {
                $addon = new $class();
                $addon->install();
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // 导入
        ApiService::importsql($name);

        // 刷新
        ApiService::refresh();
        return true;
    }


    /**
     * 远程下载插件
     *
     * @param   string $name 插件名称
     * @param   array $extend 扩展参数
     * @return  string
     * @throws  AddonException
     * @throws  Exception
     */
    public static function download($name, $extend = [])
    {
        $addonTmpDir = RUNTIME_PATH . 'addons' . DS;
        if (!is_dir($addonTmpDir)) {
            @mkdir($addonTmpDir, 0755, true);
        }

        $tmpFile = $addonTmpDir . $name . ".zip";

        $options = [
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'X-REQUESTED-WITH: XMLHttpRequest'
            ]
        ];

        $ret = Http::sendRequest($extend['downurl'],[],'GET',$options);
        if ($ret['ret']) {
            if (substr($ret['msg'], 0, 1) == '{') {
                $json = (array)json_decode($ret['msg'], true);
                //如果传回的是一个下载链接,则再次下载
                if ($json['data'] && isset($json['data']['url'])) {
                    array_pop($options);
                    $ret = Http::sendRequest($json['data']['url'], [], 'GET', $options);
                    if (!$ret['ret']) {
                        //下载返回错误，抛出异常
                        throw new AddonException($json['msg'], $json['code'], $json['data']);
                    }
                } else {
                    //下载返回错误，抛出异常
                    throw new AddonException($json['msg'], $json['code'], $json['data']);
                }
            }
            if ($write = fopen($tmpFile, 'w')) {
                fwrite($write, $ret['msg']);
                fclose($write);
                return $tmpFile;
            }
            throw new Exception("没有权限写入临时文件");
        }
        throw new Exception("无法下载远程文件");
    }


    /**
     * 升级插件
     *
     * @param   string $name 插件名称
     * @param   array $extend 扩展参数
     */
    public static function upgrade($name, $extend = [])
    {
        $info = get_addon_info($name);
        if ($info['state']) {
            throw new Exception(__('Please disable addon first'));
        }
        $config = get_addon_config($name);
        if ($config) {
            //备份配置
        }

        // 备份插件文件
        ApiService::backup($name);

        // 远程下载插件
        $tmpFile = ApiService::download($name, $extend);

        // 解压插件
        $addonDir = ApiService::unzip($name);

        // 移除临时文件
        @unlink($tmpFile);

        if ($config) {
            // 还原配置
            set_addon_config($name, $config);
        }

        // 导入
        ApiService::importsql($name);

        // 执行升级脚本
        try {
            $class = get_addon_class($name);
            if (class_exists($class)) {
                $addon = new $class();

                if (method_exists($class, "upgrade")) {
                    $addon->upgrade();
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // 刷新
        ApiService::refresh();

        return true;
    }

}
