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
     * @param string $set_type 账单操作类型：add 添加账单； remove 删除账单
     * @return mixed $result
     */
    public function setDayData(array $bill_data = [], string $set_type = 'add')
    {	
        // 初始化入库数据
        $init_data = [];
        
        // 检查已有数据
        $where = ['user_id' => $bill_data['user_id'], 'bill_day' => $bill_data['bill_date']];
        $res = $this->where($where)->find();
        $res_data = isset($res->data) ? $res->data : [];
        
        // 计算账单日数据
        if ($set_type == 'add') {
            $init_data = $this->addBillToDayData($bill_data, $res_data);
        } else if ($set_type == 'remove') {
            $init_data = $this->removeBillToDayData($bill_data, $res_data);
        } else {
            return false;
        }

        if (!empty($res_data)) {
            // 更新
            return $this->save($init_data, ['id' => $res_data['id']]);
        } else {
            // 添加
            $init_data['user_id'] = $bill_data['user_id'];
            $init_data['bill_day'] = $bill_data['bill_date'];
            
            return $this->save($init_data);
        }
    }
    
    /**
     * 账单添加：账单日数据变更
     * @param array $bill_data
     * @param array $exist_daydata
     * @return array $result 包含计算好的账单日数据元素
     */
    private function addBillToDayData(array $bill_data, array $exist_daydata = [])
    {
        $result = [];
        
        // 支出
        if ($bill_data['bill_type'] == 1) {
            $expenditure_bill_total_fee = isset($exist_daydata['expenditure_bill_total_fee'])?$exist_daydata['expenditure_bill_total_fee']:0;
            $result['expenditure_bill_total_fee'] = $expenditure_bill_total_fee + $bill_data['bill_amount'];
            
            $expenditure_bill_total_number = isset($exist_daydata['expenditure_bill_total_number'])?$exist_daydata['expenditure_bill_total_number']:0;
            $result['expenditure_bill_total_number'] = $expenditure_bill_total_number + 1;
        } else if ($bill_data['bill_type'] == 2) {
            // 收入
            $income_bill_total_fee = isset($exist_daydata['income_bill_total_fee'])?$exist_daydata['income_bill_total_fee']:0;
            $result['income_bill_total_fee'] = $income_bill_total_fee + $bill_data['bill_amount'];
            
            $income_bill_total_number = isset($exist_daydata['income_bill_total_number'])?$exist_daydata['income_bill_total_number']:0;
            $result['income_bill_total_number'] = $income_bill_total_number + 1;
        }
        
        return $result;
    }
    
    /**
     * 账单删除：账单日数据变更
     * @param array $bill_data
     * @param array $exist_daydata
     * @return array $result 包含计算好的账单日数据元素
     */
    private function removeBillToDayData(array $bill_data, array $exist_daydata = [])
    {
        $result = [];
        
        // 支出
        if ($bill_data['bill_type'] == 1) {
            $expenditure_bill_total_fee = isset($exist_daydata['expenditure_bill_total_fee'])?$exist_daydata['expenditure_bill_total_fee']:0;
            $result['expenditure_bill_total_fee'] = $expenditure_bill_total_fee - $bill_data['bill_amount'];
            
            $expenditure_bill_total_number = isset($exist_daydata['expenditure_bill_total_number'])?$exist_daydata['expenditure_bill_total_number']:0;
            $result['expenditure_bill_total_number'] = $expenditure_bill_total_number - 1;
        } else if ($bill_data['bill_type'] == 2) {
            // 收入
            $income_bill_total_fee = isset($exist_daydata['income_bill_total_fee'])?$exist_daydata['income_bill_total_fee']:0;
            $result['income_bill_total_fee'] = $income_bill_total_fee - $bill_data['bill_amount'];
            
            $income_bill_total_number = isset($exist_daydata['income_bill_total_number'])?$exist_daydata['income_bill_total_number']:0;
            $result['income_bill_total_number'] = $income_bill_total_number - 1;
        }
        
        return $result;
    }

	/**
	 * 获取账单每月/年支出总天数
	 * @param int $user_id 用户ID
	 * @param int $date 查询的日期 月201908 | 年2019
	 * @return int $result 
	 */
	public function countBillDay(int $user_id, int $date = 0)
	{
		$where = ['user_id' => $user_id];

		if ($date) {
			$where['bill_day'] = ['LIKE', "$date%"];
		}

		$res = $this->where($where)->field('COUNT(DISTINCT(bill_day)) AS total')->find();
		if ($res->data['total']) {
			return $res->data['total'];
		}

		return 1;
	}
	
	/**
	 * 获取某天账单数据
	 * @param array $where
     * @param array $fields
     * @return array $result
	 */
	public function getDayData(array $where = [], array $fields = [])
	{	
	    $res = $this->where($where)->field($fields)->find();
        if (!empty($res->data)) {
            return $res->data;
        }
        
        return [];
	}
	
	/**
	 * 获取某几天账单集合数据
	 * @param array $where
	 * @param array $count_day_fields 统计的字段列表 key => value  字段别名 => 字段
	 * @return array $result
	 */
	public function countDaysBill(array $where = [], array $count_day_fields = [])
	{  
	    $result = [];
	    
	    foreach ($count_day_fields as $alias => $field) {
	        if ($field == 'average_expenditure') {
	            $result[$alias] = $this->where($where)->avg('expenditure_bill_total_fee');
	        } else {
	            $result[$alias] = $this->where($where)->sum($field);
	        }
	    }
	    
	    return $result;
	}
}