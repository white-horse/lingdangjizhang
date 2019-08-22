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
     * 添加一笔账单
     * @param array $bill_data
     * @return boolean $result
     */
    public function addBill(array $bill_data)
    {	// 开启事务
        // 1. 添加账单
        // 2. 维护账单日数据
        // 3. 维护账单月数据
        // 4. 维护账单年数据
        // 5. 维护账单总数据
        // 结束事务
        
//         Db::startTrans();
        try{
            $add_bill_res = $this->save($bill_data);
            
            $bill_day_entity = new BillDayData();
            $bill_day_entity->setDayData($bill_data);

            $bill_month_entity = new BillMonthData();
            return $bill_month_entity->setMonthData($bill_data);
            
            $bill_year_entity = new BillYearData();
            
            $bill_total_entity = new BillTotalData();
            
//             Db::commit();
            
            return true;
        } catch (\Exception $e) {
//             Db::rollback();
            
            return false;
        }
    }
}