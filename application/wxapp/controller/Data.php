<?php
/**
 * 数据相关设置获取
 * @author Jerry
 * @date 20190819
 */

namespace app\wxapp\controller;

class Data extends Base
{
    
    public function __construct()
    {
        parent::__construct();
        $this->checkUser();
    }
    
    
    /**
     * 获取用户中心总概览数据
     * @param string $openid
     * @return json
     */
    public function getOverviewItem()
    {
        
        return $openid = $this->openid;
        
//         $user
        
    }
    
}