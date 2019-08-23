<?php
/**
 * 账单管理控制器
 * @author Jerry
 * @date 20190821
 */
namespace app\wxapp\controller;

use app\wxapp\model\BillTag;
use app\wxapp\model\BillItem;

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

		if (empty($this->request->param('billFee'))) {
			return $this->outputData(1000, '请输入账单金额');
		}

		$create_data = [
			'user_id' => $this->userInfo['id'],
		    'bill_type' => self::$billType[$this->request->param('billType')],
			'bill_amount' => $this->request->param('billFee'),
		    'bill_tag' => $this->request->param('tagTitle')?:'其他',
			'bill_remark' => $this->request->param('billRemark'),
			'bill_date' => str_replace('-', '', $this->request->param('billDate')),
		];
		
		$result['result'] = true;
		return $this->outputData(200, 'success', $result);
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
     * 初始化常用实体
     */
    protected function init()
    {
        self::$billTagEntity = new BillTag();
		self::$billItemEntity = new BillItem();
    }
}