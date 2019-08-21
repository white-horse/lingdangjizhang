<?php
/**
 * 账单标签模型
 * @author Jerry
 * @date 20190820
 */
namespace app\wxapp\model;

use think\Model;

class BillTag extends Model
{
    /**@var string 主键  */
    protected $pk = 'id';
    
    /**
     * 获取数据
     * @param array $where
     * @param array $fields
     * @return array $result
     */
    public function getTagList(array $where = [], array $fields = [])
    {
        return $this->where($where)->field($fields)->order("sort_index DESC")->select();
    }
}