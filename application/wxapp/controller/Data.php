<?php
/**
 * 数据相关设置获取
 * @author Jerry
 * @date 20190819
 */

namespace app\wxapp\controller;

use app\wxapp\model\BillTotalData;

class Data extends Base
{
    /**@var object 常用实体对象  */
    protected static $billTotalDataEntity = null;
    
    public function __construct()
    {
        parent::__construct();
        $this->checkUser();
        $this->init();
    }
    
    
    /**
     * 获取用户中心总概览数据
     * @param string $openid
     * @return json
     */
    public function getOverviewItem()
    {
        $where = ['user_id' => $this->userInfo['id']];
        $fields = [
            'expenditure_bill_total_fee AS totalExpenditureFee',
            'income_bill_total_fee AS totalIncomeFee',
            'total_balance_fee AS totalBalanceFee',
            'day_average_expenditure_fee AS averageDayExpenditureFee',
            'data_start_date AS dataStartDate',
            'data_latest_date AS dataLatestDate',
        ];
        
       $data = self::$billTotalDataEntity->getOne($where, $fields);
       if (empty($data)) {
                $data = [
                    'totalIncomeFee' => '0.00',
                    'totalExpenditureFee' => '0.00',
                    'totalBalanceFee' => '0.00',
                    'averageDayExpenditureFee' => '0.00',
                    'dataStartDate' => date('Y-m-d'),
                    'dataLatestDate' => date('Y-m-d')
                ];
        }
        
        return $this->outputData(200, 'success', $data);
    }
    
    /**
     * 初始化常用实体
     */
    protected function init()
    {
        self::$billTotalDataEntity = new BillTotalData();
    }
    
}