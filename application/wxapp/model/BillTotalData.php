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

    /**
     * 维护账单总数据
     * @param array $bill_data
     * @param string $set_type 账单操作类型：add 添加账单； remove 删除账单
     * @return mixed $result
     */
    public function setTotalData(array $bill_data = [], string $set_type = 'add')
    {
        // 初始化入库数据
        $init_data = [];

		$format_bill_date = date('Y-m-d', strtotime($bill_data['bill_date']));

		// 该账号支出天数
		$bill_day_entity = new BillDayData();
		$init_data['total_expenditure_day'] = $bill_day_entity->countBillDay($bill_data['user_id']);

		// 该账号支出月数
		$bill_month_entity = new BillMonthData();
		$init_data['total_expenditure_month'] = $bill_month_entity->countBillMonth($bill_data['user_id']);

        // 检查已有数据
        $where = ['user_id' => $bill_data['user_id']];
        $res = $this->where($where)->find();
        
        // 初始化现有支出 和 收入
        $expenditure_bill_total_fee = isset($res->data['expenditure_bill_total_fee'])?$res->data['expenditure_bill_total_fee']:0;
        $income_bill_total_fee = isset($res->data['income_bill_total_fee'])?$res->data['income_bill_total_fee']:0;

		// 校正账单初始日期 和 最新日期
		$init_data['data_start_date'] = isset($res->data['data_start_date']) && $res->data['data_start_date'] < $format_bill_date ? $res->data['data_start_date'] : $format_bill_date;

		$init_data['data_latest_date'] = isset($res->data['data_latest_date']) && $res->data['data_latest_date'] > $format_bill_date ? $res->data['data_latest_date'] : $format_bill_date;

        // 支出
        if ($bill_data['bill_type'] == 1) {
            $init_data['expenditure_bill_total_fee'] = $expenditure_bill_total_fee + $bill_data['bill_amount'];
            
            $expenditure_bill_total_number = isset($res->data['expenditure_bill_total_number'])?$res->data['expenditure_bill_total_number']:0;
            $init_data['expenditure_bill_total_number'] = $expenditure_bill_total_number + 1;
            
            // 余额计算: 最新收入 - 最新支出
            $total_balance_fee = $income_bill_total_fee - $init_data['expenditure_bill_total_fee'];

			// 日均支出金额
			$init_data['day_average_expenditure_fee'] = $init_data['expenditure_bill_total_fee'] / $init_data['total_expenditure_day'];

			// 月均支出金额
			$init_data['month_average_expenditure_fee'] = $init_data['expenditure_bill_total_fee'] / $init_data['total_expenditure_month'];
        } else if ($bill_data['bill_type'] == 2) {
            // 收入
            $init_data['income_bill_total_fee'] = $income_bill_total_fee + $bill_data['bill_amount'];
            			
            $income_bill_total_number = isset($res->data['income_bill_total_number'])?$res->data['income_bill_total_number']:0;
            $init_data['income_bill_total_number'] = $income_bill_total_number + 1;
            
            // 余额计算: 最新收入 - 最新支出
            $total_balance_fee = $init_data['income_bill_total_fee'] - $expenditure_bill_total_fee;

			// 日均支出金额
			$init_data['day_average_expenditure_fee'] = $expenditure_bill_total_fee / $init_data['total_expenditure_day'];

			// 月均支出金额
			$init_data['month_average_expenditure_fee'] = $expenditure_bill_total_fee / $init_data['total_expenditure_month'];
        }

        $init_data['total_balance_fee'] = $total_balance_fee > 0 ? $total_balance_fee : 0;

        if (!empty($res->data)) {
            // 更新
            return $this->save($init_data, ['id' => $res->data['id']]);
        } else {
            // 添加
            $init_data['user_id'] = $bill_data['user_id'];

            return $this->save($init_data);
        }
    }
    
    /**
     * 账单添加：账单总数据变更
     * @param array $bill_data
     * @param array $exist_totaldata
     * @return array $result 包含计算好的账单总数据元素
     */
    private function addBillToTotalData(array $bill_data, array $exist_totaldata = [])
    {
        $result = [];
        
        
        return $result;
    }
    
    /**
     * 账单删除：账单总数据变更
     * @param array $bill_data
     * @param array $exist_daydata
     * @return array $result 包含计算好的账单总数据元素
     */
    private function removeBillToTotalData(array $bill_data, array $exist_totaldata = [])
    {
        $result = [];
        
        
        return $result;
    }
}