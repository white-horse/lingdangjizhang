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
    // 主键
    protected $pk = 'id';
    
    /**
     * @var string 时间类型字段，自动设置
     */
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    /** 设置用户账号信息：更新 或 插入  */
    public function setUser()
    {
        $this->data = [
            'openid' => '33333333333',
        ];
        
        $info = $this->where(['openid' => $this->data['openid']])->find();
        if (!empty($info)) {
            return $this->save($this->data, ['id' => $info['id']]);
        }else{
            return $this->save($this->data);
        }
    }
    
    /** 自动完成  */
//     protected function //
    
}