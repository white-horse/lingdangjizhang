<?php
/**
 * 账单管理模型
 * @author Jerry
 * @date 20190820
 */
namespace app\wxapp\model;

use think\Model;
use think\Db;

class BillItem extends Model
{
    /**@var string 主键  */
    protected $pk = 'id';

	/**@var strng 时间类型字段，自动完成*/
    protected $autoWriteTimestamp = 'datetime';
    protected $createIime = 'create_time';
    protected $updateTime = 'update_time';
    
    /**
     * 设置一笔账单：添加 | 删除
     * @param array $bill_data 账单数据
     * @param string $set_type 账单操作类型：add 添加账单； remove 删除账单 
     * @return boolean $result
     */
    public function setBill(array $bill_data, string $set_type = 'add')
    {	
		// 开启事务
        // 1. 添加/移除账单
        // 2. 维护账单日数据
        // 3. 维护账单月数据
        // 4. 维护账单年数据
        // 5. 维护账单总数据
        // 结束事务
        
        Db::startTrans();
        try{
            if ($set_type == 'add') {
                $bill_res = $this->save($bill_data);
            } else if ($set_type == 'remove') {
                $bill_res = $this->where(['id' => $bill_data['id'], 'user_id' => $bill_data['user_id']])->delete();
            }
            
            $bill_day_entity = new BillDayData();
            $setday_res = $bill_day_entity->setDayData($bill_data, $set_type);

            $bill_month_entity = new BillMonthData();
            $setmonth_res = $bill_month_entity->setMonthData($bill_data, $set_type);
            
            $bill_year_entity = new BillYearData();
            $setyear_res = $bill_year_entity->setYearData($bill_data, $set_type);
            
            $bill_total_entity = new BillTotalData();
            $settotal_res = $bill_total_entity->setTotalData($bill_data, $set_type);

			if ($bill_res && $setday_res && $setmonth_res && $setyear_res && $settotal_res) {

			} else {			
			    Db::rollback();            
				return false;
			}

			Db::commit();
			return true;
        } catch (\Exception $e) {
            Db::rollback();            
            return false;
        }
    }
    
	/**
	 * 获取账单列表
	 * $param int $start_date
	 * @param int $end_date
	 * @return array $result
	 */
	public function getBills(int $user_id, int $start_date, int $end_date)
	{
		return $this->where(['user_id' => $user_id])
					->where('bill_date', 'between', [$start_date, $end_date])
					->order('bill_date DESC, create_time DESC')
					->select();	
	}
	
	/**
	 * 获取单个账单
	 * @param int $user_id
	 * @param int @bill_id
	 * @return array $result
	 */
	public function getBill(int $user_id, int $bill_id, array $fields = [])
	{
	    $where = [
	        'user_id' => $user_id,
	        'id' => $bill_id
	    ];
	    $res = $this->where($where)->field($fields)->find();
	    if (!empty($res->data)) {
	        return $res->data;
	    }
	    
	    return [];
	    
	}
}