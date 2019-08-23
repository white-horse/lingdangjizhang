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

        $bill_month = substr($bill_data['bill_date'], 0, 6);

		// 该月支出天数
		$bill_day_entity = new BillDayData();
		$init_data['month_expenditure_day'] = $bill_day_entity->countBillDay($bill_data['user_id'], $bill_month);

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

			// 日均支出金额
			$init_data['day_average_expenditure_fee'] = $init_data['expenditure_bill_total_fee'] / $init_data['month_expenditure_day'];
        } else if ($bill_data['bill_type'] == 2) {
            // 收入
            $init_data['income_bill_total_fee'] = $income_bill_total_fee + $bill_data['bill_amount'];
            
            $income_bill_total_number = isset($res->data['income_bill_total_number'])?$res->data['income_bill_total_number']:0;
            $init_data['income_bill_total_number'] = $income_bill_total_number + 1;
            
            // 余额计算: 最新收入 - 最新支出
            $month_balance_fee = $init_data['income_bill_total_fee'] - $expenditure_bill_total_fee;

			// 日均支出金额
			$init_data['day_average_expenditure_fee'] = $expenditure_bill_total_fee / $init_data['month_expenditure_day'];
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
	 * 获取账单每年支出总月数
	 * @param int $user_id 用户ID
	 * @param int $year 查询的年份2019
	 * @return int $result 
	 */
	public function countBillMonth(int $user_id, int $year = 0)
	{
		$where = ['user_id' => $user_id];

		if ($year) {
			$where['bill_month'] = ['LIKE', "$year%"];
		}

		$res = $this->where($where)->field('COUNT(DISTINCT(bill_month)) AS total')->find();
		if ($res->data['total']) {
			return $res->data['total'];
		}

		return 1;
	}
}