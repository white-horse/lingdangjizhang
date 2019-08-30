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
     * @param string $set_type 账单操作类型：add 添加账单； remove 删除账单
     * @return mixed $result
     */
    public function setMonthData(array $bill_data = [], string $set_type = 'add')
    {
        // 初始化入库数据
        $init_data = [];

        $bill_data['bill_month']= substr($bill_data['bill_date'], 0, 6);

        // 检查已有数据
        $where = ['user_id' => $bill_data['user_id'], 'bill_month' => $bill_data['bill_month']];
        $res = $this->where($where)->find();
        $res_data = isset($res->data) ? $res->data : [];
        
        // 计算账单月数据
        if ($set_type == 'add') {
            $init_data = $this->addBillToMonthData($bill_data, $res_data);
        } else if ($set_type == 'remove') {
            $init_data = $this->removeBillToMonthData($bill_data, $res_data);
        } else {
            return false;
        }
        
        if (!empty($res_data)) {
            // 更新
            return $this->save($init_data, ['id' => $res_data['id']]);
        } else {
            // 添加
            $init_data['user_id'] = $bill_data['user_id'];
            $init_data['bill_month'] = $bill_data['bill_month'];
            
            return $this->save($init_data);
        }
    }

    /**
     * 账单添加：账单月数据变更
     * @param array $bill_data
     * @param array $exist_monthdata
     * @return array $result 包含计算好的账单月数据元素
     */
    private function addBillToMonthData(array $bill_data, array $exist_monthdata = [])
    {
        $result = [];
        
        // 该月支出天数
        $bill_day_entity = new BillDayData();
        $result['month_expenditure_day'] = $bill_day_entity->countBillDay($bill_data['user_id'], $bill_data['bill_month']);
        
        // 初始化现有支出 和 收入
        $expenditure_bill_total_fee = isset($exist_monthdata['expenditure_bill_total_fee'])?$exist_monthdata['expenditure_bill_total_fee']:0;
        $income_bill_total_fee = isset($exist_monthdata['income_bill_total_fee'])?$exist_monthdata['income_bill_total_fee']:0;
        
        // 支出
        if ($bill_data['bill_type'] == 1) {
            $result['expenditure_bill_total_fee'] = $expenditure_bill_total_fee + $bill_data['bill_amount'];
            
            $expenditure_bill_total_number = isset($exist_monthdata['expenditure_bill_total_number'])?$exist_monthdata['expenditure_bill_total_number']:0;
            $result['expenditure_bill_total_number'] = $expenditure_bill_total_number + 1;
            
            // 余额计算: 最新收入 - 最新支出
            $month_balance_fee = $income_bill_total_fee - $result['expenditure_bill_total_fee'];
            
            // 日均支出金额
            $result['day_average_expenditure_fee'] = $result['expenditure_bill_total_fee'] / $result['month_expenditure_day'];
        } else if ($bill_data['bill_type'] == 2) {
            // 收入
            $result['income_bill_total_fee'] = $income_bill_total_fee + $bill_data['bill_amount'];
            
            $income_bill_total_number = isset($exist_monthdata['income_bill_total_number'])?$exist_monthdata['income_bill_total_number']:0;
            $result['income_bill_total_number'] = $income_bill_total_number + 1;
            
            // 余额计算: 最新收入 - 最新支出
            $month_balance_fee = $result['income_bill_total_fee'] - $expenditure_bill_total_fee;
            
            // 日均支出金额
            $result['day_average_expenditure_fee'] = $expenditure_bill_total_fee / $result['month_expenditure_day'];
        }
        
        $result['month_balance_fee'] = $month_balance_fee > 0 ? $month_balance_fee : 0;
        
        return $result;
    }
    
    /**
     * 账单删除：账单月数据变更
     * @param array $bill_data
     * @param array $exist_monthdata
     * @return array $result 包含计算好的账单月数据元素
     */
    private function removeBillToMonthData(array $bill_data, array $exist_monthdata = [])
    {
        $result = [];
        
        // 该月支出天数
        $bill_day_entity = new BillDayData();
        $result['month_expenditure_day'] = $bill_day_entity->countBillDay($bill_data['user_id'], $bill_data['bill_month']);
        
        // 初始化现有支出 和 收入
        $expenditure_bill_total_fee = isset($exist_monthdata['expenditure_bill_total_fee'])?$exist_monthdata['expenditure_bill_total_fee']:0;
        $income_bill_total_fee = isset($exist_monthdata['income_bill_total_fee'])?$exist_monthdata['income_bill_total_fee']:0;
        
        // 支出
        if ($bill_data['bill_type'] == 1) {
            $result['expenditure_bill_total_fee'] = $expenditure_bill_total_fee - $bill_data['bill_amount'];
            
            $expenditure_bill_total_number = isset($exist_monthdata['expenditure_bill_total_number'])?$exist_monthdata['expenditure_bill_total_number']:0;
            $result['expenditure_bill_total_number'] = $expenditure_bill_total_number - 1;
            
            // 余额计算: 最新收入 - 最新支出
            $month_balance_fee = $income_bill_total_fee - $result['expenditure_bill_total_fee'];
            
            // 日均支出金额
            $result['day_average_expenditure_fee'] = $result['expenditure_bill_total_fee'] / $result['month_expenditure_day'];
        } else if ($bill_data['bill_type'] == 2) {
            // 收入
            $result['income_bill_total_fee'] = $income_bill_total_fee - $bill_data['bill_amount'];
            
            $income_bill_total_number = isset($exist_monthdata['income_bill_total_number'])?$exist_monthdata['income_bill_total_number']:0;
            $result['income_bill_total_number'] = $income_bill_total_number - 1;
            
            // 余额计算: 最新收入 - 最新支出
            $month_balance_fee = $result['income_bill_total_fee'] - $expenditure_bill_total_fee;
            
            // 日均支出金额
            $result['day_average_expenditure_fee'] = $expenditure_bill_total_fee / $result['month_expenditure_day'];
        }
        
        $result['month_balance_fee'] = $month_balance_fee > 0 ? $month_balance_fee : 0;
        
        return $result;
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
	
	/**
	 * 获取某月账单数据
	 * @param array $where
	 * @param array $fields
	 * @return array $result
	 */
	public function getMonthData(array $where = [], array $fields = [])
	{
	    $res = $this->where($where)->field($fields)->find();
	    if (!empty($res->data)) {
	        return $res->data;
	    }
	    
	    return [];
	}
	
	    
}