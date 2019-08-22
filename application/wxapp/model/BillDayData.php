<?php
/**
 * 账单日数据管理模型
 * @author Jerry
 * @date 20190822
 */
namespace app\wxapp\model;

use think\Model;

class BillDayData extends Model
{
    /**@var string 主键  */
    protected $pk = 'id';

	/**@var strng 时间类型字段，自动完成*/
    protected $autoWriteTimestamp = 'datetime';
    protected $createIime = 'create_time';
    protected $updateTime = 'update_time';
    
    /**
     * 维护账单日数据
     * @param array $bill_data
     * @return mixed $result
     */
    public function setDayData(array $bill_data = [])
    {	
        // 初始化入库数据
        $init_data = [];
        
        // 检查已有数据
        $where = ['user_id' => $bill_data['user_id'], 'bill_day' => $bill_data['bill_date']];
        $res = $this->where($where)->find();

        // 支出
        if ($bill_data['bill_type'] == 1) {
            $expenditure_bill_total_fee = isset($res->data['expenditure_bill_total_fee'])?$res->data['expenditure_bill_total_fee']:0;
            $init_data['expenditure_bill_total_fee'] = $expenditure_bill_total_fee + $bill_data['bill_amount'];
            
            $expenditure_bill_total_number = isset($res->data['expenditure_bill_total_number'])?$res->data['expenditure_bill_total_number']:0;
            $init_data['expenditure_bill_total_number'] = $expenditure_bill_total_number + 1;
        } else if ($bill_data['bill_type'] == 2) {
            // 收入
            $income_bill_total_fee = isset($res->data['income_bill_total_fee'])?$res->data['income_bill_total_fee']:0;
            $init_data['income_bill_total_fee'] = $income_bill_total_fee + $bill_data['bill_amount'];
            
            $income_bill_total_number = isset($res->data['income_bill_total_number'])?$res->data['income_bill_total_number']:0;
            $init_data['income_bill_total_number'] = $income_bill_total_number + 1;
        } 

        if (!empty($res->data)) {
            // 更新
            return $this->save($init_data, ['id' => $res->data['id']]);
        } else {
            // 添加
            $init_data['user_id'] = $bill_data['user_id'];
            $init_data['bill_day'] = $bill_data['bill_date'];
            return $this->save($init_data);
        }
    }
}