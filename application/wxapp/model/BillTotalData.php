<?php
/**
 * 账单总数据模型
 * @author Jerry
 * @date 20190820
 */
namespace app\wxapp\model;

use think\Model;

class BillTotalData extends Model
{
    /**@var string 主键  */
    protected $pk = 'id';
    
    /**@var strng 时间类型字段，自动完成*/
    protected $autoWriteTimestamp = 'datetime';
    protected $createIime = 'create_time';
    protected $updateTime = 'update_time';
    
    /**
     * 获取数据
     * @param array $where
     * @param array $fields
     * @return array $result
     */
    public function getOne(array $where = [], array $fields = [])
    {
        $res = $this->where($where)->field($fields)->find();
        if (!empty($res->data)) {
            return $res->data;
        }
        
        return [];
    }
}