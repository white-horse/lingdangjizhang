<?php
/**
 * 账单月数据管理模型
 * @author Jerry
 * @date 20190822
 */
namespace app\wxapp\model;

use think\Model;

class BillYearData extends Model
{
    /**@var string 主键  */
    protected $pk = 'id';

	/**@var strng 时间类型字段，自动完成*/
    protected $autoWriteTimestamp = 'datetime';
    protected $createIime = 'create_time';
    protected $updateTime = 'update_time';
    
    /**
     * 添加一笔账单
     * @param array $bill_data
     * @return boolean $result
     */
    public function addBill(array $bill_data)
    {	
        return $this->save($bill_data);
    }
}