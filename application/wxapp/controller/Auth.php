<?php
/**
 * 小程序服务器端相关接口实现
 * @author jerry
 * @date 20190814
 */

namespace app\wxapp\controller;

use think\Request;
use think\Config;

class Auth
{   
    /**@var 接口返回数据  */
    private static $output = ['errcode' => 200, 'errmsg' => 'success', 'data' => []];
    
    /**
     * 获取openid
     */
    public function getOpenid(Request $request)
    {
        $code = $request->param('code', '');
        if ($code) {
            $url = Config::get('wxapp.get_openid_url').'?appid='.Config::get('wxapp.app_id').'&secret='
                   .Config::get('wxapp.app_secret').'&js_code='.$code.'&grant_type=authorization_code';
            
            $res = json_decode(curl_get($url), true);
            if (isset($res['openid'])) {
                self::$output['data']['openid'] = $res['openid'];
                self::$output['data']['session_key'] = $res['session_key'];
            } else {
                self::$output['errcode'] = $res['errcode'];
                self::$output['errmsg'] = $res['errmsg'];
            }
            
        } else {
            self::$output['errcode'] = 301;
            self::$output['errmsg'] = 'code error';
        }
        
        return self::$output;
    }
    
    
}