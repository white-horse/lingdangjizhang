<?php
/**
 * 小程序接口基类
 * @author jerry
 * @date 20190813
 */
namespace app\wxapp\controller;

use think\Request;
use app\wxapp\model\UserAccount;

class Base
{
    protected const WX_MINI_APP = ['apizza', 'wechat', 'postman'];
    protected static $userEnv = '';
    protected $userAccountEntity = null;
    protected $openid = '';
    protected $userInfo = [];
    protected $request = null;
    
    public function __construct()
    {
        $this->request = Request::instance();
        $this->init();

        // 验证用户环境
        if (!in_array(self::$userEnv, self::WX_MINI_APP)) {
            exit(json_encode($this->outputData(301, 'env error')));
        }
        
        // 通用参数校验
        if (strlen($this->request->param('openid')) != 28) {
            exit(json_encode($this->outputData(301, '请登录')));
        }
        
        $this->openid = $this->request->param('openid');
         
    }
    
    /** 
     * 获取用户基本信息
     * @return array $userInfo
     */
    protected function getUserBaseInfo()
    {   
        $where = ['openid' => $this->openid];
        $this->userInfo = $this->userAccountEntity->getUserInfo($where, ['id', 'openid']);
        
        return $this->userInfo;
    }
    
    /**
     * 初始化相关数据
     */
    private function init()
    {
        $this->setUserEnv();
        $this->userAccountEntity = new UserAccount();
    }
    
    /**
     * 设置当前用户环境 
     */
    private function setUserEnv()
    {
        if (substr_count(strtolower($this->request->header('user-agent')), 'micromessenger')) {
            self::$userEnv = 'wechat';
        } else if (substr_count(strtolower($this->request->header('user-agent')), 'apizza')) {
            self::$userEnv = 'apizza';
        } else if (substr_count(strtolower($this->request->header('user-agent')), 'postman')) {
            self::$userEnv = 'postman';
        }
    }
    
    /**
     * 检测用户，若不存在，直接输出
     */
    protected function checkUser()
    {
        $this->getUserBaseInfo();
        if (empty($this->userInfo)) {
            exit(json_encode($this->outputData(400, 'user error')));
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