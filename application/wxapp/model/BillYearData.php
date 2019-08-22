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
     * 维护账单年数据
     * @param array $bill_data
     * @return mixed $result
     */
    public function setYearData(array $bill_data = [])
    {
        // 初始化入库数据
        $init_data = [];
 
        $bill_year = substr($bill_data['bill_date'], 0, 4);

		// 该年支出天数
		$bill_day_entity = new BillDayData();
		$init_data['year_expenditure_day'] = $bill_day_entity->countBillDay($bill_data['user_id'], $bill_year);

		// 该年支出月数
		$bill_month_entity = new BillMonthData();
		$init_data['year_expenditure_month'] = $bill_month_entity->countBillMonth($bill_data['user_id'], $bill_year);

        // 检查已有数据
        $where = ['user_id' => $bill_data['user_id'], 'bill_year' => $bill_year];
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
            $year_balance_fee = $income_bill_total_fee - $init_data['expenditure_bill_total_fee'];

			// 日均支出金额
			$init_data['day_average_expenditure_fee'] = $init_data['expenditure_bill_total_fee'] / $init_data['year_expenditure_day'];

			// 月均支出金额
			$init_data['month_average_expenditure_fee'] = $init_data['expenditure_bill_total_fee'] / $init_data['year_expenditure_month'];
        } else if ($bill_data['bill_type'] == 2) {
            // 收入
            $init_data['income_bill_total_fee'] = $income_bill_total_fee + $bill_data['bill_amount'];
            
            $income_bill_total_number = isset($res->data['income_bill_total_number'])?$res->data['income_bill_total_number']:0;
            $init_data['income_bill_total_number'] = $income_bill_total_number + 1;
            
            // 余额计算: 最新收入 - 最新支出
            $year_balance_fee = $init_data['income_bill_total_fee'] - $expenditure_bill_total_fee;

			// 日均支出金额
			$init_data['day_average_expenditure_fee'] = $expenditure_bill_total_fee / $init_data['year_expenditure_day'];

			// 月均支出金额
			$init_data['month_average_expenditure_fee'] = $expenditure_bill_total_fee / $init_data['year_expenditure_month'];
        }

        $init_data['year_balance_fee'] = $year_balance_fee > 0 ? $year_balance_fee : 0;

        if (!empty($res->data)) {
            // 更新
            return $this->save($init_data, ['id' => $res->data['id']]);
        } else {
            // 添加
            $init_data['user_id'] = $bill_data['user_id'];
            $init_data['bill_year'] = $bill_year;
            
            return $this->save($init_data);
        }
    }
}