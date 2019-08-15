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
    /**@var 授权获取openid的微信地址，code换取  */
    private const OPENID_URL = 'https://api.weixin.qq.com/sns/jscode2session?';
    
    /**@var 小程序配置  */
    private static $appId;
    private static $appSecret;
    
    /**@var 接口返回数据  */
    private static $output = ['errcode' => 200, 'errmsg' => 'success', 'data' => []];
    
    public function __construct()
    {
        self::$appId = Config::get('wxapp.app_id');
        self::$appSecret = Config::get('wxapp.app_secret');
    }
    
    /**
     * 获取openid
     */
    public function getOpenid(Request $request)
    {
        
        $code = $request->param('code', '');
        if ($code) {
            $url = self::OPENID_URL.'appid='.self::$appId.'&secret='.self::$appSecret
                        .'&js_code='.$code.'&grant_type=authorization_code';
            $res = json_decode(curl_get($url), true);
            if (isset($res['openid'])) {
                self::$output['data']['openid'] = $res['openid'];
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