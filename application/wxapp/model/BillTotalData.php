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

		$bill_data['format_bill_date']= date('Y-m-d', strtotime($bill_data['bill_date']));

        // 检查已有数据
        $where = ['user_id' => $bill_data['user_id']];
        $res = $this->where($where)->find();
        $res_data = isset($res->data) ? $res->data : [];
        
        // 计算账单总数据
        if ($set_type == 'add') {
            $init_data = $this->addBillToTotalData($bill_data, $res_data);
        } else if ($set_type == 'remove') {
            $init_data = $this->removeBillToTotalData($bill_data, $res_data);
        } else {
            return false;
        }

        if (!empty($res_data)) {
            // 更新
            return $this->save($init_data, ['id' => $res_data['id']]);
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
        
        // 该账号支出天数
        $bill_day_entity = new BillDayData();
        $result['total_expenditure_day'] = $bill_day_entity->countBillDay($bill_data['user_id']);
        
        // 该账号支出月数
        $bill_month_entity = new BillMonthData();
        $result['total_expenditure_month'] = $bill_month_entity->countBillMonth($bill_data['user_id']);
        
        // 初始化现有支出 和 收入
        $expenditure_bill_total_fee = isset($exist_totaldata['expenditure_bill_total_fee'])?$exist_totaldata['expenditure_bill_total_fee']:0;
        $income_bill_total_fee = isset($exist_totaldata['income_bill_total_fee'])?$exist_totaldata['income_bill_total_fee']:0;
        
        // 校正账单初始日期 和 最新日期
        $result['data_start_date'] = isset($exist_totaldata['data_start_date']) && $exist_totaldata['data_start_date'] < $bill_data['format_bill_date'] ? $exist_totaldata['data_start_date'] : $bill_data['format_bill_date'];
        
        $result['data_latest_date'] = isset($exist_totaldata['data_latest_date']) && $exist_totaldata['data_latest_date'] > $bill_data['format_bill_date'] ? $exist_totaldata['data_latest_date'] : $bill_data['format_bill_date'];
        
        // 支出
        if ($bill_data['bill_type'] == 1) {
            $result['expenditure_bill_total_fee'] = $expenditure_bill_total_fee + $bill_data['bill_amount'];
            
            $expenditure_bill_total_number = isset($exist_totaldata['expenditure_bill_total_number'])?$exist_totaldata['expenditure_bill_total_number']:0;
            $result['expenditure_bill_total_number'] = $expenditure_bill_total_number + 1;
            
            // 余额计算: 最新收入 - 最新支出
            $total_balance_fee = $income_bill_total_fee - $result['expenditure_bill_total_fee'];
            
            // 日均支出金额
            $result['day_average_expenditure_fee'] = $result['expenditure_bill_total_fee'] / $result['total_expenditure_day'];
            
            // 月均支出金额
            $result['month_average_expenditure_fee'] = $result['expenditure_bill_total_fee'] / $result['total_expenditure_month'];
        } else if ($bill_data['bill_type'] == 2) {
            // 收入
            $result['income_bill_total_fee'] = $income_bill_total_fee + $bill_data['bill_amount'];
            
            $income_bill_total_number = isset($exist_totaldata['income_bill_total_number'])?$exist_totaldata['income_bill_total_number']:0;
            $result['income_bill_total_number'] = $income_bill_total_number + 1;
            
            // 余额计算: 最新收入 - 最新支出
            $total_balance_fee = $result['income_bill_total_fee'] - $expenditure_bill_total_fee;
            
            // 日均支出金额
            $result['day_average_expenditure_fee'] = $expenditure_bill_total_fee / $result['total_expenditure_day'];
            
            // 月均支出金额
            $result['month_average_expenditure_fee'] = $expenditure_bill_total_fee / $result['total_expenditure_month'];
        }
        
        $result['total_balance_fee'] = $total_balance_fee > 0 ? $total_balance_fee : 0;
        
        return $result;
    }
    
    /**
     * 账单删除：账单总数据变更
     * @param array $bill_data
     * @param array $exist_totaldata
     * @return array $result 包含计算好的账单总数据元素
     */
    private function removeBillToTotalData(array $bill_data, array $exist_totaldata = [])
    {
        $result = [];
        
        // 该账号支出天数
        $bill_day_entity = new BillDayData();
        $result['total_expenditure_day'] = $bill_day_entity->countBillDay($bill_data['user_id']);
        
        // 该账号支出月数
        $bill_month_entity = new BillMonthData();
        $result['total_expenditure_month'] = $bill_month_entity->countBillMonth($bill_data['user_id']);
        
        // 初始化现有支出 和 收入
        $expenditure_bill_total_fee = isset($exist_totaldata['expenditure_bill_total_fee'])?$exist_totaldata['expenditure_bill_total_fee']:0;
        $income_bill_total_fee = isset($exist_totaldata['income_bill_total_fee'])?$exist_totaldata['income_bill_total_fee']:0;
        
        // 校正账单初始日期 和 最新日期
        $result['data_start_date'] = isset($exist_totaldata['data_start_date']) && $exist_totaldata['data_start_date'] < $bill_data['format_bill_date'] ? $exist_totaldata['data_start_date'] : $bill_data['format_bill_date'];
        
        $result['data_latest_date'] = isset($exist_totaldata['data_latest_date']) && $exist_totaldata['data_latest_date'] > $bill_data['format_bill_date'] ? $exist_totaldata['data_latest_date'] : $bill_data['format_bill_date'];
        
        // 支出
        if ($bill_data['bill_type'] == 1) {
            $result['expenditure_bill_total_fee'] = $expenditure_bill_total_fee - $bill_data['bill_amount'];
            
            $expenditure_bill_total_number = isset($exist_totaldata['expenditure_bill_total_number'])?$exist_totaldata['expenditure_bill_total_number']:0;
            $result['expenditure_bill_total_number'] = $expenditure_bill_total_number - 1;
            
            // 余额计算: 最新收入 - 最新支出
            $total_balance_fee = $income_bill_total_fee - $result['expenditure_bill_total_fee'];
            
            // 日均支出金额
            $result['day_average_expenditure_fee'] = $result['expenditure_bill_total_fee'] / $result['total_expenditure_day'];
            
            // 月均支出金额
            $result['month_average_expenditure_fee'] = $result['expenditure_bill_total_fee'] / $result['total_expenditure_month'];
        } else if ($bill_data['bill_type'] == 2) {
            // 收入
            $result['income_bill_total_fee'] = $income_bill_total_fee - $bill_data['bill_amount'];
            
            $income_bill_total_number = isset($exist_totaldata['income_bill_total_number'])?$exist_totaldata['income_bill_total_number']:0;
            $result['income_bill_total_number'] = $income_bill_total_number - 1;
            
            // 余额计算: 最新收入 - 最新支出
            $total_balance_fee = $result['income_bill_total_fee'] - $expenditure_bill_total_fee;
            
            // 日均支出金额
            $result['day_average_expenditure_fee'] = $expenditure_bill_total_fee / $result['total_expenditure_day'];
            
            // 月均支出金额
            $result['month_average_expenditure_fee'] = $expenditure_bill_total_fee / $result['total_expenditure_month'];
        }
        
        $result['total_balance_fee'] = $total_balance_fee > 0 ? $total_balance_fee : 0;
        
        return $result;
    }
}