<?php
/**
 * 小程序接口基类
 * @author jerry
 * @date 20190813
 */
namespace app\wxapp\controller;

use think\Request;

class Base
{
    protected const WX_MINI_APP = 'wxapp';
    protected static $userEnv;
    protected $userInfo = [];
    protected $request = null;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->init();
        
        // 验证用户环境
        if( self::$userEnv !== self::WX_MINI_APP ){
            
        }
        
        // 通用参数校验
        if (strlen($this->request->param('openid')) != 28) {
            exit(json_encode($this->outputData(301, 'openid error')));
        }
        
        $this->userInfo['openid'] = $this->request->param('openid');
        
    }
    
    /**
     * 初始化相关数据
     */
    private function init()
    {
        self::setUserEnv();
    }
    
    /**
     * 设置当前用户环境 
     */
    private static function setUserEnv()
    {
        if (true) {
            self::$userEnv = 'wxapp';
        }
    }
    
    /**
     * 接口数据输出
     * 
     */
    public function outputData(int $code = 200, string $msg = 'success', array $data = [])
    {
        return [
            'errcode' => $code,
            'errmsg' => $msg,
            'data' => $data
        ];
    }

}