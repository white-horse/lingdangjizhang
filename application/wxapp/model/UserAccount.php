<?php
/**
 * 用户账号模型
 * @author jerry
 * @date 20190813 
 */

namespace app\wxapp\model;

use think\Model;

class UserAccount extends Model
{
    /**@var string 主键 */
    protected $pk = 'id';
    
    /**
     * @var string 时间类型字段，自动设置
     */
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    /** 设置用户账号信息：更新 或 插入  */
    public function setUser(array $data = [])
    {
        $res = $this->where(['openid' => $data['openid']])->find();
        if (!empty($res)) {
            return $this->save($data, ['id' => $res['id']]);
        }else{
            return $this->save($data);
        }
    }
    
    /**
     * 获取用户信息
     * @param string $openid
     * @return mixed $userInfo
     */
    public function getUserInfo(array $where = [], array $fields = [])
    {
        $res = $this->where($where)->field($fields)->find();
        if (!empty($res->data)) {
            return $res->data;
        }
        
        return false;
    }
    
    
}