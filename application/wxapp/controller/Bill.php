<?php
/**
 * 账单管理控制器
 * @author Jerry
 * @date 20190821
 */
namespace app\wxapp\controller;

use app\wxapp\model\BillTag;
use app\wxapp\model\BillItem;
use app\service\controller\Time;

class Bill extends Base
{
    /**@var object 常用实体对象  */
    protected static $billTagEntity = null;
	protected static $billItemEntity = null;

	/**@var array 账单类型*/
	protected static $billType = ['z' => 1, 's' => 2];
	
    public function __construct()
    {
        parent::__construct();
        $this->checkUser();
        $this->init();
    }

	/**
	 * 添加一个账单
	 */
	public function createOne()
	{
		if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $this->request->param('billDate'))) {
			return $this->outputData(301, 'billDate param error');
		}

		if (empty(self::$billType[$this->request->param('billType')])) {
			return $this->outputData(301, 'billType param error');
		}

		if (!preg_match('/((^[1-9]\d*)|^0)(\.{0,1}\d{0,8}){0,1}$/', $this->request->param('billFee'))) {
			return $this->outputData(1000, '请输入正确的账单金额');
		}
		
		if (!empty($this->request->param('billRemark')) && (mb_strlen($this->request->param('billRemark')) > 64)) {
		    return $this->outputData(1001, '请输入正确的备注');
		}

		$create_data = [
			'user_id' => $this->userInfo['id'],
		    'bill_type' => self::$billType[$this->request->param('billType')],
			'bill_amount' => $this->request->param('billFee'),
		    'bill_tag' => $this->request->param('tagTitle')?:'其他',
			'bill_remark' => $this->request->param('billRemark'),
			'bill_date' => str_replace('-', '', $this->request->param('billDate')),
		];
		
		$result['result'] = false;
		if (self::$billItemEntity->addBill($create_data) !== false) {
		    $result['result'] = true;
		}
		
		return $this->outputData(200, 'success', $result);
	}
    
    /**
     * 获取账单标签列表
     */
    public function getTags()
    {
        $where = ['is_show' => 1];
        $fields = [
//             'id AS tag_id',
            'tag_name AS title',
            'tag_color_name name',
            'tag_color_value AS color'
        ];
        $list = self::$billTagEntity->getTagList($where, $fields);
        
        return $this->outputData(200, 'success', $list);
    }
    
	/**
	 * 获取账单列表 按日期分组 倒序
	 * @param int $startDate 开始日期 20190801
	 * @param int $endDate 结束日期 20190831
	 * @return array @list 账单列表
	 */
	public function getItem()
	{
		$list = [];

		$curr_month_date = Time::monthDate();
		$start_date = $this->request->param('startDate');
		$end_date = $this->request->param('endDate');

		$start_date = $start_date ? str_replace('-', '', $start_date) : $curr_month_date[0];
		$end_date = $end_date ? str_replace('-', '', $end_date) : $curr_month_date[1];

		if (($end_date - $start_date) > 31) {
			return $this->outputData(301, '查询日期范围不能超过31天');
		}

		$bills = self::$billItemEntity->getBills($start_date, $end_date);
		if (!empty($bills)) {
			$list = $this->billsGroup($bills);
		}

		return $this->outputData(200, 'success', $list);
	}

	/** 
	 * 将账单按日期分组
	 * @param array $bills 原账单列表
	 * @return array $result 按日期分组后的列表
	 */
	private function billsGroup(array $bills = [])
	{	
		$result = [];

		foreach ($bills as $key => $value) {
			$result[$value['bill_date']][] = $value;
		}

		return $result;
	}

    /**
     * 初始化常用实体
     */
    protected function init()
    {
        self::$billTagEntity = new BillTag();
		self::$billItemEntity = new BillItem();
    }


}