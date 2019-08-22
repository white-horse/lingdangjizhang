<?php
/**
 * 账单月数据管理模型
 * @author Jerry
 * @date 20190822
 */
namespace app\wxapp\model;

use think\Model;

class BillMonthData extends Model
{
    /**@var string 主键  */
    protected $pk = 'id';

	/**@var strng 时间类型字段，自动完成*/
    protected $autoWriteTimestamp = 'datetime';
    protected $createIime = 'create_time';
    protected $updateTime = 'update_time';
    
    /**
     * 维护账单月数据
     * @param array $bill_data
     * @return mixed $result
     */
    public function setMonthData(array $bill_data = [])
    {
        // 初始化入库数据
        $init_data = [];
        
        $bill_day = $bill_data['bill_date'];
        $bill_month = substr($bill_day, 0, 6);

        // 检查已有数据
        $where = ['user_id' => $bill_data['user_id'], 'bill_month' => $bill_month];
        $res = $this->where($where)->find();
        
        // 初始化现有支出 和 收入
        $expenditure_bill_total_fee = isset($res->data['expenditure_bill_total_fee'])?$res->data['expenditure_bill_total_fee']:0;
        $income_bill_total_fee = isset($res->data['income_bill_total_fee'])?$res->data['income_bill_total_fee']:0;
        
        // 支出
        if ($bill_data['bill_type'] == 1) {
            $init_data['expenditure_bill_total_fee'] = $expenditure_bill_total_fee + $bill_data['bill_amount'];
            
            $expenditure_bill_total_number = isset($res->data['expenditure_bill_total_number'])?$res->data['expenditure_bill_total_number']:0;
            $init_data['expenditure_bill_total_number'] = $expenditure_bill_total_number + 1;
            
            // 余额计算: 最新收入 - 最新支出
            $month_balance_fee = $income_bill_total_fee - $init_data['expenditure_bill_total_fee'];
        } else if ($bill_data['bill_type'] == 2) {
            // 收入
            $init_data['income_bill_total_fee'] = $income_bill_total_fee + $bill_data['bill_amount'];
            
            $income_bill_total_number = isset($res->data['income_bill_total_number'])?$res->data['income_bill_total_number']:0;
            $init_data['income_bill_total_number'] = $income_bill_total_number + 1;
            
            // 余额计算: 最新收入 - 最新支出
            $month_balance_fee = $init_data['income_bill_total_fee'] - $expenditure_bill_total_fee;
        }

        $init_data['month_balance_fee'] = $month_balance_fee > 0 ? $month_balance_fee : 0;
        
        if (!empty($res->data)) {
            // 更新
            return $this->save($init_data, ['id' => $res->data['id']]);
        } else {
            // 添加
            $init_data['user_id'] = $bill_data['user_id'];
            $init_data['bill_month'] = $bill_month;
            
            return $this->save($init_data);
        }
    }
    
    /**
     * 计算余额
     */
}