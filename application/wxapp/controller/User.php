<?php
/**
 * 用户信息处理
 * @author jerry
 * @date 20190813
 */
namespace app\wxapp\controller;

use think\Config;
use app\wxapp\model\UserAccount;

class User extends Base
{
    /**@var object UserAccount */
    protected static $userAccount = null;
    
    /**
     * 维护用户信息
     * @param array userinfo
     * @return boolean true | false
     */
    public function saveInfo()
    {   
        // 参数校验
        
        $useraccount = new UserAccount();
        $data[] = $useraccount->setUser();
        return $this->outputData(200, 'success', $data);
    }
    
    /**
     * 初始化
     */
    protected function init()
    {
        self::$userAccount = new UserAccount();
    }
    
}