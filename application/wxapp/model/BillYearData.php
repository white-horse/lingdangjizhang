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
     * @param string $set_type 账单操作类型：add 添加账单； remove 删除账单
     * @return mixed $result
     */
    public function setYearData(array $bill_data = [], string $set_type = 'add')
    {
        // 初始化入库数据
        $init_data = [];
 
        $bill_data['bill_year'] = substr($bill_data['bill_date'], 0, 4);

        // 检查已有数据
        $where = ['user_id' => $bill_data['user_id'], 'bill_year' => $bill_data['bill_year']];
        $res = $this->where($where)->find();
        $res_data = isset($res->data) ? $res->data : [];
        
        // 计算账单年数据
        if ($set_type == 'add') {
            $init_data = $this->addBillToYearData($bill_data, $res_data);
        } else if ($set_type == 'remove') {
            $init_data = $this->removeBillToYearData($bill_data, $res_data);
        } else {
            return false;
        }
        
        if (!empty($res_data)) {
            // 更新
            return $this->save($init_data, ['id' => $res_data['id']]);
        } else {
            // 添加
            $init_data['user_id'] = $bill_data['user_id'];
            $init_data['bill_year'] = $bill_data['bill_year'];
            
            return $this->save($init_data);
        }
    }
    
    /**
     * 账单添加：账单年数据变更
     * @param array $bill_data
     * @param array $exist_yeardata
     * @return array $result 包含计算好的账单年数据元素
     */
    private function addBillToYearData(array $bill_data, array $exist_yeardata = [])
    {
        $result = [];
        
        // 该年支出天数
        $bill_day_entity = new BillDayData();
        $result['year_expenditure_day'] = $bill_day_entity->countBillDay($bill_data['user_id'], $bill_data['bill_year']);
        
        // 该年支出月数
        $bill_month_entity = new BillMonthData();
        $result['year_expenditure_month'] = $bill_month_entity->countBillMonth($bill_data['user_id'], $bill_data['bill_year']);
        
        // 初始化现有支出 和 收入
        $expenditure_bill_total_fee = isset($exist_yeardata['expenditure_bill_total_fee'])?$exist_yeardata['expenditure_bill_total_fee']:0;
        $income_bill_total_fee = isset($exist_yeardata['income_bill_total_fee'])?$exist_yeardata['income_bill_total_fee']:0;
        
        // 支出
        if ($bill_data['bill_type'] == 1) {
            $result['expenditure_bill_total_fee'] = $expenditure_bill_total_fee + $bill_data['bill_amount'];
            
            $expenditure_bill_total_number = isset($exist_yeardata['expenditure_bill_total_number'])?$exist_yeardata['expenditure_bill_total_number']:0;
            $result['expenditure_bill_total_number'] = $expenditure_bill_total_number + 1;
            
            // 余额计算: 最新收入 - 最新支出
            $year_balance_fee = $income_bill_total_fee - $result['expenditure_bill_total_fee'];
            
            // 日均支出金额
            $result['day_average_expenditure_fee'] = $result['expenditure_bill_total_fee'] / $result['year_expenditure_day'];
            
            // 月均支出金额
            $result['month_average_expenditure_fee'] = $result['expenditure_bill_total_fee'] / $result['year_expenditure_month'];
        } else if ($bill_data['bill_type'] == 2) {
            // 收入
            $result['income_bill_total_fee'] = $income_bill_total_fee + $bill_data['bill_amount'];
            
            $income_bill_total_number = isset($exist_yeardata['income_bill_total_number'])?$exist_yeardata['income_bill_total_number']:0;
            $result['income_bill_total_number'] = $income_bill_total_number + 1;
            
            // 余额计算: 最新收入 - 最新支出
            $year_balance_fee = $result['income_bill_total_fee'] - $expenditure_bill_total_fee;
            
            // 日均支出金额
            $result['day_average_expenditure_fee'] = $expenditure_bill_total_fee / $result['year_expenditure_day'];
            
            // 月均支出金额
            $result['month_average_expenditure_fee'] = $expenditure_bill_total_fee / $result['year_expenditure_month'];
        }
        
        $result['year_balance_fee'] = $year_balance_fee > 0 ? $year_balance_fee : 0;
        
        return $result;
    }
    
    /**
     * 账单删除：账单年数据变更
     * @param array $bill_data
     * @param array $exist_yeardata
     * @return array $result 包含计算好的账单年数据元素
     */
    private function removeBillToYearData(array $bill_data, array $exist_yeardata = [])
    {
        $result = [];
        
        // 该年支出天数
        $bill_day_entity = new BillDayData();
        $result['year_expenditure_day'] = $bill_day_entity->countBillDay($bill_data['user_id'], $bill_data['bill_year']);
        
        // 该年支出月数
        $bill_month_entity = new BillMonthData();
        $result['year_expenditure_month'] = $bill_month_entity->countBillMonth($bill_data['user_id'], $bill_data['bill_year']);
        
        // 初始化现有支出 和 收入
        $expenditure_bill_total_fee = isset($exist_yeardata['expenditure_bill_total_fee'])?$exist_yeardata['expenditure_bill_total_fee']:0;
        $income_bill_total_fee = isset($exist_yeardata['income_bill_total_fee'])?$exist_yeardata['income_bill_total_fee']:0;
        
        // 支出
        if ($bill_data['bill_type'] == 1) {
            $result['expenditure_bill_total_fee'] = $expenditure_bill_total_fee - $bill_data['bill_amount'];
            
            $expenditure_bill_total_number = isset($exist_yeardata['expenditure_bill_total_number'])?$exist_yeardata['expenditure_bill_total_number']:0;
            $result['expenditure_bill_total_number'] = $expenditure_bill_total_number - 1;
            
            // 余额计算: 最新收入 - 最新支出
            $year_balance_fee = $income_bill_total_fee - $result['expenditure_bill_total_fee'];
            
            // 日均支出金额
            $result['day_average_expenditure_fee'] = $result['expenditure_bill_total_fee'] / $result['year_expenditure_day'];
            
            // 月均支出金额
            $result['month_average_expenditure_fee'] = $result['expenditure_bill_total_fee'] / $result['year_expenditure_month'];
        } else if ($bill_data['bill_type'] == 2) {
            // 收入
            $result['income_bill_total_fee'] = $income_bill_total_fee - $bill_data['bill_amount'];
            
            $income_bill_total_number = isset($exist_yeardata['income_bill_total_number'])?$exist_yeardata['income_bill_total_number']:0;
            $result['income_bill_total_number'] = $income_bill_total_number - 1;
            
            // 余额计算: 最新收入 - 最新支出
            $year_balance_fee = $result['income_bill_total_fee'] - $expenditure_bill_total_fee;
            
            // 日均支出金额
            $result['day_average_expenditure_fee'] = $expenditure_bill_total_fee / $result['year_expenditure_day'];
            
            // 月均支出金额
            $result['month_average_expenditure_fee'] = $expenditure_bill_total_fee / $result['year_expenditure_month'];
        }
        
        $result['year_balance_fee'] = $year_balance_fee > 0 ? $year_balance_fee : 0;
        
        return $result;
    }
}