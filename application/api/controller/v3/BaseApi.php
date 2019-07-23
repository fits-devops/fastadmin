<?php
/**
 * Created by PhpStorm.
 * User: chaofu
 * Date: 2019/7/4
 * Time: 14:59
 */

namespace app\api\controller\v3;
use app\common\controller\Api;
use fast\Http;
use think\Exception;

class BaseApi extends Api
{

    public static  function sendRequest($url, $params = array(), $method = 'POST'){
        $options =  [
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'X-REQUESTED-WITH: XMLHttpRequest',
                'bk_user: admin',
                'http_blueking_supplier_id: 0',
                'content-type: application/json',
            ]
        ];
        try{

            $ret = Http::sendRequest($url,$params,$method,$options);
            if($ret['ret']){
                return $ret['msg'];
            }else{
                return $ret;
            }

        }catch (Exception $e){
            return $e->getMessage();
        }
    }

}