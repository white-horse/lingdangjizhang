<?php
/**
 * 数据相关设置获取
 * @author Jerry
 * @date 20190819
 */

namespace app\wxapp\controller;

use app\service\controller\Time;
use app\wxapp\model\BillTotalData;
use app\wxapp\model\BillDayData;
use app\wxapp\model\BillMonthData;

class Data extends Base
{
    /**@var object 常用实体对象  */
    protected static $billTotalDataEntity = null;
    protected static $billDayDataEntity = null;
    protected static $billMonthDataEntity = null;
    
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
     * 获取基本分析数据
     */
    public function getBaseAnalysis()
    {
        $result = [];
        
        // 昨日、今日、上周、本周的支出/收入   金额|账单数
        // 上月、本月的支出/收入 金额|账单数|结余|日均消费
        
        $today = Time::todayDate();
        $yestoday = Time::yesterdayDate();
        
        $week = Time::weekDate();
        $last_week = Time::lastWeekDate();
        
        $month = Time::month();
        $last_month = Time::lastMonth();
        
        // 昨日
        $day_where = [
            'user_id' => $this->userInfo['id'],
            'bill_day' => $yestoday
        ];
        $day_fields = [
            'bill_day AS date',
            'expenditure_bill_total_fee AS expenditureFee',
            'expenditure_bill_total_number AS expenditureTotal',
            'income_bill_total_fee AS incomeFee',
            'income_bill_total_number AS incomeTotal',
        ];
        $result['yestodayData'] = self::$billDayDataEntity->getDayData($day_where, $day_fields);
        $result['yestodayData']['date'] = substr($yestoday, 4);
        
        // 今日
        $day_where['bill_day'] = $today;
        $result['todayData'] = self::$billDayDataEntity->getDayData($day_where, $day_fields);
        $result['todayData']['date'] = substr($today, 4);
        
        // 上周
        $day_where['bill_day'] = [
            'BETWEEN',
            [$last_week[0], $last_week[1]]
        ];
        $count_day_fields = [
            'expenditureFee' => 'expenditure_bill_total_fee',
            'expenditureTotal' => 'expenditure_bill_total_number',
            'incomeFee' => 'income_bill_total_fee',
            'incomeTotal' => 'income_bill_total_number',
            'averageExpenditureFee' => 'average_expenditure'
        ];
        $result['lastWeekData'] = self::$billDayDataEntity->countDaysBill($day_where, $count_day_fields);
        $result['lastWeekData']['date'] = substr($last_week[0], 4).'~'.substr($last_week[1], 4);
        
        // 本周
        $day_where['bill_day'] = [
            'BETWEEN',
            [$week[0], $week[1]]
        ];
        $result['currWeekData'] = self::$billDayDataEntity->countDaysBill($day_where, $count_day_fields);
        $result['currWeekData']['date'] = substr($week[0], 4).'~'.substr($week[1], 4);
        
        // 上月
        $month_where = [
            'user_id' => $this->userInfo['id'],
            'bill_month' => $last_month,
        ];
        $month_fields = [
            'bill_month AS date',
            'expenditure_bill_total_fee AS expenditureFee',
            'expenditure_bill_total_number AS expenditureTotal',
            'income_bill_total_fee AS incomeFee',
            'income_bill_total_number AS incomeTotal',  
            'day_average_expenditure_fee AS averageExpenditureFee',
            'month_balance_fee AS balanceFee'
        ];
        
        $result['lastMonthData'] = self::$billMonthDataEntity->getMonthData($month_where, $month_fields);
        
        // 本月
        $month_where['bill_month'] = $month;
        $result['currMonthData'] = self::$billMonthDataEntity->getMonthData($month_where, $month_fields);
        
        return $this->outputData(200, 'success', $result);
    }
    
    /**
     * 初始化常用实体
     */
    protected function init()
    {
        self::$billTotalDataEntity = new BillTotalData();
        self::$billDayDataEntity = new BillDayData();
        self::$billMonthDataEntity = new BillMonthData();
    }
    
}