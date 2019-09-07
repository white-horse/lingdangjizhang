<?php
/**
 * 用户信息处理
 * @author jerry
 * @date 20190813
 */
namespace app\wxapp\controller;

//use think\Config;

class User extends Base
{
    /** 性别定义  */
    protected static $gender = [0,1,2];
    
    
    /**
     * 维护用户信息
     * @param array userinfo
     * @return boolean true | false
     */
    public function saveInfo()
    {
        // 参数校验
        $mobile_phone = $this->request->param('acctPhone');
        if (!empty($mobile_phone) && !check_mobile_no($mobile_phone)) {
            return $this->outputData(301, 'mobile_phone param error');
        } else {
			$mobile_phone = $mobile_phone ?: 0;
		}
        
        $gender = $this->request->param('gender');
        if ($gender !== null && !in_array($gender, self::$gender) ) {
            return $this->outputData(301, 'param error');
        }
        
       $infoData = [
            'openid' => $this->openid,
            'mobile_phone' => $mobile_phone,
            'gender' => $gender,
            'nickname' => $this->request->param('nickName'),
            'avatar_url' => $this->request->param('avatarUrl'),
            'country' => $this->request->param('country'),
            'province' => $this->request->param('province'),
            'city' => $this->request->param('city'),
            'language' => $this->request->param('language'),
        ];
        
       $result['result'] = false;
       if ($this->userAccountEntity->setUser($infoData) !== false) {
           $result['result'] = true;
       }
        return $this->outputData(200, 'success', $result);
    }
    
    /**
     * 初始化
     */
    protected function init()
    {
        
    }
    
}